<?php
/**
 * Cart Controller
 * Stores cart items in the session. No database needed until checkout.
 */

require_once __DIR__ . '/../models/Product.php';

$productModel = new Product();

// Initialise the cart array in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? null;

// --- ADD item to cart ---
if ($action === 'add') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $product = $productModel->getById($id);
        if ($product) {
            // If already in cart, increase quantity
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity']++;
            } else {
                $_SESSION['cart'][$id] = [
                    'product_id' => $product['product_id'],
                    'name'       => $product['name'],
                    'price'      => $product['price'],
                    'quantity'   => 1,
                    'image'      => $product['image_filename'],
                ];
            }
        }
    }
    header('Location: ' . BASE_URL . '/?page=cart');
    exit;
}

// --- REMOVE item from cart ---
if ($action === 'remove') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header('Location: ' . BASE_URL . '/?page=cart');
    exit;
}

// --- UPDATE quantities (from form submission) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action ?? '') === 'update') {
    $quantities = $_POST['qty'] ?? [];
    foreach ($quantities as $id => $qty) {
        $id  = (int) $id;
        $qty = (int) $qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } elseif (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
    header('Location: ' . BASE_URL . '/?page=cart');
    exit;
}

// Calculate totals for the view
$cartItems = $_SESSION['cart'];
$cartTotal = 0;
foreach ($cartItems as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}

// Set page variables
$pageTitle   = 'Shopping Cart — Darwin Art Company';
$currentPage = 'cart';

// Load views
require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../views/cart.php';
require_once __DIR__ . '/../views/footer.php';