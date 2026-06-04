<?php
/**
 * Admin Testimonials Moderation Page
 * Allows admins to approve or reject pending customer testimonials.
 * Only displays pending testimonials for quick moderation workflow.
 */

require_once __DIR__ . '/../app/core/Auth.php';
Auth::requireLogin();

require_once __DIR__ . '/../app/models/Testimonial.php';
require_once __DIR__ . '/../app/core/Database.php';

$testimonial_model = new Testimonial();
$message = '';
$message_type = '';

// Handle approve action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['testimonial_id'])) {
    $testimonial_id = (int) $_POST['testimonial_id'];
    $admin_id = (int) $_SESSION['admin_id'];

    if ($_POST['action'] === 'approve') {
        if ($testimonial_model->approve($testimonial_id, $admin_id)) {
            $message = 'Testimonial approved successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to approve testimonial.';
            $message_type = 'error';
        }
    } elseif ($_POST['action'] === 'reject') {
        if ($testimonial_model->reject($testimonial_id, $admin_id)) {
            $message = 'Testimonial rejected.';
            $message_type = 'success';
        } else {
            $message = 'Failed to reject testimonial.';
            $message_type = 'error';
        }
    }
}

// Fetch all pending testimonials
$pending = $testimonial_model->getPending();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials Moderation - Darwin Art Store Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php require_once __DIR__ . '/admin-header.php'; ?>

    <div class="admin-container">
        <h1>Testimonials Moderation</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (count($pending) === 0): ?>
            <p class="info-message">No pending testimonials to moderate at this time.</p>
        <?php else: ?>
            <p>You have <strong><?= count($pending) ?></strong> pending testimonial(s) to review.</p>

            <div class="testimonials-queue">
                <?php foreach ($pending as $testimonial): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <h3><?= htmlspecialchars($testimonial['customer_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php if ($testimonial['email']): ?>
                                <p class="email">
                                    <em><?= htmlspecialchars($testimonial['email'], ENT_QUOTES, 'UTF-8') ?></em>
                                </p>
                            <?php endif; ?>
                            <p class="submitted-date">
                                Submitted: <?= date('M d, Y \a\t g:i A', strtotime($testimonial['submitted_at'])) ?>
                            </p>
                        </div>

                        <div class="testimonial-content">
                            <p><?= htmlspecialchars($testimonial['content'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="testimonial-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="testimonial_id" value="<?= (int) $testimonial['testimonial_id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-approve">✓ Approve</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="testimonial_id" value="<?= (int) $testimonial['testimonial_id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-reject">✗ Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/admin-footer.php'; ?>
</body>
</html>
