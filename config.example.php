<?php
/**
 * Application Configuration TEMPLATE
 *
 * HOW TO USE THIS FILE:
 * 1. Copy this file and rename the copy to "config.php"
 * 2. Open config.php and replace the placeholder values with your local
 *    database credentials and any environment-specific settings
 * 3. Save. The application will now connect to your local database.
 *
 * IMPORTANT:
 * - config.php is listed in .gitignore and must NEVER be committed.
 * - Only this template file (config.example.php) belongs in the repository.
 * - This pattern keeps sensitive credentials out of source control while
 *   still giving every team member a clear starting point.
 */

// ---------------------------------------------------------------------
// Database connection
// ---------------------------------------------------------------------
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    3306);
define('DB_NAME',    'darwin_art');
define('DB_USER',    'your_db_user');      // e.g. 'root' for default XAMPP
define('DB_PASS',    'your_db_password');  // e.g. '' (empty) for default XAMPP
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------
// Application
// ---------------------------------------------------------------------
define('APP_NAME', 'Darwin Art Company');
define('BASE_URL', 'http://localhost/darwin-art-store');

// 'development' shows errors on screen; 'production' hides them.
define('APP_ENV', 'development');

// ---------------------------------------------------------------------
// Session
// ---------------------------------------------------------------------
define('SESSION_NAME',     'darwin_art_session');
define('SESSION_LIFETIME', 7200); // seconds (2 hours)

// ---------------------------------------------------------------------
// Error reporting (driven by APP_ENV)
// ---------------------------------------------------------------------
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
