<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/Auth.php';

final class Mailer
{
    public static function orderPlaced(array $order): void
    {
        $code = (string) ($order['public_code'] ?? '');
        $subject = 'KDesigns order received' . ($code !== '' ? ' (' . $code . ')' : '');
        $lines = [
            'Thank you for your order.',
            'Order: ' . $code,
            'Status: ' . (string) ($order['status'] ?? 'pending'),
            'Payment method: ' . (string) ($order['payment_method'] ?? ''),
            'Total: ₱' . number_format((int) ($order['total_php'] ?? 0)),
        ];
        $receipt = trim((string) ($order['receipt_ref'] ?? ''));
        if ($receipt !== '') {
            $lines[] = 'Reference: ' . $receipt;
        }
        $lines[] = '';
        $lines[] = 'We will confirm once payment is recorded and keep you updated on fulfillment.';

        self::deliver($order, $subject, implode("\n", $lines));
    }

    public static function statusChanged(array $order, string $previousStatus, string $newStatus): void
    {
        $code = (string) ($order['public_code'] ?? '');
        $subject = 'KDesigns order update' . ($code !== '' ? ' (' . $code . ')' : '');
        $body = implode("\n", [
            'Your order status has changed.',
            'Order: ' . $code,
            'Previous: ' . $previousStatus,
            'Current: ' . $newStatus,
        ]);

        self::deliver($order, $subject, $body);
    }

    /** @param array<string,mixed> $order */
    private static function deliver(array $order, string $subject, string $body): void
    {
        $userId = (int) ($order['user_id'] ?? 0);
        $user = $userId > 0 ? Auth::findById($userId) : null;
        $to = trim((string) ($user['email'] ?? ''));
        if ($to === '' || filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            self::writeLog('(none)', $subject, 'No recipient for order user_id=' . $userId);
            return;
        }

        $driver = function_exists('kd_env') ? kd_env('MAIL_DRIVER', 'log') : 'log';
        if ($driver !== 'php') {
            self::writeLog($to, $subject, $body);
            return;
        }

        $from = function_exists('kd_env') ? kd_env('MAIL_FROM', 'noreply@kdesigns.ph') : 'noreply@kdesigns.ph';
        $from = preg_replace('/[\r\n]+/', '', $from) ?? '';
        if (filter_var($from, FILTER_VALIDATE_EMAIL) === false) {
            $from = 'noreply@kdesigns.ph';
        }
        $headers = 'From: ' . $from . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
        @mail($to, $subject, $body, $headers);
    }

    private static function writeLog(string $to, string $subject, string $body): void
    {
        $root = defined('KD_ROOT') ? KD_ROOT : dirname(__DIR__);
        $dir = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $line = sprintf(
            "[%s] to=%s subject=%s\n%s\n---\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $body
        );

        file_put_contents($dir . DIRECTORY_SEPARATOR . 'mail.log', $line, FILE_APPEND | LOCK_EX);
    }
}
