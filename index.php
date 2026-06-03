<?php
/**
 * index.php — Front Controller
 * Every page request enters here and is routed to the correct controller.
 */

// Load configuration and core classes
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Auth.php';

// Start session (needed for cart and admin login)
Auth::start();

// Read which page was requested (default to 'home')
$page = $_GET['page'] ?? 'home';

// Only allow pages that exist (prevents directory traversal attacks)
$allowed = ['home', 'products', 'product_detail', 'cart', 'checkout', 'order-confirmation'];

if (!in_array($page, $allowed)) {
    $page = 'home';
}

// Load the controller for the requested page
require_once __DIR__ . '/app/controllers/' . $page . '.php';