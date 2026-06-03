<?php
/**
 * admin/logout.php
 *
 * Destroys the current admin session and redirects to the login page.
 *
 * Auth::logout() handles:
 *   - Clearing all session variables
 *   - Expiring the session cookie in the browser
 *   - Calling session_destroy()
 *
 */

require_once __DIR__ . '/../app/core/Auth.php';

// Log the person out and take them back to the login screen
Auth::logout();
header('Location: login.php');
exit;
