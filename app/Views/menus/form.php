<?php
/**
 * @var array|null $menu
 * @var array $categories
 * @var string $action
 * @var string|null $error
 */
$isEdit = $menu !== null;
$value = static function (string $key, string $default = '') use ($menu): string {
    $old = old($key, null);
    if ($old !== null && $old !== '') {
        return (string) $old;
    }
    if ($menu === null) {
        return $default;
    }

    return (string) ($menu[$key] ?? $default);
};
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?></div>
        <?php endif; ?>

        <div class="card kp-card">
            <div class="card-header fw-semibold">
                <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> text-kp-brown"></i>
                <?= $isEdit ? 'Ubah Menu' : 'Tambah Menu Baru' ?>
            </div>
            <div class="card-body">
                <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Menu <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" maxlength="120" required
                                   value="<?= e($value('name')) ?>" placeholder="mis. Es Kopi Susu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>"
                                        <?= (int) $value('category_id', '0') === (int) $c['id'] ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="text" name="price" class="form-control" required inputmode="numeric"
                                   value="<?= e($value('price', $isEdit ? (string) (int) $menu['price'] : '')) ?>" placeholder="18000">
                            <div class="form-text">Angka saja, contoh: 18000</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Ketersediaan</label>
                            <?php $avail = (int) $value('is_available', $isEdit ? (string) $menu['is_available'] : '1'); ?>
                            <select name="is_available" class="form-select">
                                <option value="1" <?= $avail === 1 ? 'selected' : '' ?>>Tersedia</option>
                                <option value="0" <?= $avail === 0 ? 'selected' : '' ?>>Tidak Tersedia</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="255"
                                      placeholder="Deskripsi singkat menu (opsional)"><?= e($value('description')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Gambar Menu</label>
                            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Format JPG/PNG/WEBP, maksimal 2 MB. Kosongkan jika tidak ingin mengubah.</div>
                            <?php if ($isEdit && !empty($menu['image'])): ?>
                                <img src="<?= asset('images/' . $menu['image']) ?>" alt="" class="kp-preview mt-2">
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('menus') ?>" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-kp-primary">
                            <i class="bi bi-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Menu' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
