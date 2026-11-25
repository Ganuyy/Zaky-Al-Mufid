<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected string $table = 'payments';

    /** @return array<string,mixed>|null */
    public function byOrder(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM payments WHERE order_id = :id ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['id' => $orderId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int,array<string,mixed>> */
    public function summaryByMethod(string $dateFrom = '', string $dateTo = ''): array
    {
        $sql = "SELECT payment_method,
                       COUNT(*) AS trx_count,
                       SUM(amount) AS total_amount
                FROM payments
                WHERE status = 'lunas'";
        $params = [];
        if ($dateFrom !== '') {
            $sql .= ' AND paid_at >= :from';
            $params['from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $sql .= ' AND paid_at <= :to';
            $params['to'] = $dateTo . ' 23:59:59';
        }
        $sql .= ' GROUP BY payment_method ORDER BY total_amount DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
