<?php
/**
 * admin/admin-header.php
 *
 * Shared header template included at the top of every admin page.
 *
 * Outputs:
 *   - The opening HTML (DOCTYPE, <head>, Bootstrap CSS)
 *   - A responsive Bootstrap 5 navbar with links to all admin sections
 *
 * Usage — add this at the top of any admin page (after PHP logic):
 *   include __DIR__ . '/admin-header.php';
 *
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Viewport meta tag is required for mobile responsiveness (rubric item) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Darwin Art Store</title>
    <!-- Bootstrap 5 CDN — provides the responsive grid and navbar collapse on mobile -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body         { background-color: #f8f8f8; font-family: Georgia, serif; }
        /* Dark navbar to visually separate the admin panel from the public site */
        .navbar      { background-color: #2c2c2c !important; }
        .navbar-brand{ color: #fff !important; font-size: 1rem; letter-spacing: 0.05em; }
        .nav-link    { color: #ccc !important; font-size: 0.9rem; }
        .nav-link:hover, .nav-link.active { color: #fff !important; }
    </style>
</head>
<body>

<!-- Responsive navbar — collapses to a hamburger menu on small screens -->
<nav class="navbar navbar-expand-md navbar-dark">
    <div class="container-fluid">
        <!-- Brand link always returns admin to the dashboard -->
        <a class="navbar-brand" href="index.php">Darwin Art — Admin</a>

        <!-- Hamburger toggle shown on mobile (md breakpoint and below) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation links — collapse on mobile, inline on desktop -->
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">News</a></li>
                <li class="nav-item"><a class="nav-link" href="testimonials.php">Testimonials</a></li>
                <!-- Logout is styled red to make it visually distinct from navigation links -->
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
