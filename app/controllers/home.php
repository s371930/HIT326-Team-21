<?php


require_once __DIR__ . '/../models/News.php';
require_once __DIR__ . '/../models/Product.php';

// Fetch the latest news item (only one, per project requirement)
$newsModel  = new News();
$latestNews = $newsModel->getLatest();

// Fetch a few featured artworks for the home page
$productModel    = new Product();
$featuredProducts = $productModel->getAllAvailable();
// Show only the first 3 as "featured"
$featuredProducts = array_slice($featuredProducts, 0, 3);

// Set page variables for header
$pageTitle   = 'Home — Darwin Art Company';
$currentPage = 'home';

// Load views
require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../views/home.php';
require_once __DIR__ . '/../views/footer.php';