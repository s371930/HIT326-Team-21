<?php
/**
 * admin/testimonials.php
 *
 * Testimonial moderation page — allows the admin to approve or reject
 * customer testimonials before they are published on the public site.
 *
 * Project requirement (Option 2, Feature B):
 * "These would have to be moderated by the company before they are published."
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Testimonial.php';

// Start session and protect this page from unauthenticated access
Auth::start();
Auth::requireLogin();

$admin            = Auth::getCurrentAdmin();
$testimonialModel = new Testimonial();

// Holds feedback message shown after an approve/reject action
$message = '';
$msgType = 'success';

// Handle approve / reject form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action        = $_POST['action'] ?? '';
    $testimonialId = filter_input(INPUT_POST, 'testimonial_id', FILTER_VALIDATE_INT);

    if ($testimonialId) {
        try {
            if ($action === 'approve') {
                // Approve — testimonial will now show on the public page
                $testimonialModel->approve($testimonialId, $admin['admin_id']);
                $message = 'Testimonial approved and published.';
            } elseif ($action === 'reject') {
                // Reject — testimonial will not be shown publicly
                $testimonialModel->reject($testimonialId, $admin['admin_id']);
                $message = 'Testimonial rejected.';
                $msgType = 'warning';
            }
        } catch (Exception $e) {
            $message = 'Error processing testimonial: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            $msgType = 'danger';
        }
    }
}

// Fetch pending and approved testimonials separately for display
$pendingTestimonials  = $testimonialModel->getPending();
$approvedTestimonials = $testimonialModel->getApproved();

// Load the shared admin navbar and opening HTML
include __DIR__ . '/admin-header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-1">Moderate Testimonials</h2>
    <p class="text-muted mb-4">Review and approve customer testimonials before they appear publicly.</p>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= $msgType ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- PENDING TESTIMONIALS -->
    <h4 class="mb-3">
        Pending Review
        <?php if (!empty($pendingTestimonials)): ?>
            <span class="badge bg-warning text-dark ms-2"><?= count($pendingTestimonials) ?></span>
        <?php endif; ?>
    </h4>

    <?php if (empty($pendingTestimonials)): ?>
        <div class="alert alert-light border mb-4">No testimonials waiting for review.</div>
    <?php else: ?>
        <div class="row g-3 mb-5">
            <?php foreach ($pendingTestimonials as $t): ?>
                <div class="col-12 col-md-6">
                    <div class="card border-warning h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-1">
                                <?= htmlspecialchars($t['customer_name'], ENT_QUOTES, 'UTF-8') ?>
                            </h6>
                            <p class="text-muted small mb-2">
                                <?= htmlspecialchars($t['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                &middot;
                                <?= date('j M Y, g:i A', strtotime($t['submitted_at'])) ?>
                            </p>
                            <p class="card-text fst-italic">
                                "<?= htmlspecialchars($t['content'], ENT_QUOTES, 'UTF-8') ?>"
                            </p>
                        </div>
                        <div class="card-footer bg-transparent d-flex gap-2">
                            <form method="POST" action="testimonials.php">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="testimonial_id" value="<?= (int)$t['testimonial_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">✓ Approve</button>
                            </form>
                            <form method="POST" action="testimonials.php"
                                  onsubmit="return confirm('Reject this testimonial?');">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="testimonial_id" value="<?= (int)$t['testimonial_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">✗ Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- APPROVED TESTIMONIALS -->
    <h4 class="mb-3">Approved & Published</h4>

    <?php if (empty($approvedTestimonials)): ?>
        <div class="alert alert-light border">No approved testimonials yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Customer</th>
                        <th>Testimonial</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approvedTestimonials as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($t['content'], 0, 80, '...'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= date('j M Y', strtotime($t['submitted_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/admin-footer.php'; ?>