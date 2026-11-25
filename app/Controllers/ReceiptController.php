<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ValidationException;
use App\Models\Order;
use App\Services\ReceiptService;

class ReceiptController extends Controller
{
    public function show(array $params): void
    {
        $user = $this->requireUser();
        $id   = (int) $params['id'];

        $order = (new Order())->findDetail($id);
        if ($order === null) {
            flash_error('Transaksi tidak ditemukan.');
            $this->redirect('orders/history');
        }

        if ($user['role'] === 'kasir' && (int) $order['cashier_id'] !== (int) $user['id']) {
            flash_error('Anda tidak berhak melihat struk transaksi ini.');
            $this->redirect('orders/history');
        }

        try {
            $receipt = (new ReceiptService())->build($id);
        } catch (ValidationException $e) {
            flash_error($e->getMessage());
            $this->redirect('orders/history');
        }

        $width = (int) $this->input('width', app_config('receipt_width', 58));
        if (!in_array($width, [58, 80], true)) {
            $width = 58;
        }

        $this->view('receipt/show', [
            'title'   => 'Struk ' . $order['order_number'],
            'receipt' => $receipt,
            'width'   => $width,
            'success' => get_flash('success'),
        ], 'layouts/blank');
    }
}
