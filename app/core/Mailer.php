<?php
/**
 * app/core/Mailer.php
 *
 * Email dispatch helper for the two automated emails the system sends
 * after a successful order:
 *   - sendBuyerConfirmation($order)  — confirms the order to the customer
 *   - sendOwnerNotification($order)  — alerts the store owner of a new sale
 *
 * WHY SMTP (and not PHP's mail()):
 *   PHP's built-in mail() cannot authenticate with a username/password, so
 *   it cannot deliver to Mailtrap (or any modern SMTP relay). This class
 *   therefore speaks SMTP directly using the credentials in config.php,
 *   which is what lets Mailtrap catch our development emails.
 *
 *   It is dependency-free (no Composer / PHPMailer required) so it runs on a
 *   stock XAMPP install. It supports STARTTLS (recommended) or a plain
 *   connection, selected via MAIL_ENCRYPTION in config.php.
 *
 * Configure these in config.php (see config.example.php):
 *   MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION,
 *   MAIL_FROM_ADDRESS, MAIL_FROM_NAME, MAIL_OWNER_ADDRESS
 *
 * Expected $order array keys:
 *   purchase_id, customer_name, customer_email,
 *   delivery_address, total_amount,
 *   items => [ [name, quantity, price], ... ]
 */

require_once __DIR__ . '/../../config.php';

class Mailer
{
    /** Send the buyer their order confirmation. Returns true on success. */
    public static function sendBuyerConfirmation(array $order): bool
    {
        $to      = $order['customer_email'];
        $subject = 'Your Darwin Art Store Order #' . (int)$order['purchase_id'];
        $body    = self::buildBuyerBody($order);

        // Reply-To the store so the buyer can reply to us.
        return self::send($to, $subject, $body, self::fromAddress());
    }

    /** Send the store owner a new-order notification. Returns true on success. */
    public static function sendOwnerNotification(array $order): bool
    {
        $to      = self::ownerAddress();
        $subject = 'New Order #' . (int)$order['purchase_id'] . ' — Darwin Art Store';
        $body    = self::buildOwnerBody($order);

        // Reply-To the customer's email so the owner can reply directly.
        return self::send($to, $subject, $body, $order['customer_email']);
    }

    // ------------------------------------------------------------------
    // Config helpers (with sensible fallbacks so nothing fatals if unset)
    // ------------------------------------------------------------------
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

    // ------------------------------------------------------------------
    // Email bodies (unchanged content)
    // ------------------------------------------------------------------
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

    // ------------------------------------------------------------------
    // SMTP transport
    // ------------------------------------------------------------------

    /**
     * Send one message. Uses authenticated SMTP when MAIL_HOST is configured
     * (the normal case — this is how mail reaches Mailtrap); otherwise falls
     * back to PHP mail() so the app still runs on a box with no SMTP set up.
     */
    private static function send(string $to, string $subject, string $body, string $replyTo): bool
    {
        if (!defined('MAIL_HOST') || MAIL_HOST === '') {
            // Fallback: native mail() (will NOT reach Mailtrap).
            $headers = implode("\r\n", [
                'From: ' . self::fromName() . ' <' . self::fromAddress() . '>',
                'Reply-To: ' . $replyTo,
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
            ]);
            return mail($to, $subject, $body, $headers);
        }

        try {
            return self::smtpSend($to, $subject, $body, $replyTo);
        } catch (Throwable $e) {
            error_log('Mailer SMTP error: ' . $e->getMessage());
            return false;
        }
    }

    private static function smtpSend(string $to, string $subject, string $body, string $replyTo): bool
    {
        $host = MAIL_HOST;
        $port = defined('MAIL_PORT') ? (int)MAIL_PORT : 2525;
        $enc  = defined('MAIL_ENCRYPTION') ? strtolower((string)MAIL_ENCRYPTION) : 'tls';
        $user = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
        $pass = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
        $timeout = 20;

        $transport = ($enc === 'ssl') ? "ssl://{$host}" : $host;
        $fp = @fsockopen($transport, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            throw new RuntimeException("Connect failed: $errstr ($errno)");
        }
        stream_set_timeout($fp, $timeout);

        self::expect($fp, 220);

        $ehloHost = 'localhost';
        self::cmd($fp, "EHLO {$ehloHost}", 250);

        if ($enc === 'tls') {
            self::cmd($fp, "STARTTLS", 220);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('STARTTLS negotiation failed');
            }
            self::cmd($fp, "EHLO {$ehloHost}", 250);
        }

        if ($user !== '') {
            self::cmd($fp, "AUTH LOGIN", 334);
            self::cmd($fp, base64_encode($user), 334);
            self::cmd($fp, base64_encode($pass), 235);
        }

        $from = self::fromAddress();
        self::cmd($fp, "MAIL FROM:<{$from}>", 250);
        self::cmd($fp, "RCPT TO:<{$to}>", [250, 251]);
        self::cmd($fp, "DATA", 354);

        $message = self::buildMessage($to, $subject, $body, $replyTo);
        // Dot-stuffing: any line starting with '.' gets an extra '.'.
        $message = preg_replace('/^\./m', '..', $message);
        fwrite($fp, $message . "\r\n.\r\n");
        self::expect($fp, 250);

        self::cmd($fp, "QUIT", 221);
        fclose($fp);
        return true;
    }

    private static function buildMessage(string $to, string $subject, string $body, string $replyTo): string
    {
        $from = self::fromName() . ' <' . self::fromAddress() . '>';
        $headers = [
            'Date: ' . date('r'),
            'From: ' . $from,
            'To: ' . $to,
            'Reply-To: ' . $replyTo,
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        // Normalise body line endings to CRLF for the wire.
        $bodyCrlf = preg_replace('/\r\n|\r|\n/', "\r\n", $body);
        return implode("\r\n", $headers) . "\r\n\r\n" . $bodyCrlf;
    }

    /** Send a command and assert the reply code. */
    private static function cmd($fp, string $line, $expected): string
    {
        fwrite($fp, $line . "\r\n");
        return self::expect($fp, $expected);
    }

    /** Read a (possibly multi-line) SMTP reply and assert its code. */
    private static function expect($fp, $expected): string
    {
        $expected = (array)$expected;
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            // Continuation lines look like "250-...", final line "250 ...".
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
