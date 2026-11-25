<?php
/**
 * Konfigurasi aplikasi.
 */

declare(strict_types=1);

use App\Core\Env;

return [
    'name'     => (string) Env::get('APP_NAME', 'SipOrder'),
    'short'    => (string) Env::get('APP_SHORT', 'SO'),
    'debug'    => filter_var(Env::get('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    'timezone' => (string) Env::get('APP_TIMEZONE', 'Asia/Jakarta'),
    'address'  => (string) Env::get('APP_ADDRESS', 'Jl. Bumi Lestari, Kabupaten Bekasi'),
    'phone'    => (string) Env::get('APP_PHONE', '0899-0091-090'),
    'receipt_width' => (int) Env::get('RECEIPT_WIDTH', 58),

    // Metode pembayaran yang tersedia.
    // requires_reference: metode non-tunai butuh konfirmasi/referensi dari kasir.
    'payment_methods' => [
        'tunai'   => ['label' => 'Tunai',        'requires_reference' => false],
        'qris'    => ['label' => 'QRIS',         'requires_reference' => true],
        'debit'   => ['label' => 'Kartu Debit',  'requires_reference' => true],
        'ewallet' => ['label' => 'E-Wallet',     'requires_reference' => true],
    ],

    // Status pesanan (pengerjaan minuman) - terpisah dari status pembayaran.
    'order_statuses' => [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'dibayar'             => 'Dibayar',
        'diproses'            => 'Diproses',
        'siap_diambil'        => 'Siap Diambil',
        'selesai'             => 'Selesai',
        'dibatalkan'          => 'Dibatalkan',
    ],

    // Status pembayaran.
    'payment_statuses' => [
        'belum_dibayar' => 'Belum Dibayar',
        'lunas'         => 'LUNAS',
        'gagal'         => 'Gagal',
        'dikembalikan'  => 'Dikembalikan',
    ],

    // Alur transisi status pesanan yang sah (dikelola Barista).
    'order_status_flow' => [
        'dibayar'      => ['diproses', 'dibatalkan'],
        'diproses'     => ['siap_diambil', 'dibatalkan'],
        'siap_diambil' => ['selesai'],
        'selesai'      => [],
        'dibatalkan'   => [],
        'menunggu_pembayaran' => ['dibayar', 'dibatalkan'],
    ],

    // Role yang boleh mengubah status pengerjaan pesanan.
    'order_status_roles' => ['admin', 'barista'],
];
