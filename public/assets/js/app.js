/* SipOrder - skrip umum */
(function () {
    'use strict';

    // Konfirmasi aksi berbahaya: form[data-confirm]
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var msg = form.getAttribute('data-confirm') || 'Anda yakin?';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // Toggle lihat/sembunyikan password
    document.querySelectorAll('.kp-toggle-pass').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(btn.getAttribute('data-target'));
            if (!target) return;
            var isPassword = target.getAttribute('type') === 'password';
            target.setAttribute('type', isPassword ? 'text' : 'password');
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            }
            btn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            target.focus();
        });
    });

    // Cegah klik ganda: nonaktifkan tombol submit setelah form dikirim
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (form.dataset.submitted === '1') {
                return;
            }
            form.dataset.submitted = '1';
            var btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                var original = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
                // aktifkan kembali bila validasi HTML membatalkan submit
                window.setTimeout(function () {
                    if (document.activeElement !== btn) {
                        btn.disabled = false;
                        btn.innerHTML = original;
                        form.dataset.submitted = '0';
                    }
                }, 4000);
            }
        });
    });

    // Peringatan sebelum meninggalkan halaman POS dengan keranjang (opsional)
    // Ditangani di pos.js bila diperlukan.

    // Auto-hilangkan alert sukses setelah beberapa detik
    document.querySelectorAll('.kp-alert.alert-success').forEach(function (el) {
        window.setTimeout(function () {
            if (window.bootstrap && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(el).close();
            }
        }, 6000);
    });

    // Utilitas format angka (tanpa pemisah) untuk input
    window.SO = window.SO || {};
    window.SO.parseRupiah = function (value) {
        if (value === null || value === undefined) return 0;
        var cleaned = String(value).replace(/[^0-9.-]/g, '');
        var n = parseFloat(cleaned);
        return isNaN(n) ? 0 : n;
    };
    window.SO.formatRupiah = function (value) {
        var n = Math.round(Number(value) || 0);
        return 'Rp ' + n.toLocaleString('id-ID');
    };
    window.SO.postForm = function (url, data) {
        var body = new URLSearchParams();
        Object.keys(data).forEach(function (k) {
            body.append(k, data[k]);
        });
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': window.SO_CSRF || ''
            },
            body: body.toString(),
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return { ok: false, message: 'Respons tidak valid.' }; });
        });
    };
})();
