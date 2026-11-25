<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Report;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->requireUser();

        $dateFrom = (string) $this->input('date_from', date('Y-m-01'));
        $dateTo   = (string) $this->input('date_to', date('Y-m-d'));
        if (!$this->isValidDate($dateFrom)) {
            $dateFrom = date('Y-m-01');
        }
        if (!$this->isValidDate($dateTo)) {
            $dateTo = date('Y-m-d');
        }
        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $report = new Report();
        $summary = $report->summary($dateFrom, $dateTo);

        $page    = max(1, $this->intInput('page', 1));
        $perPage = 15;
        $filters = [
            'payment_status' => 'lunas',
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
            'payment_method' => (string) $this->input('payment_method', ''),
            'search'         => (string) $this->input('q', ''),
        ];

        $orders = new Order();
        $total  = $orders->historyCount($filters);

        $this->view('reports/index', [
            'title'           => 'Laporan Penjualan',
            'dateFrom'        => $dateFrom,
            'dateTo'          => $dateTo,
            'summary'         => $summary,
            'revenueToday'    => $report->revenueToday(),
            'transactionsToday' => $report->transactionsToday(),
            'topMenus'        => $report->topMenus($dateFrom, $dateTo, 10),
            'daily'           => $report->dailyBreakdown($dateFrom, $dateTo),
            'paymentSummary'  => (new Payment())->summaryByMethod($dateFrom, $dateTo),
            'orders'          => $orders->history($filters, $perPage, ($page - 1) * $perPage),
            'filters'         => $filters,
            'page'            => $page,
            'totalPages'      => max(1, (int) ceil($total / $perPage)),
            'total'           => $total,
        ]);
    }

    public function export(): void
    {
        $this->requireUser();

        $dateFrom = (string) $this->input('date_from', date('Y-m-01'));
        $dateTo   = (string) $this->input('date_to', date('Y-m-d'));
        if (!$this->isValidDate($dateFrom)) {
            $dateFrom = date('Y-m-01');
        }
        if (!$this->isValidDate($dateTo)) {
            $dateTo = date('Y-m-d');
        }

        $orders = (new Order())->history([
            'payment_status' => 'lunas',
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
            'payment_method' => (string) $this->input('payment_method', ''),
            'search'         => (string) $this->input('q', ''),
        ], 100000, 0);

        $filename = 'laporan_penjualan_' . str_replace('-', '', $dateFrom) . '_' . str_replace('-', '', $dateTo) . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
        fputcsv($out, ['No. Transaksi', 'Tanggal', 'Kasir', 'Status Pesanan', 'Metode', 'Subtotal', 'Diskon', 'Total', 'Dibayar', 'Kembalian']);
        foreach ($orders as $o) {
            fputcsv($out, [
                $o['order_number'],
                $o['paid_at'] ?? $o['created_at'],
                $o['cashier_name'],
                order_status_label($o['order_status']),
                payment_method_label($o['payment_method']),
                number_format((float) $o['subtotal'], 0, ',', '.'),
                number_format((float) $o['discount'], 0, ',', '.'),
                number_format((float) $o['total'], 0, ',', '.'),
                number_format((float) $o['amount_paid'], 0, ',', '.'),
                number_format((float) $o['change_amount'], 0, ',', '.'),
            ]);
        }
        fclose($out);
        exit;
    }

    private function isValidDate(string $value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $value);

        return $d !== false && $d->format('Y-m-d') === $value;
    }
}
