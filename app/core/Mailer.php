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
 *   Per the project proposal's exclusions section, email may be simulated.
 *
 */

require_once __DIR__ . '/../../config.php';

class Mailer
{
    // -------------------------------------------------------------------------
    // Config helpers — read from config.php constants with sensible fallbacks
    // -------------------------------------------------------------------------

    private static function fromAddress(): string
    {
        return defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'noreply@darwin-art-store.local';
    }

    private static function fromName(): string
    {
        return defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Darwin Art Store';
    }

    private static function ownerAddress(): string
    {
        return defined('MAIL_OWNER_ADDRESS') ? MAIL_OWNER_ADDRESS : 'owner@darwin-art-store.local';
    }

    // -------------------------------------------------------------------------
    // Public methods
    // -------------------------------------------------------------------------

    /**
     * Send an order confirmation email to the customer (buyer).
     *
     * @param array $order  Order data (purchase_id, customer_name, customer_email,
     *                      delivery_address, total_amount, items)
     * @return bool         true if mail was accepted
     */
    public static function sendBuyerConfirmation(array $order): bool
    {
        $to      = $order['customer_email'];
        $subject = 'Your Darwin Art Store Order #' . (int)$order['purchase_id'];
        $body    = self::buildBuyerBody($order);

        // Reply-To the store so the buyer can reply to us
        return self::send($to, $subject, $body, self::fromAddress());
    }

    /**
     * Send a new-order notification email to the store owner/admin.
     *
     * @param array $order  Order data (same structure as sendBuyerConfirmation)
     * @return bool         true if mail was accepted
     */
    public static function sendOwnerNotification(array $order): bool
    {
        $to      = self::ownerAddress();
        $subject = 'New Order #' . (int)$order['purchase_id'] . ' — Darwin Art Store';
        $body    = self::buildOwnerBody($order);

        // Reply-To the customer's email so the owner can reply directly
        return self::send($to, $subject, $body, $order['customer_email']);
    }

    // -------------------------------------------------------------------------
    // Email body builders
    // -------------------------------------------------------------------------

    private static function buildBuyerBody(array $order): string
    {
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

    // -------------------------------------------------------------------------
    // Transport layer
    // -------------------------------------------------------------------------

    /**
     * Send one message. Uses authenticated SMTP when MAIL_HOST is configured
     * (e.g. Mailtrap); otherwise falls back to PHP mail() so the app still
     * runs on a box with no SMTP configured.
     * The @ suppresses the XAMPP mail() warning during local development.
     */
    private static function send(string $to, string $subject, string $body, string $replyTo): bool
    {
        if (!defined('MAIL_HOST') || MAIL_HOST === '') {
            // Fallback: native mail() — will not reach Mailtrap but won't crash
            $headers = implode("\r\n", [
                'From: ' . self::fromName() . ' <' . self::fromAddress() . '>',
                'Reply-To: ' . $replyTo,
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
            ]);
            // @ suppresses the XAMPP "no mailserver" warning during local dev
            return @mail($to, $subject, $body, $headers);
        }

        // SMTP configured — use authenticated send (works with Mailtrap)
        try {
            return self::smtpSend($to, $subject, $body, $replyTo);
        } catch (Throwable $e) {
            error_log('Mailer SMTP error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send via raw SMTP socket — no external libraries needed.
     * Supports STARTTLS and AUTH LOGIN (required by Mailtrap).
     */
    private static function smtpSend(string $to, string $subject, string $body, string $replyTo): bool
    {
        $host    = MAIL_HOST;
        $port    = defined('MAIL_PORT')       ? (int)MAIL_PORT                   : 2525;
        $enc     = defined('MAIL_ENCRYPTION') ? strtolower((string)MAIL_ENCRYPTION) : 'tls';
        $user    = defined('MAIL_USERNAME')   ? MAIL_USERNAME                    : '';
        $pass    = defined('MAIL_PASSWORD')   ? MAIL_PASSWORD                    : '';
        $timeout = 20;

        $transport = ($enc === 'ssl') ? "ssl://{$host}" : $host;
        $fp = @fsockopen($transport, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            throw new RuntimeException("Connect failed: $errstr ($errno)");
        }
        stream_set_timeout($fp, $timeout);

        self::expect($fp, 220);
        self::cmd($fp, "EHLO localhost", 250);

        if ($enc === 'tls') {
            self::cmd($fp, "STARTTLS", 220);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('STARTTLS negotiation failed');
            }
            self::cmd($fp, "EHLO localhost", 250);
        }

        if ($user !== '') {
            self::cmd($fp, "AUTH LOGIN", 334);
            self::cmd($fp, base64_encode($user), 334);
            self::cmd($fp, base64_encode($pass), 235);
        }

        $from = self::fromAddress();
        self::cmd($fp, "MAIL FROM:<{$from}>", 250);
        self::cmd($fp, "RCPT TO:<{$to}>",     [250, 251]);
        self::cmd($fp, "DATA", 354);

        $message = self::buildMessage($to, $subject, $body, $replyTo);
        $message = preg_replace('/^\./m', '..', $message); // dot-stuffing
        fwrite($fp, $message . "\r\n.\r\n");
        self::expect($fp, 250);

        self::cmd($fp, "QUIT", 221);
        fclose($fp);
        return true;
    }

    private static function buildMessage(string $to, string $subject, string $body, string $replyTo): string
    {
        $from    = self::fromName() . ' <' . self::fromAddress() . '>';
        $headers = [
            'Date: '         . date('r'),
            'From: '         . $from,
            'To: '           . $to,
            'Reply-To: '     . $replyTo,
            'Subject: '      . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        $bodyCrlf = preg_replace('/\r\n|\r|\n/', "\r\n", $body);
        return implode("\r\n", $headers) . "\r\n\r\n" . $bodyCrlf;
    }

    private static function cmd($fp, string $line, $expected): string
    {
        fwrite($fp, $line . "\r\n");
        return self::expect($fp, $expected);
    }

    private static function expect($fp, $expected): string
    {
        $expected = (array)$expected;
        $data     = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int)substr($data, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new RuntimeException('Unexpected SMTP reply: ' . trim($data));
        }
        return $data;
    }
}