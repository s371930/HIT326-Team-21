<h1 class="mb-4">Our Artworks</h1>

<!-- Category Filter Buttons -->
<div class="mb-4">
    <a href="<?= BASE_URL ?>/?page=products"
       class="btn btn-sm <?= !$categoryFilter ? 'btn-dark' : 'btn-outline-dark' ?> me-1 mb-1">
        All
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>/?page=products&category=<?= urlencode($cat['category']) ?>"
           class="btn btn-sm <?= $categoryFilter === $cat['category'] ? 'btn-dark' : 'btn-outline-dark' ?> me-1 mb-1">
            <?= htmlspecialchars($cat['category']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Artworks Grid -->
<?php if (empty($products)): ?>
    <div class="alert alert-info">No artworks found in this category.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">

                    <!-- Image or Placeholder -->
                    <?php
                    $imagePath = __DIR__ . '/../../assets/images/' . $product['image_filename'];
                    if ($product['image_filename'] && file_exists($imagePath)):
                    ?>
                        <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($product['image_filename']) ?>"
                             class="card-img-top img-fluid"
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php else: ?>
                        <div class="img-placeholder">
                            <?= htmlspecialchars($product['name']) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Card Body -->
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>

                        <p class="text-secondary mb-1">
                            <small><?= htmlspecialchars($product['category'] ?? 'Artwork') ?>
                            &middot; <?= htmlspecialchars($product['size'] ?? '') ?></small>
                        </p>

                        <p class="card-text flex-grow-1">
                            <?= htmlspecialchars(mb_strimwidth($product['description'] ?? '', 0, 100, '...')) ?>
                        </p>

                        <p class="fw-bold fs-5 mb-3">$<?= number_format($product['price'], 2) ?></p>

                        <div class="mt-auto d-flex gap-2">
                            <a href="<?= BASE_URL ?>/?page=product_detail&id=<?= $product['product_id'] ?>"
                               class="btn btn-outline-dark flex-fill">
                                View Details
                            </a>
                            <a href="<?= BASE_URL ?>/?page=cart&action=add&id=<?= $product['product_id'] ?>"
                               class="btn btn-dark flex-fill">
                                Add to Cart
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>