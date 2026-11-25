<?php
/**
 * @var array $menus
 * @var array $categories
 * @var string $search
 * @var int $categoryId
 * @var array $cart
 */
?>
<div class="row g-3">
    <!-- Daftar Menu -->
    <div class="col-lg-8">
        <div class="card kp-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= base_url('pos') ?>" class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Cari menu...">
                        </div>
                    </div>
                    <div class="col-md-5 d-flex gap-2">
                        <select name="category_id" class="form-select">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= (int) $categoryId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-kp-primary"><i class="bi bi-funnel"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($menus)): ?>
            <div class="card kp-card"><div class="kp-empty py-5"><i class="bi bi-cup"></i><p>Tidak ada menu yang cocok / tersedia.</p></div></div>
        <?php else: ?>
            <div class="row g-3" id="menuGrid">
                <?php foreach ($menus as $m): ?>
                    <div class="col-sm-6 col-xl-4">
                        <div class="kp-menu-card h-100">
                            <div class="kp-menu-thumb">
                                <?php if (!empty($m['image'])): ?>
                                    <img src="<?= asset('images/' . $m['image']) ?>" alt="<?= e($m['name']) ?>">
                                <?php else: ?>
                                    <i class="bi bi-cup-hot"></i>
                                <?php endif; ?>
                                <span class="kp-menu-cat"><?= e($m['category_name']) ?></span>
                            </div>
                            <div class="kp-menu-body">
                                <div class="fw-semibold kp-menu-name"><?= e($m['name']) ?></div>
                                <div class="kp-menu-price"><?= rupiah($m['price']) ?></div>
                                <button type="button" class="btn btn-kp-primary btn-sm w-100 mt-2 kp-add-menu"
                                        data-id="<?= (int) $m['id'] ?>"
                                        data-name="<?= e($m['name']) ?>"
                                        data-price="<?= rupiah($m['price']) ?>"
                                        data-custom-type="<?= e(menu_customization_type($m['category_name'])) ?>">
                                    <i class="bi bi-plus-lg"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Keranjang -->
    <div class="col-lg-4">
        <div class="kp-cart" id="cartPanel">
            <div class="kp-cart-head">
                <span class="fw-semibold"><i class="bi bi-cart3"></i> Keranjang</span>
                <span class="badge badge-kp-cream" id="cartCount"><?= (int) $cart['count'] ?></span>
            </div>

            <div id="cartProblems" class="mx-3"></div>

            <div class="kp-cart-body" id="cartItems">
                <?php if (empty($cart['items'])): ?>
                    <div class="kp-empty py-5"><i class="bi bi-bag"></i><p>Keranjang masih kosong.</p></div>
                <?php endif; ?>
            </div>

            <div class="kp-cart-foot">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-semibold" id="cartSubtotal"><?= rupiah($cart['subtotal']) ?></span>
                </div>
                <div class="d-flex justify-content-between fs-5 mb-3">
                    <span class="fw-bold">TOTAL</span>
                    <span class="fw-bold text-kp-green" id="cartTotal"><?= rupiah($cart['total']) ?></span>
                </div>
                <a href="<?= base_url('checkout') ?>" class="btn btn-kp-primary w-100 py-2 fw-semibold" id="btnCheckout">
                    <i class="bi bi-credit-card"></i> Lanjut ke Pembayaran
                </a>
                <button type="button" class="btn btn-link text-danger w-100 mt-1 btn-sm" id="btnClearCart">
                    <i class="bi bi-trash"></i> Kosongkan Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Menu -->
<div class="modal fade" id="addMenuModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cart-plus text-kp-brown"></i> Tambah ke Keranjang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="fw-bold fs-5" id="modalMenuName">-</div>
                    <div class="text-muted" id="modalMenuPrice">-</div>
                </div>
                <input type="hidden" id="modalMenuId">
                <div class="mb-3">
                    <label class="form-label">Jumlah</label>
                    <div class="input-group" style="max-width:180px">
                        <button class="btn btn-outline-secondary" type="button" id="modalQtyMinus"><i class="bi bi-dash"></i></button>
                        <input type="number" class="form-control text-center" id="modalQty" value="1" min="1" max="99">
                        <button class="btn btn-outline-secondary" type="button" id="modalQtyPlus"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-1">Customisasi <span class="text-muted small">(opsional, pilih satu per kelompok)</span></label>
                    <div id="modalOptions" class="kp-modal-options"></div>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-1" for="modalAdditionalNote">Additional Note <span class="text-muted small">(opsional)</span></label>
                    <input type="text" class="form-control" id="modalAdditionalNote" maxlength="80"
                           placeholder="Tulis catatan tambahan...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-kp-primary" id="modalAddConfirm"><i class="bi bi-check-lg"></i> Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<script id="initialCart" type="application/json"><?= json_encode($cart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script id="customOptions" type="application/json"><?= menu_customization_options_json() ?></script>
<?php $scripts = '<script src="' . asset('js/pos.js') . '"></script>'; ?>
