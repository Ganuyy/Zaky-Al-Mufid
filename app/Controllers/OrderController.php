<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ValidationException;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;

class OrderController extends Controller
{
    private OrderService $cart;

    public function __construct()
    {
        $this->cart = new OrderService();
    }

    /**
     * Halaman utama Kasir (kartu menu + keranjang).
     */
    public function pos(): void
    {
        $search     = (string) $this->input('q', '');
        $categoryId = $this->intInput('category_id', 0);

        $menus = (new Menu())->availableForSale($search, $categoryId);

        $this->view('pos/index', [
            'title'      => 'Kasir / POS',
            'menus'      => $menus,
            'categories' => (new Category())->allOrdered(),
            'search'     => $search,
            'categoryId' => $categoryId,
            'cart'       => $this->cart->summary(),
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
        ]);
    }

    public function cart(): void
    {
        $this->json($this->cartPayload('Keranjang dimuat.'));
    }

    public function addToCart(): void
    {
        try {
            $menuId = $this->intInput('menu_id', 0);
            $qty    = $this->intInput('quantity', 1);
            $notes  = (string) $this->input('notes', '');

            $result = $this->cart->add($menuId, $qty, $notes);
            $this->json([
                'ok'      => $result['ok'],
                'message' => $result['message'],
            ] + $this->cartPayload($result['message']), $result['ok'] ? 200 : 422);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'Gagal menambah item.'] + $this->cartPayload(), 500);
        }
    }

    public function updateCart(): void
    {
        try {
            $lineKey = trim((string) $this->input('line_key', ''));
            if ($lineKey === '') {
                $lineKey = (string) $this->intInput('menu_id', 0);
            }
            $qty   = $this->intInput('quantity', 1);
            $notes = $this->input('notes', null);

            $result = $this->cart->update($lineKey, $qty, $notes === null ? null : (string) $notes);
            $this->json([
                'ok'      => $result['ok'],
                'message' => $result['message'],
            ] + $this->cartPayload($result['message']), $result['ok'] ? 200 : 422);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'Gagal memperbarui item.'] + $this->cartPayload(), 500);
        }
    }

    public function removeFromCart(): void
    {
        try {
            $lineKey = trim((string) $this->input('line_key', ''));
            if ($lineKey === '') {
                $lineKey = (string) $this->intInput('menu_id', 0);
            }
            $result = $this->cart->remove($lineKey);
            $this->json([
                'ok'      => $result['ok'],
                'message' => $result['message'],
            ] + $this->cartPayload($result['message']));
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'Gagal menghapus item.'] + $this->cartPayload(), 500);
        }
    }

    public function clearCart(): void
    {
        $this->cart->clear();
        $this->json(['ok' => true, 'message' => 'Keranjang dikosongkan.'] + $this->cartPayload('Keranjang dikosongkan.'));
    }

    /**
     * Halaman riwayat transaksi. Kasir hanya melihat transaksinya sendiri.
     */
    public function history(): void
    {
        $user   = $this->requireUser();
        $orders = new Order();

        $page    = max(1, $this->intInput('page', 1));
        $perPage = 15;

        $filters = [
            'search'         => (string) $this->input('q', ''),
            'order_status'   => (string) $this->input('order_status', ''),
            'payment_status' => (string) $this->input('payment_status', ''),
            'payment_method' => (string) $this->input('payment_method', ''),
            'date_from'      => (string) $this->input('date_from', ''),
            'date_to'        => (string) $this->input('date_to', ''),
        ];

        if ($user['role'] === 'kasir') {
            $filters['cashier_id'] = (int) $user['id'];
        } elseif ($this->intInput('cashier_id', 0) > 0) {
            $filters['cashier_id'] = $this->intInput('cashier_id', 0);
        }

        $total  = $orders->historyCount($filters);
        $offset = ($page - 1) * $perPage;
        $rows   = $orders->history($filters, $perPage, $offset);

        $this->view('pos/history', [
            'title'      => 'Riwayat Transaksi',
            'orders'     => $rows,
            'filters'    => $filters,
            'page'       => $page,
            'perPage'    => $perPage,
            'total'      => $total,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'cashiers'   => $user['role'] === 'admin' ? (new User())->allWithFilter('', 'kasir') : [],
            'isAdmin'    => $user['role'] === 'admin',
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
        ]);
    }

    /**
     * Detail transaksi.
     */
    public function show(array $params): void
    {
        $user  = $this->requireUser();
        $order = (new Order())->findDetail((int) $params['id']);

        if ($order === null) {
            flash_error('Transaksi tidak ditemukan.');
            $this->redirect('orders/history');
        }

        // Kasir hanya boleh melihat transaksinya sendiri.
        if ($user['role'] === 'kasir' && (int) $order['cashier_id'] !== (int) $user['id']) {
            flash_error('Anda tidak berhak melihat transaksi ini.');
            $this->redirect('orders/history');
        }

        $orders = new Order();

        $this->view('pos/show', [
            'title'    => 'Detail Transaksi ' . $order['order_number'],
            'order'    => $order,
            'items'    => $orders->items((int) $order['id']),
            'logs'     => $orders->logs((int) $order['id']),
            'canPrint' => true,
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    private function cartPayload(string $message = ''): array
    {
        $summary = $this->cart->summary();

        return [
            'message'    => $message,
            'count'      => $summary['count'],
            'subtotal'   => $summary['subtotal'],
            'total'      => $summary['total'],
            'subtotal_fmt' => rupiah($summary['subtotal']),
            'total_fmt'  => rupiah($summary['total']),
            'valid'      => $summary['valid'],
            'problems'   => $summary['problems'],
            'items'      => array_map(fn ($i) => [
                'key'        => $i['key'],
                'menu_id'    => $i['menu_id'],
                'menu_name'  => $i['menu_name'],
                'unit_price' => $i['unit_price'],
                'price_fmt'  => rupiah($i['unit_price']),
                'quantity'   => $i['quantity'],
                'subtotal'   => $i['subtotal'],
                'subtotal_fmt' => rupiah($i['subtotal']),
                'notes'      => $i['notes'],
                'is_available' => $i['is_available'],
            ], $summary['items']),
        ];
    }
}
