<?php
/**
 * @var array $queue
 * @var int $queueCount
 * @var int $processingCount
 * @var int $readyCount
 */
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kp-stat kp-stat-brown">
            <div class="kp-stat-icon"><i class="bi bi-list-check"></i></div>
            <div><div class="kp-stat-label">Total Antrean</div><div class="kp-stat-value"><?= (int) $queueCount ?></div><div class="kp-stat-sub">pesanan aktif</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kp-stat kp-stat-cream">
            <div class="kp-stat-icon"><i class="bi bi-cup-hot"></i></div>
            <div><div class="kp-stat-label">Sedang Diproses</div><div class="kp-stat-value"><?= (int) $processingCount ?></div><div class="kp-stat-sub">dibuat barista</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kp-stat kp-stat-green">
            <div class="kp-stat-icon"><i class="bi bi-bag-check"></i></div>
            <div><div class="kp-stat-label">Siap Diambil</div><div class="kp-stat-value"><?= (int) $readyCount ?></div><div class="kp-stat-sub">menunggu pelanggan</div></div>
        </div>
    </div>
</div>

<div class="card kp-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-cup-hot text-kp-brown"></i> Antrean Pesanan</span>
        <a href="<?= base_url('barista/queue') ?>" class="btn btn-sm btn-kp-outline">Kelola Antrean</a>
    </div>
    <div class="card-body">
        <?php if (empty($queue)): ?>
            <div class="kp-empty py-5"><i class="bi bi-cup"></i><p>Belum ada pesanan yang perlu dibuat.</p></div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($queue as $o): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="kp-queue-card border-start border-4
                            <?= $o['order_status'] === 'dibayar' ? 'border-info' : ($o['order_status'] === 'diproses' ? 'border-warning' : 'border-success') ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold"><?= e($o['order_number']) ?></div>
                                    <div class="text-muted small"><?= e(indonesian_date($o['paid_at'])) ?></div>
                                </div>
                                <span class="badge <?= order_status_badge($o['order_status']) ?>"><?= e(order_status_label($o['order_status'])) ?></span>
                            </div>
                            <?php if (!empty($o['customer_name'])): ?>
                                <div class="mt-2 fw-bold text-kp-brown"><i class="bi bi-person-badge"></i> <?= e($o['customer_name']) ?></div>
                            <?php endif; ?>
                            <div class="mt-2 small text-muted">
                                <i class="bi bi-person"></i> Kasir: <?= e($o['cashier_name']) ?>
                            </div>
                            <a href="<?= base_url('barista/order/' . $o['id']) ?>" class="btn btn-sm btn-kp-primary mt-3 w-100">Lihat Detail</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
