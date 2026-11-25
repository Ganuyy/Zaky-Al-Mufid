<?php
/**
 * @var array $order
 * @var array $items
 * @var array $logs
 */
?>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card kp-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-receipt text-kp-brown"></i> <?= e($order['order_number']) ?></span>
                <div class="d-flex gap-2">
                    <span class="badge <?= order_status_badge($order['order_status']) ?>"><?= e(order_status_label($order['order_status'])) ?></span>
                    <span class="badge <?= payment_status_badge($order['payment_status']) ?>"><?= e(payment_status_label($order['payment_status'])) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3"><div class="text-muted small">Tanggal</div><div class="fw-semibold"><?= e(indonesian_date($order['created_at'])) ?></div></div>
                    <div class="col-6 col-md-3"><div class="text-muted small">Kasir</div><div class="fw-semibold"><?= e($order['cashier_name']) ?></div></div>
                    <div class="col-6 col-md-3"><div class="text-muted small">Pelanggan</div><div class="fw-semibold"><?= e($order['customer_name'] ?? '') !== '' ? e($order['customer_name']) : '-' ?></div></div>
                    <div class="col-6 col-md-3"><div class="text-muted small">Metode Bayar</div><div class="fw-semibold"><?= e(payment_method_label($order['payment_method'])) ?></div></div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Menu</th><th class="text-center">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($it['menu_name']) ?></div>
                                    <?php if (!empty($it['notes'])): ?><div class="small text-muted"><i class="bi bi-chat-left-text"></i> <?= e($it['notes']) ?></div><?php endif; ?>
                                </td>
                                <td class="text-center"><?= (int) $it['quantity'] ?></td>
                                <td class="text-end"><?= rupiah($it['unit_price']) ?></td>
                                <td class="text-end fw-semibold"><?= rupiah($it['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end"><?= rupiah($order['subtotal']) ?></td></tr>
                            <tr><td colspan="3" class="text-end">Diskon</td><td class="text-end"><?= rupiah($order['discount']) ?></td></tr>
                            <tr class="fs-6"><td colspan="3" class="text-end fw-bold">TOTAL</td><td class="text-end fw-bold text-kp-green"><?= rupiah($order['total']) ?></td></tr>
                            <tr><td colspan="3" class="text-end">Dibayar</td><td class="text-end"><?= rupiah($order['amount_paid']) ?></td></tr>
                            <tr><td colspan="3" class="text-end">Kembalian</td><td class="text-end"><?= rupiah($order['change_amount']) ?></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap gap-2">
                <?php if ($order['payment_status'] === 'lunas'): ?>
                    <a href="<?= base_url('receipt/' . $order['id']) ?>" class="btn btn-kp-primary"><i class="bi bi-printer"></i> Cetak Struk</a>
                <?php endif; ?>
                <a href="<?= base_url('orders/history') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card kp-card">
            <div class="card-header fw-semibold"><i class="bi bi-diagram-3 text-kp-brown"></i> Riwayat Status</div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="kp-empty py-4"><i class="bi bi-hourglass"></i><p>Belum ada log status.</p></div>
                <?php else: ?>
                    <ul class="kp-timeline">
                        <?php foreach ($logs as $log): ?>
                            <li>
                                <span class="kp-timeline-dot <?= order_status_badge($log['new_status']) ?>"></span>
                                <div class="fw-semibold"><?= e(order_status_label($log['new_status'])) ?></div>
                                <div class="small text-muted"><?= e(indonesian_date($log['created_at'])) ?></div>
                                <div class="small"><?= e($log['notes']) ?></div>
                                <div class="small text-muted"><i class="bi bi-person"></i> <?= e($log['user_name'] ?? 'Sistem') ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
