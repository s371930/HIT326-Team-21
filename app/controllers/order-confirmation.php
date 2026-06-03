<?php
/**
 * Order Confirmation Controller
 * Thank-you page shown after a successful checkout. Reads the order summary
 * stashed in the session by checkout.php, triggers the confirmation emails
 * (Opeoluwa's Mailer), and renders the summary.
 *
 * Reached only via the Post/Redirect/Get from checkout. If there is no order
 * in the session (e.g. someone navigates here directly), send them home.
 */

require_once __DIR__ . '/../core/Mailer.php';

if (empty($_SESSION['last_order'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$order = $_SESSION['last_order'];

// Send the two automated emails exactly once. The guard means refreshing
// this page (or coming back to it) won't fire duplicate emails. The order is
// already saved, so we don't fail the page if mail() can't deliver locally
// (XAMPP can't send real mail — Mailtrap catches it in development).
if (empty($order['emails_sent'])) {
    Mailer::sendBuyerConfirmation($order);
    Mailer::sendOwnerNotification($order);
    $_SESSION['last_order']['emails_sent'] = true;
}

$pageTitle   = 'Order Confirmation — Darwin Art Company';
$currentPage = 'cart';

require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../views/order-confirmation.php';
require_once __DIR__ . '/../views/footer.php';
