<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ValidationException;
use App\Models\Order;
use App\Models\OrderStatusLog;

/**
 * Mengatur transisi status pesanan beserta audit log-nya.
 *
 * Aturan:
 *  - Hanya transisi yang terdefinisi di config('order_status_flow') yang sah.
 *  - Pesanan belum dibayar tidak boleh masuk proses pembuatan minuman.
 *  - Hanya role tertentu (admin/barista) yang boleh mengubah pengerjaan.
 */
class OrderStatusService
{
    private Order $orders;

    private OrderStatusLog $logs;

    public function __construct()
    {
        $this->orders = new Order();
        $this->logs   = new OrderStatusLog();
    }

    /**
     * @param array<string,mixed> $user
     * @return array<string,mixed>
     */
    public function transition(int $orderId, string $newStatus, array $user, ?string $notes = null): array
    {
        $order = $this->orders->find($orderId);
        if ($order === null) {
            throw new ValidationException('Pesanan tidak ditemukan.');
        }

        $role = $user['role'] ?? '';
        if (!in_array($role, app_config('order_status_roles', []), true)) {
            throw new ValidationException('Anda tidak berwenang mengubah status pesanan.');
        }

        $current = (string) $order['order_status'];
        if ($current === $newStatus) {
            throw new ValidationException('Status pesanan sudah ' . order_status_label($newStatus) . '.');
        }

        // Pesanan yang belum dibayar hanya boleh menjadi 'dibayar' (oleh sistem)
        // atau 'dibatalkan'. Tidak boleh langsung masuk proses.
        if ($current === 'menunggu_pembayaran' && !in_array($newStatus, ['dibayar', 'dibatalkan'], true)) {
            throw new ValidationException('Pesanan belum dibayar tidak dapat diproses.');
        }

        // Barista tidak boleh memproses pesanan yang belum lunas.
        if ($newStatus === 'diproses' && $order['payment_status'] !== 'lunas') {
            throw new ValidationException('Pesanan belum lunas, tidak dapat diproses.');
        }

        $flow = app_config('order_status_flow', []);
        $allowed = $flow[$current] ?? [];
        if (!in_array($newStatus, $allowed, true)) {
            throw new ValidationException(
                'Transisi status tidak sah: dari "' . order_status_label($current)
                . '" ke "' . order_status_label($newStatus) . '".'
            );
        }

        $notes = $notes !== null && trim($notes) !== ''
            ? mb_substr(trim($notes), 0, 255)
            : 'Status diubah ke ' . order_status_label($newStatus);

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $this->orders->update($orderId, ['order_status' => $newStatus]);
            $this->logs->record($orderId, (int) ($user['id'] ?? 0) ?: null, $current, $newStatus, $notes);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        $updated = $this->orders->findDetail($orderId);
        if ($updated === null) {
            throw new \RuntimeException('Gagal memuat pesanan setelah perubahan status.');
        }

        return $updated;
    }
}
