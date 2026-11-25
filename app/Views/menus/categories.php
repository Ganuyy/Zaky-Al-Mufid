<?php
/** @var array $categories */
?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card kp-card">
            <div class="card-header fw-semibold"><i class="bi bi-plus-circle text-kp-brown"></i> Tambah Kategori</div>
            <div class="card-body">
                <form method="post" action="<?= base_url('categories') ?>" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" maxlength="80" required placeholder="mis. Mocktail">
                    </div>
                    <button class="btn btn-kp-primary w-100"><i class="bi bi-save"></i> Simpan</button>
                </form>
            </div>
        </div>
        <a href="<?= base_url('menus') ?>" class="btn btn-link mt-2 p-0"><i class="bi bi-arrow-left"></i> Kembali ke Menu</a>
    </div>

    <div class="col-lg-8">
        <div class="card kp-card">
            <div class="card-header fw-semibold"><i class="bi bi-tags text-kp-brown"></i> Daftar Kategori</div>
            <div class="card-body p-0">
                <?php if (empty($categories)): ?>
                    <div class="kp-empty py-5"><i class="bi bi-tags"></i><p>Belum ada kategori.</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>#</th><th>Nama</th><th class="text-end" style="width:220px">Aksi</th></tr></thead>
                            <tbody>
                            <?php foreach ($categories as $i => $c): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <form method="post" action="<?= base_url('categories/' . $c['id']) ?>" class="d-flex gap-2" novalidate>
                                            <?= csrf_field() ?>
                                            <input type="text" name="name" class="form-control form-control-sm" value="<?= e($c['name']) ?>" maxlength="80" required>
                                            <button class="btn btn-sm btn-kp-outline" title="Simpan"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <form method="post" action="<?= base_url('categories/' . $c['id'] . '/delete') ?>" class="d-inline"
                                              data-confirm="Hapus kategori '<?= e($c['name']) ?>'?">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
