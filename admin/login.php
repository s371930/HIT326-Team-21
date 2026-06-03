<?php
/**
 * admin/login.php
 *
 * Admin login page — handles both displaying the login form and
 * processing the form submission.
 *
 * Flow:
 *   1. Start the session
 *   2. If already logged in, skip to dashboard
 *   3. On POST: validate input, look up admin by username,
 *      verify bcrypt password, start session on success
 *   4. On GET (or failed POST): show the login form
 *
 */

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Database.php';

// Start the session using hardened settings defined in Auth
Auth::start();

// If the admin is already logged in, send them straight to the dashboard
if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Holds any login error message shown to the user
$error = '';

// Only process the form when it has been submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitise inputs — trim whitespace from username, leave password as-is
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation: both fields are required
    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Look up the admin record by username using a prepared statement
        // We only select the columns we actually need (principle of least privilege)
        $db    = Database::getInstance();
        $admin = $db->fetchOne(
            "SELECT admin_id, username, password_hash FROM admin WHERE username = ?",
            [$username]
        );

        // Verify the submitted password against the stored bcrypt hash.
        // Auth::verifyPassword() wraps PHP's password_verify() — never plain-text comparison.
        if ($admin && Auth::verifyPassword($password, $admin['password_hash'])) {
            // Credentials are valid — create the session and redirect
            Auth::login($admin);
            header('Location: index.php');
            exit;
        } else {
            // Keep the error message vague on purpose — don't reveal whether
            // the username or the password was wrong (security best practice)
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Darwin Art Store</title>
    <!-- Bootstrap 5 for responsive layout and form styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Centre the login card vertically and horizontally on all screen sizes */
        body {
            background-color: #f4f1ee;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Georgia, serif;
        }
        .login-card {
            background: #fff;
            border-radius: 4px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        }
        .login-card h1 { font-size: 1.4rem; margin-bottom: 0.25rem; }
        .login-card p  { color: #888; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .btn-primary   { background-color: #2c2c2c; border-color: #2c2c2c; }
        .btn-primary:hover { background-color: #444; border-color: #444; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Darwin Art Store</h1>
        <p>Admin Panel — Sign in to continue</p>

        <?php if ($error !== ''): ?>
            <!-- Display login error — escaped to prevent XSS -->
            <div class="alert alert-danger py-2" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- novalidate disables browser validation so our server-side checks always run -->
        <form method="POST" action="login.php" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input
                    type="text"
                    class="form-control"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                    autofocus
                >
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <!-- type="password" ensures the browser masks the input -->
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    required
                >
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign In</button>
        </form>
    </div>
</body>
</html>
