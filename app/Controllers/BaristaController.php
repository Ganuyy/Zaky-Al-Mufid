<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ValidationException;
use App\Models\Order;
use App\Services\OrderStatusService;

class BaristaController extends Controller
{
    public function queue(): void
    {
        $this->requireUser();
        $orders = new Order();

        $completedStatus = (string) $this->input('status', 'selesai');
        $allowed = ['selesai', 'dibatalkan'];
        if (!in_array($completedStatus, $allowed, true)) {
            $completedStatus = 'selesai';
        }

        $historyFilters = [
            'order_status'   => $completedStatus,
            'payment_status' => 'lunas',
        ];

        $queue = $orders->baristaQueue();
        foreach ($queue as &$orderRow) {
            $orderRow['items'] = $orders->items((int) $orderRow['id']);
        }
        unset($orderRow);

        $this->view('barista/queue', [
            'title'      => 'Antrean Pesanan',
            'queue'      => $queue,
            'history'    => $orders->history($historyFilters, 20, 0),
            'status'     => $completedStatus,
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
        ]);
    }

    public function show(array $params): void
    {
        $this->requireUser();
        $id    = (int) $params['id'];
        $order = (new Order())->findDetail($id);

        if ($order === null) {
            flash_error('Pesanan tidak ditemukan.');
            $this->redirect('barista/queue');
        }

        $orders = new Order();
        $this->view('barista/show', [
            'title' => 'Detail Pesanan ' . $order['order_number'],
            'order' => $order,
            'items' => $orders->items($id),
            'logs'  => $orders->logs($id),
        ]);
    }

    public function updateStatus(array $params): void
    {
        $user = $this->requireUser();
        $id   = (int) $params['id'];
        $new  = (string) $this->input('status', '');
        $notes = (string) $this->input('notes', '');

        try {
            $service = new OrderStatusService();
            $updated = $service->transition($id, $new, $user, $notes);
            flash_success('Pesanan ' . $updated['order_number'] . ' kini berstatus ' . order_status_label($new) . '.');
        } catch (ValidationException $e) {
            flash_error($e->getMessage());
        } catch (\Throwable $e) {
            flash_error('Gagal mengubah status pesanan.');
        }

        $this->back('barista/queue');
    }
}
