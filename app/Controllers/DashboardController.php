<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Report;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireUser();

        match ($user['role']) {
            'admin'   => $this->adminDashboard(),
            'kasir'   => $this->cashierDashboard($user),
            'barista' => $this->baristaDashboard(),
            default   => $this->redirect('login'),
        };
    }

    private function adminDashboard(): void
    {
        $report = new Report();
        $orders = new Order();
        $menus  = new Menu();
        $users  = new User();
        $from   = date('Y-m-01');
        $to     = date('Y-m-d');

        $summary = $report->summary($from, $to);

        $this->view('dashboard/admin', [
            'title'            => 'Dashboard Admin',
            'revenueToday'     => $report->revenueToday(),
            'transactionsToday'=> $report->transactionsToday(),
            'monthRevenue'     => $summary['revenue'],
            'monthTransactions'=> $summary['transactions'],
            'totalTransactions'=> $orders->countPaidAll(),
            'totalMenus'       => $menus->countAll(),
            'availableMenus'   => $menus->countAvailable(),
            'topMenus'         => $report->topMenus($from, $to, 5),
            'recentOrders'     => $orders->history(['payment_status' => 'lunas'], 8, 0),
            'statusCounts'     => [
                'dibayar'      => $orders->countByStatus('dibayar'),
                'diproses'     => $orders->countByStatus('diproses'),
                'siap_diambil' => $orders->countByStatus('siap_diambil'),
            ],
            'paymentSummary'   => (new Payment())->summaryByMethod($from, $to),
        ]);
    }

    /** @param array<string,mixed> $user */
    private function cashierDashboard(array $user): void
    {
        $orders = new Order();
        $report = new Report();

        $this->view('dashboard/cashier', [
            'title'         => 'Dashboard Kasir',
            'recentOrders'  => $orders->recentByCashier((int) $user['id'], 8),
            'todayRevenue'  => $report->revenueToday(),
            'todayTrx'      => $report->transactionsToday(),
            'openOrders'    => $orders->countByStatus('dibayar')
                + $orders->countByStatus('diproses')
                + $orders->countByStatus('siap_diambil'),
        ]);
    }

    private function baristaDashboard(): void
    {
        $orders = new Order();
        $queue  = $orders->baristaQueue();

        $this->view('dashboard/barista', [
            'title'          => 'Dashboard Barista',
            'queue'          => array_slice($queue, 0, 10),
            'queueCount'     => count($queue),
            'processingCount'=> $orders->countByStatus('diproses'),
            'readyCount'     => $orders->countByStatus('siap_diambil'),
        ]);
    }
}
