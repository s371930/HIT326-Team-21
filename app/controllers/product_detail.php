<?php
/**
 * Product Detail Controller
 * Shows a single artwork with full details.
 */

require_once __DIR__ . '/../models/Product.php';

$productModel = new Product();

// Get the product ID from the URL and make sure it's a number
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// If no valid ID, redirect to products page
if (!$id) {
    header('Location: ' . BASE_URL . '/?page=products');
    exit;
}

// Fetch the product
$product = $productModel->getById($id);

// If product not found, show products page
if (!$product) {
    header('Location: ' . BASE_URL . '/?page=products');
    exit;
}

// Set page variables for header
$pageTitle   = htmlspecialchars($product['name']) . ' — Darwin Art Company';
$currentPage = 'products';

// Load views
require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../views/product_detail.php';
require_once __DIR__ . '/../views/footer.php';