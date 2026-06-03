<?php
/**
 * admin/index.php
 *
 * Admin dashboard — the first page an admin sees after logging in.
 *
 * Responsibilities:
 *   - Guard the page so only logged-in admins can view it
 *   - Display a summary of key stats (products, orders, pending testimonials)
 *   - Provide quick navigation links to the manage pages
 *
 */

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Database.php';

// Redirect to login if no valid session exists — must be at the top of every admin page
Auth::requireLogin();

// Get the current admin's username to display a personalised welcome message
$admin = Auth::getCurrentAdmin();

// Fetch live stats from the database for the summary cards
$db = Database::getInstance();

// Count only available (non-soft-deleted) products
$productCount = $db->fetchOne(
    "SELECT COUNT(*) AS total FROM product WHERE is_available = 1"
)['total'] ?? 0;

// Count testimonials waiting for admin approval
$pendingTestCount = $db->fetchOne(
    "SELECT COUNT(*) AS total FROM testimonial WHERE status = 'pending'"
)['total'] ?? 0;

// Count all orders ever placed
$orderCount = $db->fetchOne(
    "SELECT COUNT(*) AS total FROM purchase"
)['total'] ?? 0;

// Load the shared admin navigation bar and opening HTML
include __DIR__ . '/admin-header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-1">Dashboard</h2>
    <!-- Escape the username before echoing — defence against XSS -->
    <p class="text-muted mb-4">Welcome back, <?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8') ?></p>

    <!-- Summary stat cards — cast to int to avoid echoing unexpected types -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card text-center p-3">
                <div class="fs-1 fw-bold"><?= (int)$productCount ?></div>
                <div class="text-muted">Available Products</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-center p-3">
                <div class="fs-1 fw-bold"><?= (int)$orderCount ?></div>
                <div class="text-muted">Total Orders</div>
            </div>
        </div>
        <!-- Highlight the testimonials card in yellow when there are items needing review -->
        <div class="col-sm-4">
            <div class="card text-center p-3 <?= $pendingTestCount > 0 ? 'border-warning' : '' ?>">
                <div class="fs-1 fw-bold"><?= (int)$pendingTestCount ?></div>
                <div class="text-muted">Pending Testimonials</div>
            </div>
        </div>
    </div>

    <!-- Quick navigation links to each management section -->
    <div class="list-group" style="max-width:400px;">
        <a href="products.php" class="list-group-item list-group-item-action">🖼 Manage Products</a>
        <a href="news.php" class="list-group-item list-group-item-action">📰 Manage News</a>
        <a href="testimonials.php" class="list-group-item list-group-item-action">
            💬 Moderate Testimonials
            <?php if ($pendingTestCount > 0): ?>
                <!-- Badge alerts the admin to pending testimonials without them needing to click in -->
                <span class="badge bg-warning text-dark float-end"><?= (int)$pendingTestCount ?> pending</span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Load the shared closing HTML and Bootstrap JS -->
<?php include __DIR__ . '/admin-footer.php'; ?>
