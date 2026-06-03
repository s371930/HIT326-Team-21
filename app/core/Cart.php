<?php
/**
 * Cart — shopping-cart session logic
 *
 * The cart lives entirely in $_SESSION['cart'] as an associative array
 * keyed by product_id. Each entry is a self-contained line item:
 *
 *   $_SESSION['cart'][12] = [
 *       'product_id' => 12,
 *       'name'       => 'Sunset over Mindil',
 *       'price'      => 450.00,   // price captured when added (display only)
 *       'quantity'   => 2,
 *       'image'      => 'sunset_mindil.jpg',
 *   ];
 *
 * No database access happens here — the cart is pure session state, so
 * these methods can be tested with hardcoded product arrays (see
 * tests/cart_test.php). The authoritative price used for the actual
 * order is re-fetched from the database at checkout, not taken from here.
 *
 * Usage:
 *
 *   require_once __DIR__ . '/../core/Cart.php';
 *   Cart::init();                       // ensure the cart array exists
 *   Cart::add($lineItem);               // add or increment
 *   Cart::setQty($id, $qty);            // set quantity (0 removes)
 *   Cart::remove($id);                  // remove a line
 *   $items = Cart::items();             // all lines
 *   $total = Cart::total();             // sum of price * quantity
 *   $count = Cart::count();             // sum of quantities
 *   Cart::clear();                      // empty the cart (after checkout)
 */

class Cart
{
    /** Session key under which the cart is stored. */
    private const KEY = 'cart';

    /**
     * Ensure the cart array exists in the session.
     * Safe to call on every request.
     */
    public static function init(): void
    {
        if (!isset($_SESSION[self::KEY]) || !is_array($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
    }

    /**
     * Add a product to the cart, or increase its quantity if already present.
     *
     * @param array $product Must contain: product_id, name, price.
     *                       May contain: image. Other keys are ignored.
     * @param int   $qty     Quantity to add (clamped to at least 1).
     */
    public static function add(array $product, int $qty = 1): void
    {
        self::init();

        $id  = (int) ($product['product_id'] ?? 0);
        $qty = max(1, $qty);
        if ($id <= 0) {
            return; // ignore malformed products
        }

        if (isset($_SESSION[self::KEY][$id])) {
            $_SESSION[self::KEY][$id]['quantity'] += $qty;
        } else {
            $_SESSION[self::KEY][$id] = [
                'product_id' => $id,
                'name'       => (string) ($product['name'] ?? 'Unknown'),
                'price'      => (float) ($product['price'] ?? 0),
                'quantity'   => $qty,
                'image'      => $product['image'] ?? ($product['image_filename'] ?? null),
            ];
        }

        // Never let a line exceed a sensible ceiling.
        self::clampQuantity($id);
    }

    /**
     * Set the exact quantity of a line item.
     * A quantity of 0 (or less) removes the item entirely.
     */
    public static function setQty(int $id, int $qty): void
    {
        self::init();
        $id = (int) $id;

        if (!isset($_SESSION[self::KEY][$id])) {
            return;
        }

        if ($qty <= 0) {
            self::remove($id);
            return;
        }

        $_SESSION[self::KEY][$id]['quantity'] = $qty;
        self::clampQuantity($id);
    }

    /**
     * Remove a line item from the cart.
     */
    public static function remove(int $id): void
    {
        self::init();
        unset($_SESSION[self::KEY][(int) $id]);
    }

    /**
     * Get all line items (associative array keyed by product_id).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function items(): array
    {
        self::init();
        return $_SESSION[self::KEY];
    }

    /**
     * Get a single line item, or null if it isn't in the cart.
     */
    public static function get(int $id): ?array
    {
        self::init();
        return $_SESSION[self::KEY][(int) $id] ?? null;
    }

    /**
     * Total cost of the cart (sum of price * quantity for every line).
     */
    public static function total(): float
    {
        $total = 0.0;
        foreach (self::items() as $item) {
            $total += (float) $item['price'] * (int) $item['quantity'];
        }
        return $total;
    }

    /**
     * Total number of individual units across all lines.
     * Handy for a "items in cart" badge.
     */
    public static function count(): int
    {
        $count = 0;
        foreach (self::items() as $item) {
            $count += (int) $item['quantity'];
        }
        return $count;
    }

    /**
     * Subtotal for one line (price * quantity), or 0 if not in the cart.
     */
    public static function lineSubtotal(int $id): float
    {
        $item = self::get($id);
        return $item === null
            ? 0.0
            : (float) $item['price'] * (int) $item['quantity'];
    }

    /**
     * Is the cart empty?
     */
    public static function isEmpty(): bool
    {
        return self::items() === [];
    }

    /**
     * Empty the cart completely. Called after a successful checkout.
     */
    public static function clear(): void
    {
        $_SESSION[self::KEY] = [];
    }

    /**
     * Keep a line's quantity within 1..99 so a bad value can't break totals.
     */
    private static function clampQuantity(int $id): void
    {
        if (!isset($_SESSION[self::KEY][$id])) {
            return;
        }
        $qty = (int) $_SESSION[self::KEY][$id]['quantity'];
        $_SESSION[self::KEY][$id]['quantity'] = max(1, min(99, $qty));
    }
}
