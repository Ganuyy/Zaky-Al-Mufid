<?php
/** @var string $content */
$title = $title ?? app_config('name');
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
<body class="kp-blank">
    <?= $content ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <?= $scripts ?? '' ?>
</body>
</html>
