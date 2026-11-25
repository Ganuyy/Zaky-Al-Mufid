<?php
/**
 * @var float $revenueToday
 * @var int $transactionsToday
 * @var float $monthRevenue
 * @var int $monthTransactions
 * @var int $totalTransactions
 * @var int $totalMenus
 * @var int $availableMenus
 * @var array $topMenus
 * @var array $recentOrders
 * @var array $statusCounts
 * @var array $paymentSummary
 */
$bulanIni = indonesian_month_year();
?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="kp-stat kp-stat-brown">
            <div class="kp-stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="kp-stat-label">Penjualan Hari Ini</div>
                <div class="kp-stat-value"><?= rupiah($revenueToday) ?></div>
                <div class="kp-stat-sub"><?= (int) $transactionsToday ?> transaksi</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kp-stat kp-stat-cream">
            <div class="kp-stat-icon"><i class="bi bi-calendar-week"></i></div>
            <div>
                <div class="kp-stat-label">Bulan Ini</div>
                <div class="kp-stat-value"><?= rupiah($monthRevenue) ?></div>
                <div class="kp-stat-sub"><?= (int) $monthTransactions ?> transaksi</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kp-stat kp-stat-green">
            <div class="kp-stat-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div>
                <div class="kp-stat-label">Total Transaksi Lunas</div>
                <div class="kp-stat-value"><?= (int) $totalTransactions ?></div>
                <div class="kp-stat-sub">sejak awal</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kp-stat kp-stat-dark">
            <div class="kp-stat-icon"><i class="bi bi-egg-fried"></i></div>
            <div>
                <div class="kp-stat-label">Menu</div>
                <div class="kp-stat-value"><?= (int) $availableMenus ?>/<?= (int) $totalMenus ?></div>
                <div class="kp-stat-sub">tersedia</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card kp-card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-hourglass-split text-kp-brown"></i> Status Pesanan Aktif</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="badge badge-kp-info">Dibayar (menunggu)</span>
                    <span class="fw-bold fs-5"><?= (int) $statusCounts['dibayar'] ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="badge badge-kp-warning">Diproses</span>
                    <span class="fw-bold fs-5"><?= (int) $statusCounts['diproses'] ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="badge badge-kp-success">Siap Diambil</span>
                    <span class="fw-bold fs-5"><?= (int) $statusCounts['siap_diambil'] ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card kp-card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-trophy text-kp-brown"></i> Menu Terlaris (<?= e($bulanIni) ?>)</div>
            <div class="card-body p-0">
                <?php if (empty($topMenus)): ?>
                    <div class="kp-empty py-4"><i class="bi bi-bar-chart"></i><p>Belum ada penjualan bulan ini.</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>#</th><th>Menu</th><th class="text-end">Terjual</th><th class="text-end">Pendapatan</th></tr></thead>
                            <tbody>
                            <?php foreach ($topMenus as $i => $m): ?>
                                <tr>
                                    <td><span class="kp-rank"><?= $i + 1 ?></span></td>
                                    <td class="fw-semibold"><?= e($m['menu_name']) ?></td>
                                    <td class="text-end"><?= (int) $m['qty'] ?> pcs</td>
                                    <td class="text-end fw-semibold text-kp-green"><?= rupiah($m['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card kp-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-clock-history text-kp-brown"></i> Transaksi Terbaru</span>
                <a href="<?= base_url('reports') ?>" class="btn btn-sm btn-kp-outline">Lihat Laporan</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentOrders)): ?>
                    <div class="kp-empty py-5"><i class="bi bi-inbox"></i><p>Belum ada transaksi.</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>No. Transaksi</th><th>Kasir</th><th>Metode</th><th class="text-end">Total</th><th>Waktu</th></tr></thead>
                            <tbody>
                            <?php foreach ($recentOrders as $o): ?>
                                <tr>
                                    <td>
                                        <a class="fw-semibold text-decoration-none" href="<?= base_url('orders/' . $o['id']) ?>"><?= e($o['order_number']) ?></a>
                                        <?php if (!empty($o['customer_name'])): ?>
                                            <div class="small text-muted"><i class="bi bi-person"></i> <?= e($o['customer_name']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($o['cashier_name']) ?></td>
                                    <td><?= e(payment_method_label($o['payment_method'])) ?></td>
                                    <td class="text-end fw-semibold"><?= rupiah($o['total']) ?></td>
                                    <td class="text-muted small"><?= e(indonesian_date($o['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card kp-card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-wallet2 text-kp-brown"></i> Ringkasan Metode Bayar (<?= e($bulanIni) ?>)</div>
            <div class="card-body">
                <?php if (empty($paymentSummary)): ?>
                    <div class="kp-empty py-4"><i class="bi bi-wallet"></i><p>Belum ada pembayaran.</p></div>
                <?php else: ?>
                    <?php foreach ($paymentSummary as $p): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-dot"></i> <?= e(payment_method_label($p['payment_method'])) ?></span>
                            <span class="fw-semibold"><?= rupiah($p['total_amount']) ?></span>
                        </div>
                        <div class="text-muted small mb-1"><?= (int) $p['trx_count'] ?> transaksi</div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
