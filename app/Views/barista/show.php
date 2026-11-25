<?php
/**
 * @var array $order
 * @var array $items
 * @var array $logs
 */
$flow = app_config('order_status_flow', []);
$nextStatuses = $order['payment_status'] === 'lunas' ? ($flow[$order['order_status']] ?? []) : [];
$actionLabel = [
    'diproses'     => ['Terima &amp; Proses', 'play-fill', 'btn-kp-primary'],
    'siap_diambil' => ['Tandai Siap Diambil', 'bell-fill', 'btn-kp-success'],
    'selesai'      => ['Selesaikan Pesanan', 'check2-all', 'btn-kp-dark'],
    'dibatalkan'   => ['Batalkan Pesanan', 'x-circle', 'btn-outline-danger'],
];
?>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card kp-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-cup-hot text-kp-brown"></i> <?= e($order['order_number']) ?></span>
                <span class="badge <?= order_status_badge($order['order_status']) ?>"><?= e(order_status_label($order['order_status'])) ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($order['customer_name'])): ?>
                    <div class="mb-3"><span class="kp-customer-badge fs-6"><i class="bi bi-person-badge"></i> <?= e($order['customer_name']) ?></span></div>
                <?php endif; ?>
                <div class="row g-2 mb-3 small">
                    <div class="col-6 col-md-4"><span class="text-muted">Dipesan:</span><br><strong><?= e(indonesian_date($order['created_at'])) ?></strong></div>
                    <div class="col-6 col-md-4"><span class="text-muted">Dibayar:</span><br><strong><?= e(indonesian_date($order['paid_at'])) ?></strong></div>
                    <div class="col-6 col-md-4"><span class="text-muted">Durasi:</span><br><strong><?= e(duration_text($order['paid_at'], $order['order_status'] === 'selesai' ? $order['updated_at'] : null)) ?></strong></div>
                </div>

                <div class="kp-order-items">
                    <?php foreach ($items as $it): ?>
                        <div class="kp-order-item">
                            <div class="kp-order-qty"><?= (int) $it['quantity'] ?>x</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?= e($it['menu_name']) ?></div>
                                <?php if (!empty($it['notes'])): ?>
                                    <div class="kp-note"><i class="bi bi-chat-left-text"></i> <?= e($it['notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-footer bg-white">
                <?php if (!empty($nextStatuses)): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($nextStatuses as $ns): ?>
                            <?php if (isset($actionLabel[$ns])): [$label, $icon, $cls] = $actionLabel[$ns]; ?>
                                <form method="post" action="<?= base_url('orders/' . $order['id'] . '/status') ?>"
                                      <?= $ns === 'dibatalkan' ? 'data-confirm="Batalkan pesanan ini?"' : '' ?>>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="<?= e($ns) ?>">
                                    <button class="btn <?= e($cls) ?>"><i class="bi bi-<?= e($icon) ?>"></i> <?= $label ?></button>
                                </form>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted small">Tidak ada aksi status yang tersedia.</span>
                <?php endif; ?>
                <a href="<?= base_url('barista/queue') ?>" class="btn btn-outline-secondary float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card kp-card">
            <div class="card-header fw-semibold"><i class="bi bi-diagram-3 text-kp-brown"></i> Riwayat Status</div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="kp-empty py-4"><i class="bi bi-hourglass"></i><p>Belum ada log.</p></div>
                <?php else: ?>
                    <ul class="kp-timeline">
                        <?php foreach ($logs as $log): ?>
                            <li>
                                <span class="kp-timeline-dot <?= order_status_badge($log['new_status']) ?>"></span>
                                <div class="fw-semibold"><?= e(order_status_label($log['new_status'])) ?></div>
                                <div class="small text-muted"><?= e(indonesian_date($log['created_at'])) ?> &middot; <?= e($log['user_name'] ?? 'Sistem') ?></div>
                                <div class="small"><?= e($log['notes']) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
