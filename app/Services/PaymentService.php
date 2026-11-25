<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ValidationException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use PDO;
use PDOException;

/**
 * Memproses pembayaran pesanan.
 *
 * Prinsip:
 *  - Total dihitung ulang di server (tidak mempercayai angka dari browser).
 *  - Harga di-snapshot ke order_items.
 *  - Semua penyimpanan dibungkus database transaction (rollback bila gagal).
 *  - Nomor transaksi unik; jika bentrok akan dicoba ulang.
 */
class PaymentService
{
    /** Cek & lengkapi struktur database baru sekali per request. */
    private static bool $schemaReady = false;

    private OrderService $orderService;

    private Order $orders;

    private OrderItem $orderItems;

    private Payment $payments;

    private OrderStatusLog $logs;

    public function __construct(?OrderService $orderService = null)
    {
        $this->orderService = $orderService ?? new OrderService();
        $this->orders       = new Order();
        $this->orderItems   = new OrderItem();
        $this->payments     = new Payment();
        $this->logs         = new OrderStatusLog();
    }

    /**
     * @param array<string,mixed> $input
     * @param array<string,mixed> $user
     * @return array<string,mixed> detail order yang tersimpan
     */
    public function process(array $input, array $user): array
    {
        $summary = $this->orderService->summary();

        if ($summary['items'] === [] || $summary['count'] === 0) {
            throw new ValidationException('Keranjang masih kosong. Tambahkan menu terlebih dahulu.');
        }
        if ($summary['problems'] !== []) {
            throw new ValidationException(implode(' ', $summary['problems']));
        }

        $methods = app_config('payment_methods', []);
        $method  = (string) ($input['payment_method'] ?? '');
        if (!isset($methods[$method])) {
            throw new ValidationException('Metode pembayaran tidak valid.');
        }
        $requiresReference = (bool) ($methods[$method]['requires_reference'] ?? false);

        $customerName = trim((string) ($input['customer_name'] ?? ''));
        $customerName = $customerName !== '' ? mb_substr($customerName, 0, 100) : null;

        $subtotal = round($summary['subtotal'], 2);
        $discount = round((float) ($input['discount'] ?? 0), 2);
        if ($discount < 0) {
            throw new ValidationException('Diskon tidak boleh bernilai negatif.');
        }
        if ($discount > $subtotal) {
            throw new ValidationException('Diskon tidak boleh melebihi subtotal.');
        }
        $total = round($subtotal - $discount, 2);

        $amountPaid = 0.0;
        $change     = 0.0;
        $reference  = null;

        if ($method === 'tunai') {
            $amountPaid = round((float) ($input['amount_paid'] ?? 0), 2);
            if ($amountPaid <= 0) {
                throw new ValidationException('Masukkan nominal uang tunai yang dibayarkan pelanggan.');
            }
            if ($amountPaid < $total) {
                throw new ValidationException(
                    'Uang dibayar kurang. Total ' . rupiah($total) . ', dibayar ' . rupiah($amountPaid) . '.'
                );
            }
            $change = round($amountPaid - $total, 2);
        } else {
            // Metode non-tunai: wajib konfirmasi/referensi, bukan asal pilih.
            if ($requiresReference) {
                $reference = trim((string) ($input['payment_reference'] ?? ''));
                if ($reference === '') {
                    throw new ValidationException(
                        'Masukkan nomor referensi / bukti konfirmasi pembayaran ' . $methods[$method]['label'] . '.'
                    );
                }
                $reference = mb_substr($reference, 0, 64);
            }
            $amountPaid = $total;
            $change     = 0.0;
        }

        $now = date('Y-m-d H:i:s');
        $pdo = Database::connection();
        $this->ensureSchema($pdo);
        $orderId = 0;
        $attempt = 0;

        while (true) {
            $attempt++;
            $orderNumber = $this->orders->generateOrderNumber(date('Ymd'));

            try {
                $pdo->beginTransaction();

                $orderId = $this->orders->create([
                    'order_number'   => $orderNumber,
                    'cashier_id'     => (int) $user['id'],
                    'customer_name'  => $customerName,
                    'order_status'   => 'dibayar',
                    'payment_status' => 'lunas',
                    'payment_method' => $method,
                    'subtotal'       => $subtotal,
                    'discount'       => $discount,
                    'total'          => $total,
                    'amount_paid'    => $amountPaid,
                    'change_amount'  => $change,
                    'created_at'     => $now,
                    'paid_at'        => $now,
                ]);

                foreach ($summary['items'] as $item) {
                    $this->orderItems->create([
                        'order_id'   => $orderId,
                        'menu_id'    => $item['menu_id'],
                        'menu_name'  => $item['menu_name'],
                        'unit_price' => $item['unit_price'],
                        'quantity'   => $item['quantity'],
                        'subtotal'   => $item['subtotal'],
                        'notes'      => $item['notes'] !== '' ? $item['notes'] : null,
                    ]);
                }

                $this->payments->create([
                    'order_id'          => $orderId,
                    'payment_reference' => $reference ?? 'TRX-' . $orderNumber,
                    'payment_method'    => $method,
                    'amount'            => $amountPaid,
                    'status'            => 'lunas',
                    'paid_at'           => $now,
                ]);

                $this->logs->record(
                    $orderId,
                    (int) $user['id'],
                    'menunggu_pembayaran',
                    'dibayar',
                    'Pembayaran ' . $methods[$method]['label'] . ' terkonfirmasi.'
                );

                $pdo->commit();
                break;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                // 23000 = pelanggaran constraint (mis. nomor transaksi duplikat)
                $isDuplicate = (string) $e->getCode() === '23000';
                if ($isDuplicate && $attempt < 5) {
                    continue;
                }
                throw $e;
            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
        }

        $this->orderService->clear();

        $detail = $this->orders->findDetail($orderId);
        if ($detail === null) {
            throw new \RuntimeException('Transaksi tersimpan tetapi gagal dimuat.');
        }

        return $detail;
    }

    /**
     * Pastikan kolom fitur baru tersedia pada database lama tanpa menghapus data.
     * Jika user database tidak punya izin ALTER, berikan pesan migrasi yang jelas.
     */
    private function ensureSchema(PDO $pdo): void
    {
        if (self::$schemaReady) {
            return;
        }
        self::$schemaReady = true;

        try {
            $column = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'customer_name'")->fetch();
            if ($column === false) {
                $pdo->exec('ALTER TABLE `orders` ADD COLUMN `customer_name` VARCHAR(100) NULL AFTER `cashier_id`');
            }

            $notes = $pdo->query("SHOW COLUMNS FROM `order_items` LIKE 'notes'")->fetch();
            if ($notes !== false) {
                $type = (string) ($notes['Type'] ?? '');
                if (preg_match('/\((\d+)\)/', $type, $matches) === 1 && (int) $matches[1] < 255) {
                    $pdo->exec('ALTER TABLE `order_items` MODIFY COLUMN `notes` VARCHAR(255) NULL');
                }
            }
        } catch (\Throwable $e) {
            throw new ValidationException(
                'Struktur database belum diperbarui. Jalankan migrasi: php database/migrate.php'
            );
        }
    }
}
