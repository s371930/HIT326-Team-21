<?php
/**
 * Checkout Controller
 * Collects customer details, saves the order to the database.
 * Uses Database transactions to ensure purchase + items save together.
 */

require_once __DIR__ . '/../models/Product.php';

$db = Database::getInstance();

// Redirect to cart if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: ' . BASE_URL . '/?page=cart');
    exit;
}

$cartItems = $_SESSION['cart'];
$cartTotal = 0;
foreach ($cartItems as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}

$error   = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitise input
    $email     = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');

    // Validate
    if ($email === '' || $firstName === '' || $lastName === '' || $address === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Use a transaction — purchase + items must save together
        $db->beginTransaction();
        try {
            // 1. Find or create customer by email
            $customer = $db->fetchOne(
                "SELECT customer_id FROM customer WHERE email = ?",
                [$email]
            );

            if ($customer) {
                $customerId = $customer['customer_id'];
            } else {
                $db->execute(
                    "INSERT INTO customer (email, first_name, last_name, phone)
                     VALUES (?, ?, ?, ?)",
                    [$email, $firstName, $lastName, $phone]
                );
                $customerId = (int) $db->lastInsertId();
            }

            // 2. Create the purchase record
            $db->execute(
                "INSERT INTO purchase (customer_id, total_amount, delivery_address, status)
                 VALUES (?, ?, ?, 'confirmed')",
                [$customerId, $cartTotal, $address]
            );
            $purchaseId = (int) $db->lastInsertId();

            // 3. Insert each cart item as a purchase_item
            foreach ($cartItems as $item) {
                $db->execute(
                    "INSERT INTO purchase_item (purchase_id, product_id, quantity, unit_price)
                     VALUES (?, ?, ?, ?)",
                    [$purchaseId, $item['product_id'], $item['quantity'], $item['price']]
                );
            }

            $db->commit();

            // Clear the cart
            $_SESSION['cart'] = [];
            $success = true;

        } catch (Exception $e) {
            $db->rollback();
            $error = 'Something went wrong while processing your order. Please try again.';
        }
    }
}

// Set page variables
$pageTitle   = 'Checkout — Darwin Art Company';
$currentPage = 'cart';

// Load views
require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../views/checkout.php';
require_once __DIR__ . '/../views/footer.php';