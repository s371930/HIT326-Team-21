<h1 class="mb-4">Shopping Cart</h1>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-light border text-center py-5">
        <p class="fs-5 mb-3">Your cart is empty.</p>
        <a href="<?= BASE_URL ?>/?page=products" class="btn btn-dark">Browse Artworks</a>
    </div>

<?php else: ?>
    <form method="POST" action="<?= BASE_URL ?>/?page=cart&action=update" id="cart-form">
        <div class="table-responsive">
            <table class="table align-middle bg-white" id="cart-table">
                <thead class="table-dark">
                    <tr>
                        <th>Artwork</th>
                        <th style="width:120px;">Price</th>
                        <th style="width:160px;">Qty</th>
                        <th style="width:120px;">Subtotal</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $id => $item): ?>
                        <tr data-id="<?= (int) $id ?>" data-price="<?= (float) $item['price'] ?>">
                            <td>
                                <a href="<?= BASE_URL ?>/?page=product_detail&id=<?= (int) $id ?>"
                                   class="text-decoration-none text-dark fw-bold">
                                    <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <div class="input-group input-group-sm" style="width: 140px;">
                                    <button type="button" class="btn btn-outline-secondary qty-step"
                                            data-dir="-1" aria-label="Decrease quantity">&minus;</button>
                                    <input type="number" name="qty[<?= (int) $id ?>]"
                                           value="<?= (int) $item['quantity'] ?>"
                                           min="0" max="99"
                                           class="form-control text-center qty-input"
                                           aria-label="Quantity">
                                    <button type="button" class="btn btn-outline-secondary qty-step"
                                            data-dir="1" aria-label="Increase quantity">&plus;</button>
                                </div>
                            </td>
                            <td class="fw-bold cart-subtotal">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/?page=cart&action=remove&id=<?= (int) $id ?>"
                                   class="btn btn-sm btn-outline-danger remove-item">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold fs-5">Total:</td>
                        <td class="fw-bold fs-5" id="cart-total">$<?= number_format($cartTotal, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-outline-dark" id="update-cart-btn">Update Cart</button>
            <a href="<?= BASE_URL ?>/?page=checkout" class="btn btn-dark btn-lg">Proceed to Checkout</a>
        </div>
    </form>

    <!-- Progressive enhancement: steppers, live totals, AJAX update/remove.
         The form above works on its own if this script doesn't load. -->
    <script src="<?= BASE_URL ?>/assets/js/cart.js"></script>
<?php endif; ?>
