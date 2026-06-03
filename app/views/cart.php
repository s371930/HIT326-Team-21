<h1 class="mb-4">Shopping Cart</h1>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-light border text-center py-5">
        <p class="fs-5 mb-3">Your cart is empty.</p>
        <a href="<?= BASE_URL ?>/?page=products" class="btn btn-dark">Browse Artworks</a>
    </div>

<?php else: ?>
    <form method="POST" action="<?= BASE_URL ?>/?page=cart&action=update">
        <div class="table-responsive">
            <table class="table align-middle bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Artwork</th>
                        <th style="width:120px;">Price</th>
                        <th style="width:120px;">Qty</th>
                        <th style="width:120px;">Subtotal</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $id => $item): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/?page=product_detail&id=<?= $id ?>"
                                   class="text-decoration-none text-dark fw-bold">
                                    <?= htmlspecialchars($item['name']) ?>
                                </a>
                            </td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input type="number" name="qty[<?= $id ?>]"
                                       value="<?= $item['quantity'] ?>"
                                       min="0" max="99" class="form-control form-control-sm"
                                       style="width: 70px;">
                            </td>
                            <td class="fw-bold">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/?page=cart&action=remove&id=<?= $id ?>"
                                   class="btn btn-sm btn-outline-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold fs-5">Total:</td>
                        <td class="fw-bold fs-5">$<?= number_format($cartTotal, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-outline-dark">Update Cart</button>
            <a href="<?= BASE_URL ?>/?page=checkout" class="btn btn-dark btn-lg">Proceed to Checkout</a>
        </div>
    </form>
<?php endif; ?>