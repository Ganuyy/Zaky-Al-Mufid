<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class OrderItem extends Model
{
    protected string $table = 'order_items';

    /** @return array<int,array<string,mixed>> */
    public function byOrder(int $orderId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = :id ORDER BY id ASC');
        $stmt->execute(['id' => $orderId]);

        return $stmt->fetchAll();
    }
}
