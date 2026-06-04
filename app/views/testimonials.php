<?php
/**
 * app/views/testimonials.php
 *
 * Public testimonials page — shows all approved testimonials and
 * provides a form for customers to submit their own.
 *
 * Project requirement (Option 2, Feature B):
 * "The testimonials would be presented on a separate page,
 *  with a link from the front page."
 *
 * Submitted testimonials are marked 'pending' and must be approved
 * by an admin before they appear on this page.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Testimonial.php';

$testimonialModel = new Testimonial();

// Holds form feedback message
$message = '';
$msgType = 'success';

// Handle testimonial submission form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitise all inputs before using them
    $customerName = trim($_POST['customer_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $content      = trim($_POST['content'] ?? '');

    // Server-side validation — all fields required
    if ($customerName === '' || $email === '' || $content === '') {
        $message = 'Please fill in all fields.';
        $msgType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Validate email format
        $message = 'Please enter a valid email address.';
        $msgType = 'danger';
    } else {
        // Save the testimonial — status defaults to 'pending'
        $testimonialModel->create($customerName, $email, $content);
        $message = 'Thank you! Your testimonial has been submitted and will appear after review.';
        $msgType = 'success';
    }
}

// Fetch all approved testimonials for display
$approvedTestimonials = $testimonialModel->getApproved();

$pageTitle   = 'Testimonials — Darwin Art Company';
$currentPage = 'testimonials';
?>
<?php require_once __DIR__ . '/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-2">Customer Testimonials</h1>
    <p class="text-muted mb-4">What our customers say about us.</p>

    <!-- Approved testimonials -->
    <?php if (empty($approvedTestimonials)): ?>
        <div class="alert alert-light border mb-4">
            No testimonials yet — be the first to share your experience!
        </div>
    <?php else: ?>
        <div class="row g-4 mb-5">
            <?php foreach ($approvedTestimonials as $t): ?>
                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <!-- Testimonial content — escaped to prevent XSS -->
                            <p class="card-text fst-italic">
                                "<?= htmlspecialchars($t['content'], ENT_QUOTES, 'UTF-8') ?>"
                            </p>
                            <p class="text-muted small mb-0">
                                — <?= htmlspecialchars($t['customer_name'], ENT_QUOTES, 'UTF-8') ?>
                                &middot; <?= date('j M Y', strtotime($t['submitted_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Testimonial submission form -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="mb-3">Share Your Experience</h4>

            <!-- Feedback message after submission -->
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?= $msgType ?>">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- novalidate disables browser validation so server-side checks always run -->
            <form method="POST" action="" novalidate>
                <div class="mb-3">
                    <label for="customer_name" class="form-label">Your Name *</label>
                    <input
                        type="text"
                        class="form-control"
                        id="customer_name"
                        name="customer_name"
                        value="<?= htmlspecialchars($_POST['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                    <div class="form-text">Your email will not be displayed publicly.</div>
                </div>
                <div class="mb-4">
                    <label for="content" class="form-label">Your Testimonial *</label>
                    <textarea
                        class="form-control"
                        id="content"
                        name="content"
                        rows="4"
                        required
                    ><?= htmlspecialchars($_POST['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <button type="submit" class="btn btn-dark">Submit Testimonial</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>