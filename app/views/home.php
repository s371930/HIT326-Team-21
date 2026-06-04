<!-- Hero Section -->
<div class="text-center py-5 mb-4 bg-white rounded shadow-sm">
    <h1 class="display-5 fw-bold">Welcome to Darwin Art Company</h1>
    <p class="lead text-secondary mt-3">
        Discover unique handcrafted artworks inspired by the beauty of Australia's Top End.
    </p>
    <a href="<?= BASE_URL ?>/?page=products" class="btn btn-dark btn-lg mt-2">
        Browse Artworks
    </a>
</div>


<section class="mb-5">
    <h2 class="mb-3">Latest News</h2>

    <?php if ($latestNews): ?>
        <div class="news-section">
            <h3><?= htmlspecialchars($latestNews['title']) ?></h3>
            <small class="text-secondary d-block mb-3">
                Posted on <?= date('j F Y', strtotime($latestNews['posted_at'])) ?>
            </small>
            <p class="mb-0"><?= nl2br(htmlspecialchars($latestNews['content'])) ?></p>
        </div>
    <?php else: ?>
        <div class="alert alert-light border">
            No news at the moment. Check back soon!
        </div>
    <?php endif; ?>

   
</section>


<!-- Featured Artworks Section   -->

<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Featured Artworks</h2>
        <a href="<?= BASE_URL ?>/?page=products" class="btn btn-outline-dark btn-sm">
            View All &rarr;
        </a>
    </div>

    <div class="row g-4">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
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

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="fw-bold fs-5 mb-3">$<?= number_format($product['price'], 2) ?></p>
                        <a href="<?= BASE_URL ?>/?page=product_detail&id=<?= $product['product_id'] ?>"
                           class="btn btn-outline-dark mt-auto">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>