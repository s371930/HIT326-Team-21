<?php
/**
 * Testimonials Moderation View
 * 
 * This is the template for the testimonials moderation queue.
 * Variables available from calling page:
 *   - $pending_testimonials: array of pending testimonial records
 *   - $message: optional success/error message
 *   - $message_type: 'success' or 'error'
 */

?>
<div class="testimonials-moderation-view">
    <h2>Pending Testimonials Queue</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($pending_testimonials)): ?>
        <div class="empty-state">
            <p>No pending testimonials to moderate.</p>
        </div>
    <?php else: ?>
        <div class="testimonials-list">
            <?php foreach ($pending_testimonials as $t): ?>
                <article class="testimonial-item">
                    <header class="testimonial-header">
                        <h3><?= htmlspecialchars($t['customer_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($t['email'])): ?>
                            <p class="contact-email">
                                <small>Email: <?= htmlspecialchars($t['email'], ENT_QUOTES, 'UTF-8') ?></small>
                            </p>
                        <?php endif; ?>
                        <time datetime="<?= htmlspecialchars($t['submitted_at'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= date('M d, Y \a\t g:i A', strtotime($t['submitted_at'])) ?>
                        </time>
                    </header>

                    <section class="testimonial-text">
                        <blockquote>
                            <?= nl2br(htmlspecialchars($t['content'], ENT_QUOTES, 'UTF-8')) ?>
                        </blockquote>
                    </section>

                    <footer class="testimonial-actions">
                        <form method="POST" action="../admin/testimonials.php" style="display: inline;">
                            <input type="hidden" name="testimonial_id" value="<?= (int) $t['testimonial_id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>

                        <form method="POST" action="../admin/testimonials.php" style="display: inline;">
                            <input type="hidden" name="testimonial_id" value="<?= (int) $t['testimonial_id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .testimonials-moderation-view {
        padding: 20px;
    }

    .testimonials-list {
        display: grid;
        gap: 20px;
    }

    .testimonial-item {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        background: #f9f9f9;
    }

    .testimonial-item:hover {
        background: #f5f5f5;
        border-color: #999;
    }

    .testimonial-header {
        margin-bottom: 15px;
    }

    .testimonial-header h3 {
        margin: 0 0 8px 0;
        font-size: 1.1em;
    }

    .contact-email {
        color: #666;
        margin: 5px 0;
    }

    .testimonial-header time {
        color: #999;
        font-size: 0.9em;
    }

    .testimonial-text blockquote {
        margin: 15px 0;
        padding: 15px;
        background: white;
        border-left: 4px solid #007bff;
        font-style: italic;
    }

    .testimonial-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .alert {
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
