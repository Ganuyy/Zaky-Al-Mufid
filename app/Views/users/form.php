<?php
/**
 * @var array|null $user
 * @var array $roles
 * @var string $action
 * @var string|null $error
 */
$isEdit = $user !== null;
$value = static function (string $key, string $default = '') use ($user): string {
    $old = old($key, null);
    if ($old !== null && $old !== '') {
        return (string) $old;
    }
    if ($user === null) {
        return $default;
    }

    return (string) ($user[$key] ?? $default);
};
$roleLabel = ['admin' => 'Admin', 'kasir' => 'Kasir', 'barista' => 'Barista'];
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?></div>
        <?php endif; ?>

        <div class="card kp-card">
            <div class="card-header fw-semibold">
                <i class="bi bi-person-<?= $isEdit ? 'gear' : 'plus' ?> text-kp-brown"></i>
                <?= $isEdit ? 'Ubah Pengguna' : 'Tambah Pengguna Baru' ?>
            </div>
            <div class="card-body">
                <form method="post" action="<?= e($action) ?>" novalidate autocomplete="off">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" maxlength="100" required value="<?= e($value('name')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" maxlength="50" required value="<?= e($value('username')) ?>" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= e($r) ?>" <?= $value('role', 'kasir') === $r ? 'selected' : '' ?>>
                                        <?= e($roleLabel[$r] ?? ucfirst($r)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Akun</label>
                            <?php $active = (int) $value('is_active', $isEdit ? (string) $user['is_active'] : '1'); ?>
                            <select name="is_active" class="form-select">
                                <option value="1" <?= $active === 1 ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= $active === 0 ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?></label>
                            <input type="password" name="password" class="form-control" minlength="6" autocomplete="new-password"
                                   placeholder="<?= $isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' ?>" <?= $isEdit ? '' : 'required' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" minlength="6" autocomplete="new-password">
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-kp-primary">
                            <i class="bi bi-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
