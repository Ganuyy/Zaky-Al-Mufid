<?php
/**
 * @var array $menus
 * @var array $categories
 * @var string $search
 * @var int $categoryId
 */
?>
<div class="card kp-card mb-3">
    <div class="card-body">
        <form method="get" action="<?= base_url('menus') ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cari Menu</label>
                <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Nama menu...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) $categoryId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-kp-primary flex-fill"><i class="bi bi-search"></i> Cari</button>
                <a href="<?= base_url('menus') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted small">Menampilkan <?= count($menus) ?> menu</div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('categories') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-tags"></i> Kategori</a>
        <a href="<?= base_url('menus/create') ?>" class="btn btn-kp-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Menu</a>
    </div>
</div>

<div class="card kp-card">
    <div class="card-body p-0">
        <?php if (empty($menus)): ?>
            <div class="kp-empty py-5"><i class="bi bi-cup"></i><p>Belum ada menu. Tambahkan menu pertama Anda.</p>
                <a href="<?= base_url('menus/create') ?>" class="btn btn-kp-primary mt-2"><i class="bi bi-plus-lg"></i> Tambah Menu</a></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px">Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center">Status</th>
                            <th class="text-end" style="width:210px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($menus as $m): ?>
                        <tr>
                            <td>
                                <?php if (!empty($m['image'])): ?>
                                    <img src="<?= asset('images/' . $m['image']) ?>" alt="" class="kp-thumb">
                                <?php else: ?>
                                    <span class="kp-thumb kp-thumb-empty"><i class="bi bi-cup-hot"></i></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($m['name']) ?></div>
                                <?php if (!empty($m['description'])): ?><div class="text-muted small"><?= e($m['description']) ?></div><?php endif; ?>
                            </td>
                            <td><span class="badge badge-kp-cream"><?= e($m['category_name']) ?></span></td>
                            <td class="text-end fw-semibold"><?= rupiah($m['price']) ?></td>
                            <td class="text-center">
                                <?php if ((int) $m['is_available'] === 1): ?>
                                    <span class="badge badge-kp-success">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge badge-kp-danger">Kosong</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <form method="post" action="<?= base_url('menus/' . $m['id'] . '/toggle') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-secondary" title="<?= (int) $m['is_available'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <i class="bi bi-<?= (int) $m['is_available'] === 1 ? 'toggle-on' : 'toggle-off' ?>"></i>
                                        </button>
                                    </form>
                                    <a href="<?= base_url('menus/' . $m['id'] . '/edit') ?>" class="btn btn-sm btn-kp-outline" title="Ubah"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="<?= base_url('menus/' . $m['id'] . '/delete') ?>" class="d-inline"
                                          data-confirm="Hapus/nonaktifkan menu '<?= e($m['name']) ?>'?">
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
