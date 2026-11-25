<?php
/**
 * @var array $receipt
 * @var int $width
 */
$store = $receipt['store'];
$order = $receipt['order'];
$items = $receipt['items'];
$payment = $receipt['payment'];

$head = '<link href="' . asset('css/print.css') . '" rel="stylesheet">';
$widthClass = $width === 80 ? 'receipt-80' : 'receipt-58';
?>
<div class="receipt-toolbar no-print">
    <a href="<?= base_url('pos') ?>" class="btn btn-kp-primary">
        <i class="bi bi-arrow-left"></i> Kembali ke Kasir
    </a>
    <div class="btn-group">
        <a href="<?= base_url('receipt/' . $order['id'] . '?width=58') ?>" class="btn btn-outline-secondary <?= $width === 58 ? 'active' : '' ?>">58 mm</a>
        <a href="<?= base_url('receipt/' . $order['id'] . '?width=80') ?>" class="btn btn-outline-secondary <?= $width === 80 ? 'active' : '' ?>">80 mm</a>
    </div>
    <button type="button" class="btn btn-kp-success" id="btnPrint" onclick="window.print()">
        <i class="bi bi-printer"></i> Cetak Struk
    </button>
    <a href="<?= base_url('orders/' . $order['id']) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-eye"></i> Detail Transaksi
    </a>
</div>

<div class="receipt-scroll">
    <div class="receipt <?= $widthClass ?> receipt-printable" id="receipt">
        <div class="r-center r-bold r-store"><?= e($store['name']) ?> (<?= e($store['short']) ?>)</div>
        <?php if ($store['address'] !== ''): ?>
            <div class="r-center r-sm"><?= e($store['address']) ?></div>
        <?php endif; ?>
        <?php if ($store['phone'] !== ''): ?>
            <div class="r-center r-sm">Telp. <?= e($store['phone']) ?></div>
        <?php endif; ?>

        <div class="r-line"></div>

        <div class="r-row"><span>No. Transaksi</span><span>: <?= e($order['order_number']) ?></span></div>
        <div class="r-row"><span>Tanggal</span><span>: <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></span></div>
        <div class="r-row"><span>Kasir</span><span>: <?= e($order['cashier_username'] ?? $order['cashier_name']) ?></span></div>
        <?php if (!empty($order['customer_name'])): ?>
            <div class="r-row"><span>Pelanggan</span><span>: <?= e($order['customer_name']) ?></span></div>
        <?php endif; ?>

        <div class="r-line"></div>

        <div class="r-items">
            <?php foreach ($items as $it): ?>
                <div class="r-item">
                    <div class="r-item-name"><?= e($it['menu_name']) ?></div>
                    <div class="r-row r-item-detail">
                        <span><?= (int) $it['quantity'] ?> x <?= rupiah($it['unit_price'], false) ?></span>
                        <span><?= rupiah($it['subtotal'], false) ?></span>
                    </div>
                    <?php if (!empty($it['notes'])): ?>
                        <div class="r-note">* <?= e($it['notes']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="r-line"></div>

        <div class="r-row"><span>Subtotal</span><span><?= rupiah($order['subtotal'], false) ?></span></div>
        <div class="r-row"><span>Diskon</span><span><?= rupiah($order['discount'], false) ?></span></div>
        <div class="r-row r-bold r-total"><span>TOTAL</span><span><?= rupiah($order['total'], false) ?></span></div>

        <div class="r-line"></div>

        <div class="r-row"><span>Metode</span><span>: <?= e(payment_method_label($order['payment_method'])) ?></span></div>
        <div class="r-row"><span>Uang Dibayar</span><span><?= rupiah($order['amount_paid'], false) ?></span></div>
        <div class="r-row"><span>Kembalian</span><span><?= rupiah($order['change_amount'], false) ?></span></div>
        <div class="r-row"><span>Status</span><span>: <?= e(payment_status_label($order['payment_status'])) ?></span></div>

        <div class="r-line"></div>

        <div class="r-center r-sm">Terima kasih!</div>
        <div class="r-center r-sm">Selamat menikmati</div>
        <div class="r-center r-bold r-store"><?= e($store['name']) ?></div>
        <div class="r-center r-xs">Dicetak: <?= e(date('d/m/Y H:i:s', strtotime($receipt['printed_at']))) ?></div>
    </div>
</div>

<div class="receipt-actions no-print">
    <button type="button" class="btn btn-kp-success" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Ulang Struk</button>
    <a href="<?= base_url('pos') ?>" class="btn btn-outline-secondary">Transaksi Baru</a>
</div>
