<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Darwin Art Company') ?></title>

    <!-- Bootstrap 5 CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Our custom styles -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">

            <!-- Site name / logo -->
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/">
                &#127912; Darwin Art Company
            </a>

            <!-- Hamburger button for mobile -->
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav links (collapse on mobile) -->
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'products' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/?page=products">Artworks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'cart' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/?page=cart">Cart</a>
                    </li>
                </ul>

                <!-- Admin link on the right side -->
                <ul class="navbar-nav">
                    <?php if (Auth::isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php">Admin</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </nav>

    <!-- Main content area -->
    <main class="container my-4">