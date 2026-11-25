<?php
/**
 * @var array $queue
 * @var array $history
 * @var string $status
 */
$nextAction = static function (string $current): ?array {
    return match ($current) {
        'dibayar'      => ['status' => 'diproses', 'label' => 'Terima &amp; Proses', 'icon' => 'play-fill', 'class' => 'btn-kp-primary'],
        'diproses'     => ['status' => 'siap_diambil', 'label' => 'Tandai Siap Diambil', 'icon' => 'bell-fill', 'class' => 'btn-kp-success'],
        'siap_diambil' => ['status' => 'selesai', 'label' => 'Selesai', 'icon' => 'check2-all', 'class' => 'btn-kp-dark'],
        default        => null,
    };
};
?>
<h2 class="h6 fw-bold text-kp-brown mb-2"><i class="bi bi-fire"></i> Pesanan Aktif (<?= count($queue) ?>)</h2>
<?php if (empty($queue)): ?>
    <div class="card kp-card mb-4"><div class="kp-empty py-5"><i class="bi bi-cup"></i><p>Tidak ada pesanan yang perlu dibuat saat ini.</p></div></div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <?php foreach ($queue as $o): ?>
            <?php $action = $nextAction($o['order_status']); ?>
            <div class="col-md-6 col-xl-4">
                <div class="kp-queue-card h-100 border-start border-4
                    <?= $o['order_status'] === 'dibayar' ? 'border-info' : ($o['order_status'] === 'diproses' ? 'border-warning' : 'border-success') ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold fs-6"><?= e($o['order_number']) ?></div>
                            <div class="text-muted small"><i class="bi bi-clock"></i> <?= e(indonesian_date($o['paid_at'])) ?></div>
                        </div>
                        <span class="badge <?= order_status_badge($o['order_status']) ?>"><?= e(order_status_label($o['order_status'])) ?></span>
                    </div>
                    <?php if (!empty($o['customer_name'])): ?>
                        <div class="mt-2 fw-bold text-kp-brown fs-6">
                            <i class="bi bi-person-badge"></i> <?= e($o['customer_name']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="mt-2 small">
                        <i class="bi bi-person"></i> Kasir: <?= e($o['cashier_name']) ?><br>
                        <i class="bi bi-hourglass-split"></i> Menunggu: <?= e(duration_text($o['paid_at'])) ?>
                    </div>
                    <?php if (!empty($o['items'])): ?>
                        <div class="mt-2 small">
                            <?php foreach ($o['items'] as $it): ?>
                                <div>
                                    <span class="fw-semibold"><?= (int) $it['quantity'] ?>x <?= e($it['menu_name']) ?></span>
                                    <?php if (!empty($it['notes'])): ?>
                                        <div class="kp-note"><i class="bi bi-chat-left-text"></i> <?= e($it['notes']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex gap-2 mt-3">
                        <a href="<?= base_url('barista/order/' . $o['id']) ?>" class="btn btn-sm btn-outline-secondary flex-fill">Detail</a>
                        <?php if ($action !== null): ?>
                            <form method="post" action="<?= base_url('orders/' . $o['id'] . '/status') ?>" class="flex-fill">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="<?= e($action['status']) ?>">
                                <button class="btn btn-sm <?= e($action['class']) ?> w-100">
                                    <i class="bi bi-<?= e($action['icon']) ?>"></i> <?= $action['label'] ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card kp-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-clock-history text-kp-brown"></i> Riwayat Pesanan</span>
        <div class="btn-group btn-group-sm">
            <a href="<?= base_url('barista/queue?status=selesai') ?>" class="btn <?= $status === 'selesai' ? 'btn-kp-primary' : 'btn-outline-secondary' ?>">Selesai</a>
            <a href="<?= base_url('barista/queue?status=dibatalkan') ?>" class="btn <?= $status === 'dibatalkan' ? 'btn-kp-primary' : 'btn-outline-secondary' ?>">Dibatalkan</a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($history)): ?>
            <div class="kp-empty py-5"><i class="bi bi-archive"></i><p>Belum ada riwayat pesanan.</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>No. Transaksi</th><th>Kasir</th><th>Status</th><th>Selesai Pada</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($history as $o): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($o['order_number']) ?></td>
                            <td><?= e($o['cashier_name']) ?></td>
                            <td><span class="badge <?= order_status_badge($o['order_status']) ?>"><?= e(order_status_label($o['order_status'])) ?></span></td>
                            <td class="text-muted small"><?= e(indonesian_date($o['updated_at'])) ?></td>
                            <td class="text-end"><a href="<?= base_url('barista/order/' . $o['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
