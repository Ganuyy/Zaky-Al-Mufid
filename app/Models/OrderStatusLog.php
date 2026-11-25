<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class OrderStatusLog extends Model
{
    protected string $table = 'order_status_logs';

    /**
     * Catat perubahan status pesanan (audit trail).
     */
    public function record(int $orderId, ?int $userId, ?string $oldStatus, string $newStatus, ?string $notes = null): int
    {
        return $this->create([
            'order_id'   => $orderId,
            'user_id'    => $userId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes'      => $notes,
        ]);
    }
}
