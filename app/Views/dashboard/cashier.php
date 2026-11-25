<?php
/**
 * @var array $recentOrders
 * @var float $todayRevenue
 * @var int $todayTrx
 * @var int $openOrders
 */
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kp-stat kp-stat-brown">
            <div class="kp-stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="kp-stat-label">Penjualan Hari Ini</div>
                <div class="kp-stat-value"><?= rupiah($todayRevenue) ?></div>
                <div class="kp-stat-sub"><?= (int) $todayTrx ?> transaksi lunas</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kp-stat kp-stat-green">
            <div class="kp-stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="kp-stat-label">Pesanan Aktif</div>
                <div class="kp-stat-value"><?= (int) $openOrders ?></div>
                <div class="kp-stat-sub">sedang diproses barista</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kp-stat kp-stat-cream">
            <div class="kp-stat-icon"><i class="bi bi-lightning-charge"></i></div>
            <div>
                <div class="kp-stat-label">Mulai Melayani</div>
                <div class="kp-stat-value">POS</div>
                <a href="<?= base_url('pos') ?>" class="kp-stat-sub text-decoration-none fw-semibold">Buka Kasir &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="card kp-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-clock-history text-kp-brown"></i> Transaksi Terakhir Saya</span>
        <a href="<?= base_url('orders/history') ?>" class="btn btn-sm btn-kp-outline">Semua Riwayat</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentOrders)): ?>
            <div class="kp-empty py-5"><i class="bi bi-inbox"></i><p>Belum ada transaksi. Mulai dari halaman Kasir.</p>
                <a href="<?= base_url('pos') ?>" class="btn btn-kp-primary mt-2"><i class="bi bi-cart-plus"></i> Buka POS</a></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>No. Transaksi</th><th>Status</th><th>Pembayaran</th><th class="text-end">Total</th><th>Waktu</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recentOrders as $o): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($o['order_number']) ?></div>
                                <?php if (!empty($o['customer_name'])): ?>
                                    <div class="small text-muted"><i class="bi bi-person"></i> <?= e($o['customer_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= order_status_badge($o['order_status']) ?>"><?= e(order_status_label($o['order_status'])) ?></span></td>
                            <td><span class="badge <?= payment_status_badge($o['payment_status']) ?>"><?= e(payment_status_label($o['payment_status'])) ?></span></td>
                            <td class="text-end fw-semibold"><?= rupiah($o['total']) ?></td>
                            <td class="text-muted small"><?= e(indonesian_date($o['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="<?= base_url('receipt/' . $o['id']) ?>" class="btn btn-sm btn-kp-outline" title="Cetak Struk">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
