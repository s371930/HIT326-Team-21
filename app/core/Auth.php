<?php
/**
 * Auth — Authentication helpers
 *
 * Centralises password hashing and admin session management.
 * The admin panel and any controller that gates content goes through
 * the static methods on this class.
 *
 * Quick usage:
 *
 *   require_once __DIR__ . '/../core/Auth.php';
 *
 *   // Start the session (call once at the top of every entry point)
 *   Auth::start();
 *
 *   // Verifying a login form submission
 *   $admin = $db->fetchOne(
 *       "SELECT admin_id, username, password_hash FROM admin WHERE username = ?",
 *       [$username]
 *   );
 *   if ($admin && Auth::verifyPassword($password, $admin['password_hash'])) {
 *       Auth::login($admin);
 *       header('Location: dashboard.php');
 *       exit;
 *   }
 *
 *   // Protect an admin page (put this at the top, before any output)
 *   Auth::requireLogin();
 *
 *   // Read the current user
 *   $me = Auth::getCurrentAdmin();   // null if not logged in
 *
 *   // Log out
 *   Auth::logout();
 *
 *   // Creating / changing a password (e.g. when seeding or letting an
 *   // admin change their own password)
 *   $hash = Auth::hashPassword($plainTextPassword);
 *   $db->execute(
 *       "UPDATE admin SET password_hash = ? WHERE admin_id = ?",
 *       [$hash, $adminId]
 *   );
 */

require_once __DIR__ . '/../../config.php';

class Auth
{
    /**
     * Start the session with hardened settings.
     * Safe to call multiple times — does nothing if already started.
     *
     * Security settings applied:
     *   - HttpOnly: cookie can't be read by JavaScript (XSS mitigation)
     *   - SameSite=Lax: cookie not sent on cross-site POSTs (CSRF mitigation)
     *   - Custom session name from config (avoids the default PHPSESSID)
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configure session before starting it — these calls have no effect
        // once session_start() has been called.
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false, // set true if/when site is served over HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    /**
     * Hash a plain-text password using bcrypt.
     * Use when creating a new admin or changing a password.
     */
    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    /**
     * Verify a plain-text password against a stored bcrypt hash.
     * Returns true if they match.
     */
    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * Mark the session as logged in for the given admin record.
     * Regenerates the session ID to prevent session fixation attacks.
     *
     * @param array $admin Row from the admin table (must contain
     *                     admin_id and username).
     */
    public static function login(array $admin): void
    {
        self::start();

        // Regenerate ID on privilege change to prevent fixation attacks
        session_regenerate_id(true);

        $_SESSION['admin_id']    = (int) $admin['admin_id'];
        $_SESSION['admin_user']  = $admin['username'];
        $_SESSION['logged_in']   = true;
        $_SESSION['login_time']  = time();
    }

    /**
     * Destroy the current session completely.
     */
    public static function logout(): void
    {
        self::start();
        $_SESSION = [];

        // Expire the session cookie too
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Is there a currently authenticated admin?
     */
    public static function isLoggedIn(): bool
    {
        self::start();
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Guard helper — call at the top of any admin-only page.
     * If the visitor is not authenticated, redirect them to the login page
     * and stop further execution.
     */
    public static function requireLogin(string $loginUrl = '/darwin-art-store/admin/login.php'): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /**
     * Get the current admin's session info as an associative array,
     * or null if nobody is logged in.
     *
     * @return array{admin_id:int, username:string}|null
     */
    public static function getCurrentAdmin(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        return [
            'admin_id' => (int) $_SESSION['admin_id'],
            'username' => (string) $_SESSION['admin_user'],
        ];
    }
}
