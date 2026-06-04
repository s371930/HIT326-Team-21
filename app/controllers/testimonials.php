<?php
/**
 * app/controllers/testimonials.php
 *
 * Controller for the public testimonials page.
 *
 * Responsibilities:
 *   1. Load the Testimonial model
 *   2. Handle testimonial form submission (POST)
 *   3. Fetch approved testimonials for display
 *   4. Pass data to the view
 *
 * Accessed via: http://localhost/darwin-art-store/?page=testimonials
 *
 */

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
        // Save the testimonial — status defaults to 'pending' in the model
        $testimonialModel->create($customerName, $email, $content);
        $message = 'Thank you! Your testimonial has been submitted and will appear after review.';
        $msgType = 'success';
    }
}

// Fetch all approved testimonials for display on the page
$approvedTestimonials = $testimonialModel->getApproved();

// Set page variables for the shared header
$pageTitle   = 'Testimonials — Darwin Art Company';
$currentPage = 'testimonials';

// Load the view
require_once __DIR__ . '/../views/testimonials.php';