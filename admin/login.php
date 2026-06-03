<?php
/**
 * Admin Login Page
 * Uses Auth.php (built by groupmate) for session and password verification.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';

Auth::start();

// If already logged in, go to dashboard
if (Auth::isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $db = Database::getInstance();
        $admin = $db->fetchOne(
            "SELECT admin_id, username, password_hash FROM admin WHERE username = ?",
            [$username]
        );

        if ($admin && Auth::verifyPassword($password, $admin['password_hash'])) {
            Auth::login($admin);
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$pageTitle = 'Admin Login — Darwin Art Company';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container" style="max-width: 420px; margin-top: 100px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">&#127912; Admin Login</h2>
            <p class="text-secondary">Darwin Art Company</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control"
                               value="<?= htmlspecialchars($username ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Log In</button>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>/" class="text-secondary">&larr; Back to site</a>
        </div>
    </div>

</body>
</html>