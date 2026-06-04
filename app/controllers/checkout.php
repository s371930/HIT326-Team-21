<?php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../core/Cart.php';

$db = Database::getInstance();
Cart::init();

// Nothing to check out — send them back to the (empty) cart.
if (Cart::isEmpty()) {
    header('Location: ' . BASE_URL . '/?page=cart');
    exit;
}

$productModel = new Product();

$lineItems   = [];
$unavailable = [];
foreach (Cart::items() as $id => $item) {
    $product = $productModel->getById((int) $id);
    if ($product === null) {
        $unavailable[] = $item['name'];
        continue;
    }
    $qty = (int) $item['quantity'];
    $lineItems[] = [
        'product_id' => (int) $product['product_id'],
        'name'       => $product['name'],
        'unit_price' => (float) $product['price'],
        'quantity'   => $qty,
        'subtotal'   => (float) $product['price'] * $qty,
    ];
}

$cartTotal = array_sum(array_column($lineItems, 'subtotal'));

$error = '';
// Preserve submitted values so the form can be re-rendered on error.
$old = [
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
    'phone'      => '',
    'address'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim input.
    foreach ($old as $field => $_) {
        $old[$field] = trim($_POST[$field] ?? '');
    }

    // Server-side validation (the form also validates client-side).
    if (!empty($unavailable)) {
        $error = 'Some items are no longer available: '
               . implode(', ', $unavailable)
               . '. Please review your cart.';
    } elseif (empty($lineItems)) {
        $error = 'Your cart is empty.';
    } elseif ($old['first_name'] === '' || $old['last_name'] === ''
           || $old['email'] === '' || $old['address'] === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // All good — write the order atomically.
        $db->beginTransaction();
        try {
            // 1. Find or create the customer (guest checkout, keyed by email).
            $customer = $db->fetchOne(
                "SELECT customer_id FROM customer WHERE email = ?",
                [$old['email']]
            );

            if ($customer) {
                $customerId = (int) $customer['customer_id'];
            } else {
                $db->execute(
                    "INSERT INTO customer (email, first_name, last_name, phone)
                     VALUES (?, ?, ?, ?)",
                    [$old['email'], $old['first_name'], $old['last_name'], $old['phone']]
                );
                $customerId = (int) $db->lastInsertId();
            }

            // 2. Create the purchase header.
            $db->execute(
                "INSERT INTO purchase (customer_id, total_amount, delivery_address, status)
                 VALUES (?, ?, ?, 'confirmed')",
                [$customerId, $cartTotal, $old['address']]
            );
            $purchaseId = (int) $db->lastInsertId();

            // 3. Insert each line item with the authoritative unit price.
            foreach ($lineItems as $li) {
                $db->execute(
                    "INSERT INTO purchase_item (purchase_id, product_id, quantity, unit_price)
                     VALUES (?, ?, ?, ?)",
                    [$purchaseId, $li['product_id'], $li['quantity'], $li['unit_price']]
                );
            }

            $db->commit();

          
            $_SESSION['last_order'] = [
                'purchase_id'      => $purchaseId,
                'customer_name'    => $old['first_name'] . ' ' . $old['last_name'],
                'customer_email'   => $old['email'],
                'delivery_address' => $old['address'],
                'total_amount'     => $cartTotal,
                'items'            => array_map(function ($li) {
                    return [
                        'name'     => $li['name'],
                        'quantity' => $li['quantity'],
                        'price'    => $li['unit_price'],
                    ];
                }, $lineItems),
                'emails_sent'      => false,
            ];

            Cart::clear();

            // Post/Redirect/Get — refreshing the confirmation won't re-order.
            header('Location: ' . BASE_URL . '/?page=order-confirmation');
            exit;

        } catch (Throwable $e) {
            $db->rollback();
            $error = 'Something went wrong while processing your order. Please try again.';
        }
    }
}

$pageTitle   = 'Checkout — Darwin Art Company';
$currentPage = 'cart';

require_once __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../views/checkout.php';
require_once __DIR__ . '/../views/footer.php';
