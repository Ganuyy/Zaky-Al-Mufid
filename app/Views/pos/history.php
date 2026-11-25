<?php
/**
 * @var array $orders
 * @var array $filters
 * @var int $page
 * @var int $totalPages
 * @var int $total
 * @var array $cashiers
 * @var bool $isAdmin
 */
$qs = static function (array $overrides = []) use ($filters): string {
    $params = array_filter([
        'q'              => $filters['search'] ?? '',
        'order_status'   => $filters['order_status'] ?? '',
        'payment_status' => $filters['payment_status'] ?? '',
        'payment_method' => $filters['payment_method'] ?? '',
        'date_from'      => $filters['date_from'] ?? '',
        'date_to'        => $filters['date_to'] ?? '',
        'cashier_id'     => $filters['cashier_id'] ?? '',
    ], static fn ($v) => $v !== '' && $v !== null) + $overrides;

    return $params === [] ? '' : '?' . http_build_query($params);
};
$statuses = app_config('order_statuses', []);
$payStatuses = app_config('payment_statuses', []);
$methods = app_config('payment_methods', []);
?>
<div class="card kp-card mb-3">
    <div class="card-body">
        <form method="get" action="<?= base_url('orders/history') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cari No. Transaksi</label>
                <input type="text" name="q" class="form-control" value="<?= e($filters['search'] ?? '') ?>" placeholder="SO-...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai</label>
                <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status Pesanan</label>
                <select name="order_status" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($statuses as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= ($filters['order_status'] ?? '') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Bayar</label>
                <select name="payment_status" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($payStatuses as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= ($filters['payment_status'] ?? '') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($isAdmin && !empty($cashiers)): ?>
                <div class="col-md-3">
                    <label class="form-label">Kasir</label>
                    <select name="cashier_id" class="form-select">
                        <option value="">Semua Kasir</option>
                        <?php foreach ($cashiers as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= (int) ($filters['cashier_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-kp-primary flex-fill"><i class="bi bi-funnel"></i> Filter</button>
                <a href="<?= base_url('orders/history') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted small">Total <?= (int) $total ?> transaksi ditemukan</div>
</div>

<div class="card kp-card">
    <div class="card-body p-0">
        <?php if (empty($orders)): ?>
            <div class="kp-empty py-5"><i class="bi bi-receipt"></i><p>Tidak ada transaksi pada filter ini.</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th><th>Waktu</th><th>Kasir</th><th>Bayar</th>
                            <th>Status Pesanan</th><th>Status Bayar</th><th class="text-end">Total</th><th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($o['order_number']) ?></div>
                                <?php if (!empty($o['customer_name'])): ?>
                                    <div class="small text-muted"><i class="bi bi-person"></i> <?= e($o['customer_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= e(indonesian_date($o['created_at'])) ?></td>
                            <td><?= e($o['cashier_name']) ?></td>
                            <td><?= e(payment_method_label($o['payment_method'])) ?></td>
                            <td><span class="badge <?= order_status_badge($o['order_status']) ?>"><?= e(order_status_label($o['order_status'])) ?></span></td>
                            <td><span class="badge <?= payment_status_badge($o['payment_status']) ?>"><?= e(payment_status_label($o['payment_status'])) ?></span></td>
                            <td class="text-end fw-semibold"><?= rupiah($o['total']) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= base_url('orders/' . $o['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
                                    <?php if ($o['payment_status'] === 'lunas'): ?>
                                        <a href="<?= base_url('receipt/' . $o['id']) ?>" class="btn btn-sm btn-kp-outline" title="Struk"><i class="bi bi-printer"></i></a>
                                    <?php endif; ?>
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
            <nav>
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= base_url('orders/history' . $qs(['page' => $page - 1])) ?>">Sebelumnya</a>
                    </li>
                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= base_url('orders/history' . $qs(['page' => $p])) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= base_url('orders/history' . $qs(['page' => $page + 1])) ?>">Berikutnya</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
