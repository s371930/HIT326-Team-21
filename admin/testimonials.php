<?php
/**
 * Admin testimonials moderation page
 *
 * Displays a queue of pending testimonials for approval or rejection.
 */

require_once __DIR__ . '/../app/core/Auth.php';
Auth::start();
Auth::requireLogin();

require_once __DIR__ . '/../app/models/Testimonial.php';

$testimonialModel = new Testimonial();
$admin = Auth::getCurrentAdmin();

// Handle approve/reject actions
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testimonialId = (int)($_POST['testimonial_id'] ?? 0);
    $action = $_POST['action'] ?? null;

    if ($testimonialId > 0 && ($action === 'approve' || $action === 'reject')) {
        try {
            if ($action === 'approve') {
                $testimonialModel->approve($testimonialId, $admin['admin_id']);
                $message = 'Testimonial approved and will now appear on the public page.';
            } else {
                $testimonialModel->reject($testimonialId, $admin['admin_id']);
                $message = 'Testimonial rejected.';
            }
        } catch (Exception $e) {
            $message = 'Error processing testimonial: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

$pendingTestimonials = $testimonialModel->getPending();
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
            <div class="alert alert-info">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($pendingTestimonials)): ?>
            <p class="no-results">No pending testimonials to review.</p>
        <?php else: ?>
            <p class="count"><?= count($pendingTestimonials) ?> pending <?= count($pendingTestimonials) === 1 ? 'testimonial' : 'testimonials' ?></p>

            <div class="testimonials-queue">
                <?php foreach ($pendingTestimonials as $testimonial): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <h3><?= htmlspecialchars($testimonial['customer_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="submitted-date">
                                Submitted: <?= htmlspecialchars(date('M d, Y at H:i', strtotime($testimonial['submitted_at'])), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <div class="testimonial-content">
                            <?= htmlspecialchars($testimonial['content'], ENT_QUOTES, 'UTF-8') ?>
                        </div>

                        <?php if ($testimonial['email']): ?>
                            <p class="testimonial-email">
                                <small>Email: <?= htmlspecialchars($testimonial['email'], ENT_QUOTES, 'UTF-8') ?></small>
                            </p>
                        <?php endif; ?>

                        <div class="testimonial-actions">
                            <form method="POST" action="" class="action-form">
                                <input type="hidden" name="testimonial_id" value="<?= $testimonial['testimonial_id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success">✓ Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger">✗ Reject</button>
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
