<?php
/**
 * Admin Logout
 * Destroys the session and redirects to home page.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/core/Auth.php';

Auth::logout();
header('Location: ' . BASE_URL . '/');
exit;