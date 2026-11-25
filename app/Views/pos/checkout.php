<?php
/**
 * @var array $cart
 * @var array $paymentMethods
 * @var string $checkoutToken
 */
$total = (float) $cart['total'];
?>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card kp-card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-bag-check text-kp-brown"></i> Ringkasan Pesanan</div>
            <div class="card-body p-0">
                <?php if (!empty($cart['problems'])): ?>
                    <div class="alert alert-warning m-3 mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e(implode(' ', $cart['problems'])) ?>
                    </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Menu</th><th class="text-center">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                        <?php foreach ($cart['items'] as $it): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($it['menu_name']) ?></div>
                                    <?php if (!empty($it['notes'])): ?>
                                        <div class="small text-muted"><i class="bi bi-chat-left-text"></i> <?= e($it['notes']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!$it['is_available']): ?>
                                        <span class="badge badge-kp-danger">Tidak tersedia</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= (int) $it['quantity'] ?></td>
                                <td class="text-end"><?= rupiah($it['unit_price']) ?></td>
                                <td class="text-end fw-semibold"><?= rupiah($it['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Subtotal</td>
                                <td class="text-end" id="sumSubtotal"><?= rupiah($cart['subtotal']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Diskon</td>
                                <td class="text-end" id="sumDiscount">- <?= rupiah(0) ?></td>
                            </tr>
                            <tr class="fs-5">
                                <td colspan="3" class="text-end fw-bold">TOTAL</td>
                                <td class="text-end fw-bold text-kp-green" id="sumTotal"><?= rupiah($cart['total']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <a href="<?= base_url('pos') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Kasir</a>
    </div>

    <div class="col-lg-5">
        <form method="post" action="<?= base_url('payment/process') ?>" id="paymentForm" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="checkout_token" value="<?= e($checkoutToken) ?>">
            <input type="hidden" name="server_total" value="<?= e((string) $total) ?>">
            <input type="hidden" name="server_subtotal" value="<?= e((string) $cart['subtotal']) ?>">

            <div class="card kp-card mb-3">
                <div class="card-header fw-semibold"><i class="bi bi-person-badge text-kp-brown"></i> Pelanggan</div>
                <div class="card-body">
                    <label class="form-label">Nama Pelanggan <span class="text-muted small">(opsional)</span></label>
                    <input type="text" name="customer_name" id="customerName" class="form-control" maxlength="100"
                           value="<?= e(old('customer_name')) ?>" placeholder="mis. Andi">
                    <div class="form-text">Dipakai untuk memanggil pesanan di antrean. Boleh dikosongkan.</div>
                </div>
            </div>

            <div class="card kp-card mb-3">
                <div class="card-header fw-semibold"><i class="bi bi-cash-coin text-kp-brown"></i> Metode Pembayaran</div>
                <div class="card-body">
                    <div class="row g-2" id="methodGroup">
                        <?php foreach ($paymentMethods as $key => $m): ?>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method" id="pm_<?= e($key) ?>"
                                       value="<?= e($key) ?>" data-requires-ref="<?= !empty($m['requires_reference']) ? '1' : '0' ?>"
                                       autocomplete="off" <?= $key === 'tunai' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary w-100 kp-method-btn" for="pm_<?= e($key) ?>">
                                    <i class="bi bi-<?= $key === 'tunai' ? 'cash' : ($key === 'qris' ? 'qr-code' : ($key === 'debit' ? 'credit-card' : 'wallet2')) ?> d-block fs-4"></i>
                                    <?= e($m['label']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card kp-card mb-3">
                <div class="card-header fw-semibold"><i class="bi bi-pencil-square text-kp-brown"></i> Detail Pembayaran</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Diskon (Rp) <span class="text-muted small">opsional</span></label>
                        <input type="text" name="discount" id="discount" class="form-control" inputmode="numeric"
                               value="0" placeholder="0">
                    </div>

                    <!-- Tunai -->
                    <div id="cashFields">
                        <div class="mb-2">
                            <label class="form-label">Uang Dibayar (Rp) <span class="text-danger">*</span></label>
                            <input type="text" name="amount_paid" id="amountPaid" class="form-control form-control-lg"
                                   inputmode="numeric" value="<?= e(number_format($total, 0, '', '')) ?>" placeholder="0">
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-kp-outline kp-quick" data-amount="<?= (int) $total ?>">Uang Pas</button>
                            <button type="button" class="btn btn-sm btn-kp-outline kp-quick" data-amount="20000">20.000</button>
                            <button type="button" class="btn btn-sm btn-kp-outline kp-quick" data-amount="50000">50.000</button>
                            <button type="button" class="btn btn-sm btn-kp-outline kp-quick" data-amount="100000">100.000</button>
                        </div>
                        <div class="kp-change-box">
                            <div class="d-flex justify-content-between">
                                <span>Kembalian</span>
                                <span class="fw-bold fs-5" id="changeDisplay">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Non-tunai -->
                    <div id="refFields" class="d-none">
                        <label class="form-label">Nomor Referensi / Bukti Pembayaran <span class="text-danger">*</span></label>
                        <input type="text" name="payment_reference" id="paymentReference" class="form-control" maxlength="64"
                               placeholder="mis. nomor transaksi QRIS / EDC / e-wallet">
                        <div class="form-text">Wajib diisi sebagai bukti konfirmasi pembayaran non-tunai.</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-kp-primary w-100 py-3 fw-bold fs-5" id="btnPay" <?= $cart['valid'] ? '' : 'disabled' ?>>
                <i class="bi bi-check-circle"></i> Konfirmasi Pembayaran
            </button>
            <?php if (!$cart['valid']): ?>
                <div class="alert alert-warning mt-2 small mb-0">
                    Perbaiki masalah pada keranjang sebelum melanjutkan pembayaran.
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php $scripts = '<script src="' . asset('js/checkout.js') . '"></script>'; ?>
