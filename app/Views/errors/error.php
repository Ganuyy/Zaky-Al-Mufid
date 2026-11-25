<?php
/**
 * @var int $status
 * @var string $message
 */
?>
<div class="kp-error-wrap">
    <div class="kp-error-card">
        <div class="kp-error-code"><?= (int) $status ?></div>
        <h1 class="h4 fw-bold text-kp-brown mb-2">Aduh, ada masalah</h1>
        <p class="text-muted"><?= e($message) ?></p>
        <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-kp-primary"><i class="bi bi-house"></i> Dashboard</a>
            <a href="<?= base_url('login') ?>" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-in-right"></i> Login</a>
        </div>
    </div>
</div>
