<?php


require_once __DIR__ . '/../core/Mailer.php';

if (empty($_SESSION['last_order'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$order = $_SESSION['last_order'];


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
