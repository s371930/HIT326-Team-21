<?php
/**
 * Admin Dashboard
 * Manage news items. Protected by Auth::requireLogin().
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/models/News.php';

Auth::start();
Auth::requireLogin();

$newsModel = new News();
$admin = Auth::getCurrentAdmin();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $deleteId = filter_input(INPUT_POST, 'news_id', FILTER_VALIDATE_INT);
    if ($deleteId) {
        $newsModel->delete($deleteId);
        header('Location: ' . BASE_URL . '/admin/dashboard.php?msg=deleted');
        exit;
    }
}

// Fetch all news items for the table
$allNews = $newsModel->getAll();

$pageTitle = 'Admin Dashboard — Darwin Art Company';
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
            <div>
                <span class="text-light me-3">Welcome, <?= htmlspecialchars($admin['username']) ?></span>
                <a href="<?= BASE_URL ?>/admin/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Manage News</h1>
            <a href="<?= BASE_URL ?>/admin/news_form.php" class="btn btn-dark">+ New Post</a>
        </div>

        <!-- Success messages -->
        <?php if (($_GET['msg'] ?? '') === 'saved'): ?>
            <div class="alert alert-success">News item saved successfully.</div>
        <?php elseif (($_GET['msg'] ?? '') === 'deleted'): ?>
            <div class="alert alert-success">News item deleted.</div>
        <?php endif; ?>

        <!-- News Table -->
        <?php if (empty($allNews)): ?>
            <div class="alert alert-light border">No news items yet. Click "+ New Post" to create one.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Posted</th>
                            <th style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allNews as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= htmlspecialchars($item['author'] ?? 'Unknown') ?></td>
                                <td><?= date('j M Y, g:i A', strtotime($item['posted_at'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/news_form.php?id=<?= $item['news_id'] ?>"
                                       class="btn btn-sm btn-outline-dark me-1">Edit</a>

                                    <form method="POST" action="" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="news_id" value="<?= $item['news_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/" class="text-secondary">&larr; Back to site</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>