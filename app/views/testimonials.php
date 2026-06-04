<?php
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
                <div class="alert alert-<?= htmlspecialchars($msgType, ENT_QUOTES, 'UTF-8') ?>">
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
                        value="<?= htmlspecialchars($old['customer_name'], ENT_QUOTES, 'UTF-8') ?>"
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
                        value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>"
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
                    ><?= htmlspecialchars($old['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <button type="submit" class="btn btn-dark">Submit Testimonial</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
