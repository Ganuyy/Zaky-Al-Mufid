<?php
/**
 * @var array $users
 * @var string $search
 * @var string $role
 * @var array $roles
 */
$roleBadge = ['admin' => 'badge-kp-brown', 'kasir' => 'badge-kp-info', 'barista' => 'badge-kp-cream'];
$roleLabel = ['admin' => 'Admin', 'kasir' => 'Kasir', 'barista' => 'Barista'];
?>
<div class="card kp-card mb-3">
    <div class="card-body">
        <form method="get" action="<?= base_url('users') ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cari Pengguna</label>
                <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Nama atau username...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">Semua Role</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= e($r) ?>" <?= $role === $r ? 'selected' : '' ?>><?= e($roleLabel[$r]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-kp-primary flex-fill"><i class="bi bi-search"></i> Cari</button>
                <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted small"><?= count($users) ?> pengguna</div>
    <a href="<?= base_url('users/create') ?>" class="btn btn-kp-primary btn-sm"><i class="bi bi-person-plus"></i> Tambah Pengguna</a>
</div>

<div class="card kp-card">
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
            <div class="kp-empty py-5"><i class="bi bi-people"></i><p>Tidak ada pengguna ditemukan.</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th class="text-center">Status</th><th class="text-end" style="width:230px">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($u['name']) ?></td>
                            <td class="text-muted">@<?= e($u['username']) ?></td>
                            <td><span class="badge <?= $roleBadge[$u['role']] ?? 'badge-kp-secondary' ?>"><?= e($roleLabel[$u['role']] ?? $u['role']) ?></span></td>
                            <td class="text-center">
                                <?php if ((int) $u['is_active'] === 1): ?>
                                    <span class="badge badge-kp-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-kp-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <form method="post" action="<?= base_url('users/' . $u['id'] . '/toggle') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-secondary" title="<?= (int) $u['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <i class="bi bi-<?= (int) $u['is_active'] === 1 ? 'toggle-on' : 'toggle-off' ?>"></i>
                                        </button>
                                    </form>
                                    <a href="<?= base_url('users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-kp-outline" title="Ubah"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="<?= base_url('users/' . $u['id'] . '/delete') ?>" class="d-inline"
                                          data-confirm="Hapus/nonaktifkan pengguna '<?= e($u['name']) ?>'?">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
