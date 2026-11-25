<?php
/** @var string|null $error */
/** @var string|null $success */
?>
<div class="kp-login-wrap">
    <div class="kp-login-stack">
        <div class="kp-brandmark">
            <?= siporder_logo(['type' => 'horizontal', 'variant' => 'dark', 'class' => 'kp-login-brandmark']) ?>
        </div>

        <div class="kp-login-card shadow-lg">
            <div class="kp-login-brand">
                <div class="kp-login-deco" aria-hidden="true">
                    <?= siporder_logo(['type' => 'icon', 'variant' => 'light']) ?>
                </div>
                <div class="kp-login-logo">
                    <?= siporder_logo(['type' => 'icon', 'variant' => 'light', 'class' => 'kp-login-mark']) ?>
                </div>
                <h1 class="h3 fw-bold mb-1">SipOrder</h1>
                <p class="mb-0 text-white-50">Sistem Pemesanan Minuman</p>
                <div class="kp-login-tags mt-4">
                    <span><i class="bi bi-check2-circle"></i> Kelola Pesanan</span>
                    <span><i class="bi bi-check2-circle"></i> Pembayaran &amp; Struk</span>
                    <span><i class="bi bi-check2-circle"></i> Antrean Barista</span>
                </div>
            </div>

            <div class="kp-login-form">
                <div class="kp-login-watermark" aria-hidden="true"><i class="bi bi-cup-hot"></i></div>
                <div class="kp-login-form-inner">
                    <div class="kp-login-heading">
                        <h2 class="h5 fw-bold text-kp-brown mb-1">Masuk ke Akun</h2>
                        <p class="text-muted small mb-0">Silakan masukkan kredensial Anda.</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success py-2 small">
                            <i class="bi bi-check-circle-fill me-1"></i><?= e($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= base_url('login') ?>" autocomplete="off" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <div class="input-group kp-login-input">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?= e(old('username')) ?>" placeholder="Username" maxlength="50" required autofocus>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group kp-login-input">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Password" maxlength="100" required>
                                <button class="btn btn-outline-secondary kp-toggle-pass" type="button" data-target="#password"
                                        aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-kp-primary w-100 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
