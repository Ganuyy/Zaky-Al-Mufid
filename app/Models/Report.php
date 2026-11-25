<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Query khusus laporan penjualan.
 *
 * ATURAN PENDAPATAN:
 * Hanya pesanan dengan payment_status = 'lunas' yang dihitung sebagai pendapatan.
 * Pesanan belum dibayar, gagal, atau dikembalikan (refund) TIDAK dihitung.
 * Tanggal pendapatan mengikuti kolom paid_at.
 */
class Report extends Model
{
    protected string $table = 'orders';

    /**
     * @return array{revenue:float,transactions:int,items:int,average:float}
     */
    public function summary(string $from = '', string $to = ''): array
    {
        [$where, $params] = $this->periodClause($from, $to);

        $sql = "SELECT
                    COALESCE(SUM(o.total),0) AS revenue,
                    COUNT(*) AS transactions
                FROM orders o
                {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: ['revenue' => 0, 'transactions' => 0];

        $itemsSql = "SELECT COALESCE(SUM(oi.quantity),0)
                     FROM order_items oi
                     JOIN orders o ON o.id = oi.order_id
                     {$where}";
        $itemsStmt = $this->db->prepare($itemsSql);
        $itemsStmt->execute($params);
        $items = (int) $itemsStmt->fetchColumn();

        $transactions = (int) $row['transactions'];
        $revenue = (float) $row['revenue'];

        return [
            'revenue'      => $revenue,
            'transactions' => $transactions,
            'items'        => $items,
            'average'      => $transactions > 0 ? $revenue / $transactions : 0.0,
        ];
    }

    public function revenueToday(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(total),0) FROM orders
             WHERE payment_status = 'lunas' AND DATE(paid_at) = CURDATE()"
        );

        return (float) $stmt->fetchColumn();
    }

    public function transactionsToday(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM orders
             WHERE payment_status = 'lunas' AND DATE(paid_at) = CURDATE()"
        );

        return (int) $stmt->fetchColumn();
    }

    /**
     * Menu terlaris.
     * @return array<int,array<string,mixed>>
     */
    public function topMenus(string $from = '', string $to = '', int $limit = 10): array
    {
        [$where, $params] = $this->periodClause($from, $to);

        $limit = max(1, min($limit, 100));
        $sql = "SELECT oi.menu_name,
                       SUM(oi.quantity) AS qty,
                       SUM(oi.subtotal) AS revenue
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                {$where}
                GROUP BY oi.menu_name
                ORDER BY qty DESC, revenue DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Rekap penjualan harian.
     * @return array<int,array<string,mixed>>
     */
    public function dailyBreakdown(string $from, string $to): array
    {
        [$where, $params] = $this->periodClause($from, $to);

        $sql = "SELECT DATE(o.paid_at) AS sale_date,
                       COUNT(*) AS transactions,
                       COALESCE(SUM(o.total),0) AS revenue
                FROM orders o
                {$where}
                GROUP BY DATE(o.paid_at)
                ORDER BY sale_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * @return array{0:string,1:array<string,mixed>}
     */
    private function periodClause(string $from, string $to): array
    {
        $clauses = ["o.payment_status = 'lunas'"];
        $params = [];

        if ($from !== '') {
            $clauses[] = 'o.paid_at >= :from';
            $params['from'] = $from . ' 00:00:00';
        }
        if ($to !== '') {
            $clauses[] = 'o.paid_at <= :to';
            $params['to'] = $to . ' 23:59:59';
        }

        return ['WHERE ' . implode(' AND ', $clauses), $params];
    }
}
