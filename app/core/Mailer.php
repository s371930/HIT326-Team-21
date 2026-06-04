<?php
/**
 * app/core/Mailer.php
 *
 * Email dispatch helper — wraps PHP's mail() function for the two
 * automated emails the system sends after a successful order.
 *
 * Two public methods:
 *   - sendBuyerConfirmation($order)  — confirms the order to the customer
 *   - sendOwnerNotification($order)  — alerts the store owner of a new sale
 *
 * Local development note:
 *   XAMPP cannot send real emails. Use Mailtrap (https://mailtrap.io)
 *   to catch outgoing mail during development and testing.
 *   See the project proposal's exclusions section for details.
 *
 * Expected $order array keys:
 *   purchase_id, customer_name, customer_email,
 *   delivery_address, total_amount,
 *   items => [ [name, quantity, price], ... ]
 *
 */

require_once __DIR__ . '/../../config.php';

class Mailer
{
    /**
     * The "From" address shown in every outgoing email.
     * Uses a local domain since this runs on localhost during development.
     */
    private const FROM_ADDRESS = 'noreply@darwin-art-store.local';
    private const FROM_NAME    = 'Darwin Art Store';

    /**
     * The store owner's email address.
     * Owner notifications are sent here whenever a new order is placed.
     */
    private const OWNER_EMAIL  = 'owner@darwin-art-store.local';

    /**
     * Send an order confirmation email to the customer (buyer).
     *
     * Called from order-confirmation.php after a successful checkout.
     *
     * @param array $order  Order data — see class docblock for required keys
     * @return bool         true if mail() accepted the message for delivery
     */
    public static function sendBuyerConfirmation(array $order): bool
    {
        $to      = $order['customer_email'];
        $subject = 'Your Darwin Art Store Order #' . (int)$order['purchase_id'];
        $body    = self::buildBuyerBody($order);
        $headers = self::buildHeaders($to);

        return @mail($to, $subject, $body, $headers);
    }

    /**
     * Send a new-order notification email to the store owner/admin.
     *
     * Called from order-confirmation.php after a successful checkout,
     * alongside sendBuyerConfirmation().
     *
     * @param array $order  Order data — see class docblock for required keys
     * @return bool         true if mail() accepted the message for delivery
     */
    public static function sendOwnerNotification(array $order): bool
    {
        $to      = self::OWNER_EMAIL;
        $subject = 'New Order #' . (int)$order['purchase_id'] . ' — Darwin Art Store';
        $body    = self::buildOwnerBody($order);

        // Set Reply-To as the customer's email so the owner can reply directly
        $headers = self::buildHeaders($order['customer_email']);

        return @mail($to, $subject, $body, $headers);
    }


    /**
     * Build the plain-text email body sent to the buyer.
     * All user-supplied values are escaped before being inserted.
     */
    private static function buildBuyerBody(array $order): string
    {
        // Escape all values before placing them in the email body
        $name    = htmlspecialchars($order['customer_name'],    ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($order['delivery_address'], ENT_QUOTES, 'UTF-8');
        $id      = (int)$order['purchase_id'];
        $total   = number_format((float)$order['total_amount'], 2);
        $items   = self::formatItems($order['items'] ?? []);

        return <<<TEXT
Hello {$name},

Thank you for your order! Here is your order summary:

Order #: {$id}
Delivery Address: {$address}

Items:
{$items}

Order Total: \${$total}

We will be in touch regarding delivery. If you have any questions,
please reply to this email.

— Darwin Art Store
TEXT;
    }

    /**
     * Build the plain-text email body sent to the store owner.
     * Includes full customer contact details so the owner can follow up.
     */
    private static function buildOwnerBody(array $order): string
    {
        $name    = htmlspecialchars($order['customer_name'],    ENT_QUOTES, 'UTF-8');
        $email   = htmlspecialchars($order['customer_email'],   ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($order['delivery_address'], ENT_QUOTES, 'UTF-8');
        $id      = (int)$order['purchase_id'];
        $total   = number_format((float)$order['total_amount'], 2);
        $items   = self::formatItems($order['items'] ?? []);

        return <<<TEXT
A new order has been placed.

Order #: {$id}
Customer: {$name} <{$email}>
Delivery Address: {$address}

Items:
{$items}

Order Total: \${$total}

Log in to the admin panel to view the full order.
TEXT;
    }

    /**
     * Format the items array into a readable plain-text list.
     * Each line shows: item name, quantity, and unit price.
     *
     * @param  array  $items  Array of item arrays with keys: name, quantity, price
     * @return string         Formatted multi-line string, or a placeholder if empty
     */
    private static function formatItems(array $items): string
    {
        if (empty($items)) {
            return '  (no items)';
        }

        $lines = [];
        foreach ($items as $item) {
            $name    = htmlspecialchars($item['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
            $qty     = (int)($item['quantity'] ?? 1);
            $price   = number_format((float)($item['price'] ?? 0), 2);
            $lines[] = "  - {$name} x{$qty} @ \${$price}";
        }

        return implode("\n", $lines);
    }

    /**
     * Build the standard email headers used by both outgoing emails.
     *
     * @param  string $replyTo  Address to set as Reply-To
     * @return string           Formatted header string for mail()
     */
    private static function buildHeaders(string $replyTo): string
    {
        $from = self::FROM_NAME . ' <' . self::FROM_ADDRESS . '>';

        return implode("\r\n", [
            'From: '     . $from,
            'Reply-To: ' . $replyTo,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ]);
    }
}
