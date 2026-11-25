<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected string $table = 'orders';

    /** @return array<string,mixed>|null */
    public function findDetail(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, u.name AS cashier_name, u.username AS cashier_username
             FROM orders o
             JOIN users u ON u.id = o.cashier_id
             WHERE o.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int,array<string,mixed>> */
    public function items(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM order_items WHERE order_id = :id ORDER BY id ASC'
        );
        $stmt->execute(['id' => $orderId]);

        return $stmt->fetchAll();
    }

    /**
     * Bangun nomor transaksi unik: SO-YYYYMMDD-0001
     */
    public function generateOrderNumber(?string $date = null): string
    {
        $date = $date ?? date('Ymd');
        $prefix = 'SO-' . $date . '-';

        $stmt = $this->db->prepare(
            'SELECT order_number FROM orders WHERE order_number LIKE :prefix
             ORDER BY order_number DESC LIMIT 1'
        );
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $next = 1;
        if (is_string($last) && $last !== '') {
            $suffix = (int) substr($last, strlen($prefix));
            $next = $suffix + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Antrean barista: pesanan yang SUDAH DIBAYAR dan belum selesai/dibatalkan.
     * @return array<int,array<string,mixed>>
     */
    public function baristaQueue(): array
    {
        $stmt = $this->db->query(
            "SELECT o.*, u.name AS cashier_name
             FROM orders o
             JOIN users u ON u.id = o.cashier_id
             WHERE o.payment_status = 'lunas'
               AND o.order_status IN ('dibayar','diproses','siap_diambil')
             ORDER BY FIELD(o.order_status, 'siap_diambil', 'diproses', 'dibayar'), o.paid_at ASC"
        );

        return $stmt->fetchAll();
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<int,array<string,mixed>>
     */
    public function history(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildHistoryWhere($filters);

        $sql = "SELECT o.*, u.name AS cashier_name
                FROM orders o
                JOIN users u ON u.id = o.cashier_id
                {$where}
                ORDER BY o.created_at DESC, o.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @param array<string,mixed> $filters */
    public function historyCount(array $filters): int
    {
        [$where, $params] = $this->buildHistoryWhere($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders o {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string,mixed> $filters
     * @return array{0:string,1:array<string,mixed>}
     */
    private function buildHistoryWhere(array $filters): array
    {
        $clauses = ['1=1'];
        $params = [];

        if (!empty($filters['cashier_id'])) {
            $clauses[] = 'o.cashier_id = :cashier_id';
            $params['cashier_id'] = (int) $filters['cashier_id'];
        }
        if (!empty($filters['order_status'])) {
            $clauses[] = 'o.order_status = :order_status';
            $params['order_status'] = $filters['order_status'];
        }
        if (!empty($filters['payment_status'])) {
            $clauses[] = 'o.payment_status = :payment_status';
            $params['payment_status'] = $filters['payment_status'];
        }
        if (!empty($filters['payment_method'])) {
            $clauses[] = 'o.payment_method = :payment_method';
            $params['payment_method'] = $filters['payment_method'];
        }
        if (!empty($filters['date_from'])) {
            $clauses[] = 'o.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $clauses[] = 'o.created_at <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $clauses[] = 'o.order_number LIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        return ['WHERE ' . implode(' AND ', $clauses), $params];
    }

    /** @return array<int,array<string,mixed>> */
    public function logs(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.name AS user_name
             FROM order_status_logs l
             LEFT JOIN users u ON u.id = l.user_id
             WHERE l.order_id = :id
             ORDER BY l.id ASC'
        );
        $stmt->execute(['id' => $orderId]);

        return $stmt->fetchAll();
    }

    // ---- Statistik dashboard --------------------------------------------

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM orders WHERE order_status = :s');
        $stmt->execute(['s' => $status]);

        return (int) $stmt->fetchColumn();
    }

    public function countPaidToday(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM orders
             WHERE payment_status = 'lunas' AND DATE(paid_at) = CURDATE()"
        );

        return (int) $stmt->fetchColumn();
    }

    public function countPaidAll(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM orders WHERE payment_status = 'lunas'"
        )->fetchColumn();
    }

    /** Pesanan aktif untuk dashboard kasir hari ini. */
    /** @return array<int,array<string,mixed>> */
    public function recentByCashier(int $cashierId, int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.* FROM orders o
             WHERE o.cashier_id = :id
             ORDER BY o.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute(['id' => $cashierId]);

        return $stmt->fetchAll();
    }
}
