<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Orders.php';

final class Checkout
{
    public const TIMES = [
        '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM', '12:00 PM', '12:30 PM',
        '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM', '3:00 PM', '3:30 PM',
        '4:00 PM', '4:30 PM', '5:00 PM', '5:30 PM', '6:00 PM',
    ];

    /**
     * Place the in-progress checkout wizard (session) as a real order.
     *
     * @return array<string, mixed>
     */
    public static function placeFromSession(int $productId, string $paymentMethod): array
    {
        $userId = Auth::id();
        if ($userId === null) {
            throw new RuntimeException('Please log in to place an order.');
        }
        if (!in_array($paymentMethod, Orders::PAYMENTS, true)) {
            throw new InvalidArgumentException('Invalid payment method.');
        }

        $fulfillment = (string) ($_SESSION['order_fulfillment'] ?? '');
        if (!in_array($fulfillment, Orders::FULFILLMENTS, true)) {
            throw new InvalidArgumentException('Please complete the order form first.');
        }

        $name = trim((string) ($_SESSION['order_name'] ?? ''));
        $contact = trim((string) ($_SESSION['order_contact'] ?? ''));
        $dateNeeded = trim((string) ($_SESSION['order_date'] ?? ''));
        if ($name === '' || $contact === '' || $dateNeeded === '') {
            throw new InvalidArgumentException('Please complete the order form first.');
        }

        return Orders::create([
            'user_id'           => $userId,
            'product_id'        => $productId,
            'qty'               => 1,
            'fulfillment'       => $fulfillment,
            'payment_method'    => $paymentMethod,
            'customer_name'     => $name,
            'customer_contact'  => $contact,
            'date_needed'       => $dateNeeded,
            'time_needed'       => $_SESSION['order_time'] ?? null,
            'delivery_receiver' => $_SESSION['delivery_receiver'] ?? null,
            'delivery_contact'  => $_SESSION['delivery_contact'] ?? null,
            'delivery_location' => $_SESSION['delivery_location'] ?? null,
        ]);
    }

    public static function clearWizard(): void
    {
        foreach ([
            'order_step', 'order_name', 'order_contact', 'order_date', 'order_time',
            'order_fulfillment', 'payment_method', 'receipt_ref', 'delivery_receiver',
            'delivery_contact', 'delivery_location', 'fulfillment_method',
        ] as $key) {
            unset($_SESSION[$key]);
        }
    }
}
