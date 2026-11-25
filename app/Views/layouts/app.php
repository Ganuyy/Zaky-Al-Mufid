<?php
/** @var string $content */

use App\Core\Router;

$user = auth_user();
$title = $title ?? app_config('name');
$currentUri = Router::currentUri();

$roleLabel = [
    'admin'   => 'Administrator',
    'kasir'   => 'Kasir',
    'barista' => 'Barista',
][$user['role'] ?? ''] ?? 'Pengguna';

$flashSuccess = $success ?? get_flash('success');
$flashError   = $error ?? get_flash('error');

/** Menu navigasi sesuai role. */
$menus = [
    ['label' => 'Dashboard',        'url' => 'dashboard',      'icon' => 'speedometer2',      'roles' => ['admin', 'kasir', 'barista']],
    ['label' => 'Kasir / POS',      'url' => 'pos',            'icon' => 'cash-register',     'roles' => ['admin', 'kasir']],
    ['label' => 'Antrean Barista',  'url' => 'barista/queue',  'icon' => 'cup-hot',           'roles' => ['admin', 'barista']],
    ['label' => 'Riwayat Transaksi','url' => 'orders/history', 'icon' => 'receipt',           'roles' => ['admin', 'kasir']],
    ['divider' => 'Manajemen',      'roles' => ['admin']],
    ['label' => 'Kelola Menu',      'url' => 'menus',          'icon' => 'menu-button-wide',  'roles' => ['admin']],
    ['label' => 'Kategori',         'url' => 'categories',     'icon' => 'tags',              'roles' => ['admin']],
    ['label' => 'Kelola Pengguna',  'url' => 'users',          'icon' => 'people',            'roles' => ['admin']],
    ['label' => 'Laporan Penjualan','url' => 'reports',        'icon' => 'graph-up-arrow',    'roles' => ['admin']],
];

$isActive = static function (string $url) use ($currentUri): bool {
    if ($url === 'dashboard') {
        return $currentUri === '/dashboard';
    }

    return $currentUri === '/' . $url || str_starts_with($currentUri, '/' . $url . '/');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> &middot; <?= e(app_config('short')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/brand/favicon.svg') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/brand/favicon-32.png') ?>">
    <link rel="shortcut icon" href="<?= asset('images/brand/favicon.ico') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/brand/favicon-180.png') ?>">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <?= $head ?? '' ?>
</head>
<body class="kp-app">
<div class="kp-shell">
    <aside class="kp-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="kpSidebar">
        <div class="kp-sidebar-brand">
            <a class="kp-sidebar-logo" href="<?= base_url('dashboard') ?>">
                <?= siporder_logo(['type' => 'horizontal', 'variant' => 'light', 'class' => 'kp-logo-sidebar']) ?>
            </a>
            <button type="button" class="kp-sidebar-close d-lg-none" data-bs-dismiss="offcanvas" aria-label="Tutup menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="offcanvas-body p-0 kp-sidebar-body">
            <ul class="nav flex-column kp-nav">
                <?php foreach ($menus as $item): ?>
                    <?php if (!in_array($user['role'] ?? '', $item['roles'], true)) { continue; } ?>
                    <?php if (isset($item['divider'])): ?>
                        <li class="kp-nav-divider"><?= e($item['divider']) ?></li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive($item['url']) ? 'active' : '' ?>"
                               href="<?= base_url($item['url']) ?>">
                                <i class="bi bi-<?= e($item['icon']) ?>"></i>
                                <span><?= e($item['label']) ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <div class="kp-workspace">
        <header class="kp-topbar">
            <button class="btn kp-topbar-toggle d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#kpSidebar" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>

            <a class="kp-topbar-brand" href="<?= base_url('dashboard') ?>" aria-label="SipOrder">
                <?= siporder_logo(['type' => 'horizontal', 'variant' => 'light', 'class' => 'kp-logo-topbar']) ?>
            </a>

            <div class="dropdown ms-auto">
                <button class="btn btn-kp-user dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <span class="kp-avatar"><?= e(strtoupper(mb_substr($user['name'] ?? 'U', 0, 1))) ?></span>
                    <span class="d-none d-sm-inline text-start lh-1">
                        <strong class="d-block" style="font-size:.9rem"><?= e($user['name'] ?? '') ?></strong>
                        <small class="text-white-50"><?= e($roleLabel) ?></small>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li class="px-3 py-2">
                        <div class="fw-semibold"><?= e($user['name'] ?? '') ?></div>
                        <div class="text-muted small">@<?= e($user['username'] ?? '') ?> &middot; <?= e($roleLabel) ?></div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="post" action="<?= base_url('logout') ?>" class="px-3 pb-2">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-box-arrow-right me-1"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="kp-main">
            <div class="container-fluid py-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h1 class="h4 fw-bold mb-0 text-kp-brown"><?= e($title) ?></h1>
                    </div>
                    <?= $headerActions ?? '' ?>
                </div>

                <?php if (!empty($flashSuccess)): ?>
                    <div class="alert alert-success alert-dismissible fade show kp-alert" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= e($flashSuccess) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($flashError)): ?>
                    <div class="alert alert-danger alert-dismissible fade show kp-alert" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($flashError) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.SO_BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.SO_CSRF = <?= json_encode(csrf_token()) ?>;
</script>
<script src="<?= asset('js/app.js') ?>"></script>
<?= $scripts ?? '' ?>
</body>
</html>
