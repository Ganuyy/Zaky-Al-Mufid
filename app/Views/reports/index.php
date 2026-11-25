<?php
/**
 * @var string $dateFrom
 * @var string $dateTo
 * @var array $summary
 * @var float $revenueToday
 * @var int $transactionsToday
 * @var array $topMenus
 * @var array $daily
 * @var array $paymentSummary
 * @var array $orders
 * @var array $filters
 * @var int $page
 * @var int $totalPages
 * @var int $total
 */
$methods = app_config('payment_methods', []);
$maxDaily = 0.0;
foreach ($daily as $d) {
    $maxDaily = max($maxDaily, (float) $d['revenue']);
}
$qs = static function (array $overrides = []) use ($dateFrom, $dateTo, $filters): string {
    $params = array_filter([
        'date_from'      => $dateFrom,
        'date_to'        => $dateTo,
        'payment_method' => $filters['payment_method'] ?? '',
        'q'              => $filters['search'] ?? '',
    ], static fn ($v) => $v !== '' && $v !== null) + $overrides;

    return '?' . http_build_query($params);
};
?>
<div class="card kp-card mb-3">
    <div class="card-body">
        <form method="get" action="<?= base_url('reports') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tanggal Awal</label>
                <input type="date" name="date_from" class="form-control" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="date_to" class="form-control" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Metode</label>
                <select name="payment_method" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($methods as $k => $m): ?>
                        <option value="<?= e($k) ?>" <?= ($filters['payment_method'] ?? '') === $k ? 'selected' : '' ?>><?= e($m['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cari No. Transaksi</label>
                <input type="text" name="q" class="form-control" value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-kp-primary flex-fill"><i class="bi bi-funnel"></i> Terapkan</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="kp-stat kp-stat-brown">
            <div class="kp-stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div><div class="kp-stat-label">Total Pendapatan</div><div class="kp-stat-value"><?= rupiah($summary['revenue']) ?></div><div class="kp-stat-sub"><?= e($dateFrom) ?> s/d <?= e($dateTo) ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kp-stat kp-stat-cream">
            <div class="kp-stat-icon"><i class="bi bi-receipt"></i></div>
            <div><div class="kp-stat-label">Jumlah Transaksi</div><div class="kp-stat-value"><?= (int) $summary['transactions'] ?></div><div class="kp-stat-sub">lunas</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kp-stat kp-stat-green">
            <div class="kp-stat-icon"><i class="bi bi-box-seam"></i></div>
            <div><div class="kp-stat-label">Item Terjual</div><div class="kp-stat-value"><?= (int) $summary['items'] ?></div><div class="kp-stat-sub">pcs</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kp-stat kp-stat-dark">
            <div class="kp-stat-icon"><i class="bi bi-graph-up"></i></div>
            <div><div class="kp-stat-label">Rata-rata / Transaksi</div><div class="kp-stat-value"><?= rupiah($summary['average']) ?></div><div class="kp-stat-sub">Hari ini: <?= rupiah($revenueToday) ?></div></div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= base_url('reports/export' . $qs()) ?>" class="btn btn-kp-outline"><i class="bi bi-file-earmark-excel"></i> Export CSV</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card kp-card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-trophy text-kp-brown"></i> Menu Terlaris</div>
            <div class="card-body p-0">
                <?php if (empty($topMenus)): ?>
                    <div class="kp-empty py-4"><i class="bi bi-bar-chart"></i><p>Belum ada penjualan pada periode ini.</p></div>
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
    <div class="col-lg-5">
        <div class="card kp-card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-wallet2 text-kp-brown"></i> Ringkasan Metode Pembayaran</div>
            <div class="card-body">
                <?php if (empty($paymentSummary)): ?>
                    <div class="kp-empty py-4"><i class="bi bi-wallet"></i><p>Belum ada data.</p></div>
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

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card kp-card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-calendar3 text-kp-brown"></i> Penjualan Harian</div>
            <div class="card-body p-0">
                <?php if (empty($daily)): ?>
                    <div class="kp-empty py-4"><i class="bi bi-calendar-x"></i><p>Belum ada data harian.</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead><tr><th>Tanggal</th><th class="text-end">Transaksi</th><th class="text-end">Pendapatan</th></tr></thead>
                            <tbody>
                            <?php foreach ($daily as $d): ?>
                                <tr>
                                    <td><?= e(indonesian_date($d['sale_date'], false)) ?></td>
                                    <td class="text-end"><?= (int) $d['transactions'] ?></td>
                                    <td class="text-end fw-semibold"><?= rupiah($d['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card kp-card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-bar-chart-line text-kp-brown"></i> Grafik Pendapatan Harian</div>
            <div class="card-body">
                <?php if (empty($daily)): ?>
                    <div class="kp-empty py-4"><i class="bi bi-graph-up"></i><p>Tidak ada grafik.</p></div>
                <?php else: ?>
                    <div class="kp-chart">
                        <?php foreach ($daily as $d): $height = $maxDaily > 0 ? max(4, (int) round(((float) $d['revenue'] / $maxDaily) * 100)) : 4; ?>
                            <div class="kp-chart-col" title="<?= e(indonesian_date($d['sale_date'], false)) ?>: <?= rupiah($d['revenue']) ?>">
                                <div class="kp-chart-bar" style="height: <?= $height ?>%"></div>
                                <div class="kp-chart-label"><?= e(date('d/m', strtotime($d['sale_date']))) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card kp-card">
    <div class="card-header fw-semibold"><i class="bi bi-list-ul text-kp-brown"></i> Daftar Transaksi (<?= (int) $total ?>)</div>
    <div class="card-body p-0">
        <?php if (empty($orders)): ?>
            <div class="kp-empty py-5"><i class="bi bi-receipt"></i><p>Tidak ada transaksi pada periode ini.</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Kasir</th><th>Metode</th><th class="text-end">Total</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($o['order_number']) ?></td>
                            <td class="text-muted small"><?= e(indonesian_date($o['paid_at'] ?? $o['created_at'])) ?></td>
                            <td><?= e($o['cashier_name']) ?></td>
                            <td><?= e(payment_method_label($o['payment_method'])) ?></td>
                            <td class="text-end fw-semibold"><?= rupiah($o['total']) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= base_url('orders/' . $o['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                    <a href="<?= base_url('receipt/' . $o['id']) ?>" class="btn btn-sm btn-kp-outline"><i class="bi bi-printer"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white">
            <nav><ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('reports' . $qs(['page' => $p])) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>
