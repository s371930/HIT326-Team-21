<div class="text-center py-4">
    <h1 class="text-success mb-3">&#10003; Order Placed Successfully!</h1>
    <p class="fs-5">
        Thank you for your purchase, <?= htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') ?>.
        Your order <strong>#<?= (int) $order['purchase_id'] ?></strong> has been confirmed.
    </p>
    <p class="text-secondary">
        A confirmation has been emailed to
        <?= htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8') ?>.
    </p>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">Order Summary</h5>

                <?php foreach ($order['items'] as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>
                            <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                            &times;<?= (int) $item['quantity'] ?>
                        </span>
                        <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>

                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span>$<?= number_format($order['total_amount'], 2) ?></span>
                </div>

                <hr>
                <h6 class="mb-1">Delivery Address</h6>
                <p class="text-secondary mb-0">
                    <?= nl2br(htmlspecialchars($order['delivery_address'], ENT_QUOTES, 'UTF-8')) ?>
                </p>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/?page=products" class="btn btn-dark">Continue Shopping</a>
            <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary">Back to Home</a>
        </div>
    </div>
</div>
