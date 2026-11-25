<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\ValidationException;
use App\Services\OrderService;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function checkout(): void
    {
        $this->requireUser();
        $cart = new OrderService();
        $summary = $cart->summary();

        if ($summary['items'] === [] || $summary['count'] === 0) {
            flash_error('Keranjang masih kosong. Pilih menu terlebih dahulu.');
            $this->redirect('pos');
        }

        // Token idempotensi untuk mencegah transaksi ganda akibat klik berkali-kali.
        $token = bin2hex(random_bytes(16));
        Session::set('checkout_token', $token);

        $this->view('pos/checkout', [
            'title'          => 'Pembayaran',
            'cart'           => $summary,
            'paymentMethods' => app_config('payment_methods', []),
            'checkoutToken'  => $token,
            'error'          => get_flash('error'),
            'success'        => get_flash('success'),
        ]);
    }

    public function process(): void
    {
        $user = $this->requireUser();

        $token    = (string) $this->input('checkout_token', '');
        $expected = (string) Session::get('checkout_token', '');

        // Cegah pemrosesan ulang (double submit / refresh).
        if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
            flash_error('Pembayaran ini sudah diproses atau token kedaluwarsa. Silakan buat transaksi baru.');
            $this->redirect('pos');
        }

        $cart = new OrderService();
        try {
            $order = (new PaymentService($cart))->process([
                'payment_method'    => $this->input('payment_method', ''),
                'amount_paid'       => $this->floatInput('amount_paid', 0),
                'payment_reference' => $this->input('payment_reference', ''),
                'discount'          => $this->floatInput('discount', 0),
                'customer_name'     => $this->input('customer_name', ''),
            ], $user);
        } catch (ValidationException $e) {
            flash_error($e->getMessage());
            flash('_old', ['customer_name' => (string) $this->input('customer_name', '')]);
            $this->redirect('checkout');
        } catch (\Throwable $e) {
            flash_error('Terjadi kesalahan saat menyimpan transaksi. Silakan coba lagi.');
            flash('_old', ['customer_name' => (string) $this->input('customer_name', '')]);
            $this->redirect('checkout');
        }

        // Sukses -> token dihapus agar tidak bisa dipakai ulang.
        Session::forget('checkout_token');
        flash_success('Pembayaran berhasil. Transaksi ' . $order['order_number'] . ' tersimpan.');

        $this->redirect('receipt/' . $order['id']);
    }
}
