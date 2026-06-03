<?php
/**
 * Cart Controller
 * Stores cart items in the session via the Cart helper. No database is
 * touched here except to validate a product when it is first added.
 *
 * Actions (via ?page=cart&action=...):
 *   add     (GET)  ?id=N           add one of product N
 *   remove  (GET)  ?id=N           remove product N
 *   update  (POST) qty[N]=Q ...    bulk update from the cart form
 *
 * Each action also supports an AJAX variant (cart.js). When the request
 * is AJAX we return JSON and exit; otherwise we redirect back to the cart
 * so the page keeps working with JavaScript disabled.
 */

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../core/Cart.php';

Cart::init();

$productModel = new Product();
$action       = $_GET['action'] ?? null;

/** Is this an AJAX (fetch) request rather than a normal page navigation? */
function cart_wants_json(): bool
{
    if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
        return true;
    }
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

/** Send a JSON response and stop. */
function cart_json(array $data): void
{
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}

/** Redirect back to the cart page (non-AJAX fallback). */
function cart_redirect(): void
{
    header('Location: ' . BASE_URL . '/?page=cart');
    exit;
}

// --- ADD item to cart -------------------------------------------------------
if ($action === 'add') {
    $id      = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $product = $id ? $productModel->getById($id) : null;

    if ($product) {
        // Normalise to the shape Cart expects before storing it.
        Cart::add([
            'product_id' => $product['product_id'],
            'name'       => $product['name'],
            'price'      => $product['price'],
            'image'      => $product['image_filename'],
        ]);
    }

    if (cart_wants_json()) {
        cart_json([
            'ok'         => (bool) $product,
            'cart_total' => Cart::total(),
            'cart_count' => Cart::count(),
        ]);
    }
    cart_redirect();
}

// --- REMOVE item from cart --------------------------------------------------
if ($action === 'remove') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
        ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        Cart::remove($id);
    }

    if (cart_wants_json()) {
        cart_json([
            'ok'         => true,
            'removed'    => (int) $id,
            'cart_total' => Cart::total(),
            'cart_count' => Cart::count(),
        ]);
    }
    cart_redirect();
}

// --- UPDATE quantities ------------------------------------------------------
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // AJAX single-line update: { id, qty }
    if (cart_wants_json()) {
        $id  = filter_input(INPUT_POST, 'id',  FILTER_VALIDATE_INT);
        $qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);

        if ($id) {
            Cart::setQty($id, (int) $qty); // 0 (or less) removes the line
        }

        $line = $id ? Cart::get($id) : null;
        cart_json([
            'ok'            => true,
            'removed'       => $line === null,
            'line_quantity' => $line['quantity'] ?? 0,
            'line_subtotal' => $id ? Cart::lineSubtotal($id) : 0,
            'cart_total'    => Cart::total(),
            'cart_count'    => Cart::count(),
        ]);
    }

    // Non-AJAX bulk update from the cart form: qty[ID] = Q
    $quantities = $_POST['qty'] ?? [];
    foreach ($quantities as $id => $qty) {
        Cart::setQty((int) $id, (int) $qty);
    }
    cart_redirect();
}

// --- Render the cart page ---------------------------------------------------
$cartItems = Cart::items();
$cartTotal = Cart::total();

$pageTitle   = 'Shopping Cart — Darwin Art Company';
$currentPage = 'cart';

require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../views/cart.php';
require_once __DIR__ . '/../views/footer.php';
