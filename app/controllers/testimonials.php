<?php
/**
 * app/controllers/testimonials.php
 *
 * Controller for the public testimonials page.
 *
 * Responsibilities:
 *   1. Load the Testimonial model
 *   2. Handle testimonial form submission (POST) — the ONLY place this happens
 *   3. Fetch approved testimonials for display
 *   4. Pass data to the view (which is pure presentation, no logic)
 *
 * Accessed via: http://localhost/darwin-art-store/?page=testimonials
 */

require_once __DIR__ . '/../models/Testimonial.php';

$testimonialModel = new Testimonial();

// Feedback message shown after a submission.
$message = '';
$msgType = 'success';

// Submitted values, preserved so the form can be re-rendered on error.
$old = [
    'customer_name' => '',
    'email'         => '',
    'content'       => '',
];

// Handle testimonial submission form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim input.
    foreach ($old as $field => $_) {
        $old[$field] = trim($_POST[$field] ?? '');
    }

    // Server-side validation — all fields required.
    if ($old['customer_name'] === '' || $old['email'] === '' || $old['content'] === '') {
        $message = 'Please fill in all fields.';
        $msgType = 'danger';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $msgType = 'danger';
    } else {
        // Save the testimonial — status defaults to 'pending' in the model.
        $testimonialModel->create($old['customer_name'], $old['email'], $old['content']);
        $message = 'Thank you! Your testimonial has been submitted and will appear after review.';
        $msgType = 'success';

        // Clear the field values so the form resets after a successful submit.
        $old = ['customer_name' => '', 'email' => '', 'content' => ''];
    }
}

// Fetch all approved testimonials for display on the page.
$approvedTestimonials = $testimonialModel->getApproved();

// Set page variables for the shared header.
$pageTitle   = 'Testimonials — Darwin Art Company';
$currentPage = 'testimonials';

// Load the view.
require_once __DIR__ . '/../views/testimonials.php';
