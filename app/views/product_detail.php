<!-- Back link -->
<a href="<?= BASE_URL ?>/?page=products" class="btn btn-outline-secondary btn-sm mb-4">
    &larr; Back to Artworks
</a>

<div class="row g-4">

    <!-- Left column: Image -->
    <div class="col-12 col-md-6">
        <?php
        $imagePath = __DIR__ . '/../../assets/images/' . $product['image_filename'];
        if ($product['image_filename'] && file_exists($imagePath)):
        ?>
            <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($product['image_filename']) ?>"
                 class="img-fluid rounded shadow-sm w-100"
                 alt="<?= htmlspecialchars($product['name']) ?>">
        <?php else: ?>
            <div class="img-placeholder rounded" style="height: 400px;">
                <?= htmlspecialchars($product['name']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right column: Details -->
    <div class="col-12 col-md-6">
        <h1 class="fw-bold"><?= htmlspecialchars($product['name']) ?></h1>

        <p class="text-secondary mb-3">
            <?= htmlspecialchars($product['category'] ?? 'Artwork') ?>
        </p>

        <hr>

        <p class="fs-5"><?= htmlspecialchars($product['description'] ?? 'No description available.') ?></p>

        <!-- Details table -->
        <table class="table table-borderless mt-3">
            <?php if ($product['size']): ?>
                <tr>
                    <th class="text-secondary" style="width: 100px;">Size</th>
                    <td><?= htmlspecialchars($product['size']) ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($product['color']): ?>
                <tr>
                    <th class="text-secondary">Colour</th>
                    <td><?= htmlspecialchars($product['color']) ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <hr>

        <!-- Price and Add to Cart -->
        <p class="display-6 fw-bold mb-4">$<?= number_format($product['price'], 2) ?></p>

        <a href="<?= BASE_URL ?>/?page=cart&action=add&id=<?= $product['product_id'] ?>"
           class="btn btn-dark btn-lg w-100">
            Add to Cart
        </a>
    </div>

</div>