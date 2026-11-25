<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\ValidationException;
use App\Models\Order;
use App\Models\Payment;

/**
 * Menyusun data struk dari transaksi yang benar-benar tersimpan di database.
 * Cetak ulang struk TIDAK membuat transaksi baru.
 */
class ReceiptService
{
    private Order $orders;

    private Payment $payments;

    public function __construct()
    {
        $this->orders   = new Order();
        $this->payments = new Payment();
    }

    /**
     * @return array<string,mixed>
     */
    public function build(int $orderId): array
    {
        $order = $this->orders->findDetail($orderId);
        if ($order === null) {
            throw new ValidationException('Transaksi tidak ditemukan.');
        }

        $items   = $this->orders->items($orderId);
        $payment = $this->payments->byOrder($orderId);

        return [
            'store' => [
                'name'    => app_config('name', 'SipOrder'),
                'short'   => app_config('short', 'SO'),
                'address' => app_config('address', ''),
                'phone'   => app_config('phone', ''),
            ],
            'order'   => $order,
            'items'   => $items,
            'payment' => $payment,
            'printed_at' => date('Y-m-d H:i:s'),
        ];
    }
}
