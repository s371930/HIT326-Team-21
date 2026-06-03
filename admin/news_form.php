<?php
/**
 * Admin News Form
 * Create or edit a news item. Protected by Auth::requireLogin().
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/models/News.php';

Auth::start();
Auth::requireLogin();

$newsModel = new News();
$admin = Auth::getCurrentAdmin();

// Check if we are editing an existing news item
$editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$editing = false;
$newsItem = null;

if ($editId) {
    $newsItem = $newsModel->getById($editId);
    if ($newsItem) {
        $editing = true;
    }
}

$error = '';
$title_val = $newsItem['title'] ?? '';
$content_val = $newsItem['content'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_val   = trim($_POST['title'] ?? '');
    $content_val = trim($_POST['content'] ?? '');

    // Validate input
    if ($title_val === '' || $content_val === '') {
        $error = 'Both title and content are required.';
    } elseif (mb_strlen($title_val) > 200) {
        $error = 'Title must be 200 characters or less.';
    } else {
        // Save to database
        if ($editing) {
            $newsModel->update($newsItem['news_id'], $title_val, $content_val);
        } else {
            $newsModel->create($title_val, $content_val, $admin['admin_id']);
        }

        header('Location: ' . BASE_URL . '/admin/dashboard.php?msg=saved');
        exit;
    }
}

$pageTitle = ($editing ? 'Edit' : 'New') . ' News — Admin';
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
<body>

    <!-- Admin Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">&#127912; Admin Panel</span>
            <a href="<?= BASE_URL ?>/admin/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container my-4" style="max-width: 700px;">

        <h1><?= $editing ? 'Edit News Item' : 'Create News Item' ?></h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control"
                               maxlength="200" required
                               value="<?= htmlspecialchars($title_val) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea name="content" id="content" class="form-control"
                                  rows="6" required><?= htmlspecialchars($content_val) ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark">
                            <?= $editing ? 'Update' : 'Publish' ?>
                        </button>
                        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>