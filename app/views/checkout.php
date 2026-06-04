<h1 class="mb-4">Checkout</h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row g-4">

    <!-- Left: Customer Form -->
    <div class="col-12 col-md-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">Your Details</h5>
                <form method="POST" action="<?= BASE_URL ?>/?page=checkout" novalidate>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" name="first_name" id="first_name" class="form-control"
                                   required maxlength="100"
                                   value="<?= htmlspecialchars($old['first_name'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" name="last_name" id="last_name" class="form-control"
                                   required maxlength="100"
                                   value="<?= htmlspecialchars($old['last_name'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   required maxlength="255"
                                   value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" name="phone" id="phone" class="form-control"
                                   maxlength="30"
                                   value="<?= htmlspecialchars($old['phone'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Delivery Address *</label>
                            <textarea name="address" id="address" class="form-control" rows="3"
                                      required><?= htmlspecialchars($old['address'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark btn-lg w-100 mt-4">Place Order</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Order Summary -->
    <div class="col-12 col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">Order Summary</h5>
                <?php foreach ($lineItems as $li): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>
                            <?= htmlspecialchars($li['name'], ENT_QUOTES, 'UTF-8') ?>
                            &times;<?= (int) $li['quantity'] ?>
                        </span>
                        <span>$<?= number_format($li['subtotal'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span>$<?= number_format($cartTotal, 2) ?></span>
                </div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/?page=cart" class="btn btn-outline-secondary w-100 mt-2">
            &larr; Back to Cart
        </a>
    </div>

</div>
