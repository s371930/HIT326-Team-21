<?php
/**
 * Products Controller
 * Fetches artwork data and loads the products listing view.
 */

require_once __DIR__ . '/../models/Product.php';

$productModel = new Product();

// Check if a category filter was selected
$categoryFilter = $_GET['category'] ?? null;

// Fetch products (all or filtered by category)
if ($categoryFilter) {
    $products = $productModel->getByCategory($categoryFilter);
} else {
    $products = $productModel->getAllAvailable();
}

// Fetch categories for filter buttons
$categories = $productModel->getCategories();

// Set page variables for header
$pageTitle   = 'Artworks — Darwin Art Company';
$currentPage = 'products';

// Load views
require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../views/products.php';
require_once __DIR__ . '/../views/footer.php';