/* SipOrder - halaman pembayaran */
(function () {
    'use strict';

    var form = document.getElementById('paymentForm');
    if (!form) return;

    var subtotal = parseFloat(form.querySelector('[name="server_subtotal"]').value) || 0;
    var discountEl = document.getElementById('discount');
    var amountEl = document.getElementById('amountPaid');
    var changeEl = document.getElementById('changeDisplay');
    var sumDiscountEl = document.getElementById('sumDiscount');
    var sumTotalEl = document.getElementById('sumTotal');
    var cashFields = document.getElementById('cashFields');
    var refFields = document.getElementById('refFields');
    var refInput = document.getElementById('paymentReference');

    function fmt(n) {
        n = Math.round((Number(n) || 0));
        return 'Rp ' + n.toLocaleString('id-ID');
    }
    function digits(value) {
        return String(value || '').replace(/[^0-9]/g, '');
    }
    function currentDiscount() {
        var d = parseFloat(digits(discountEl.value)) || 0;
        if (d < 0) d = 0;
        if (d > subtotal) d = subtotal;
        return d;
    }
    function total() {
        return Math.max(0, subtotal - currentDiscount());
    }
    function selectedMethod() {
        var checked = form.querySelector('input[name="payment_method"]:checked');
        return checked ? checked.value : 'tunai';
    }

    function updateTotals() {
        var d = currentDiscount();
        var t = total();
        if (sumDiscountEl) sumDiscountEl.textContent = '- ' + fmt(d);
        if (sumTotalEl) sumTotalEl.textContent = fmt(t);
        return t;
    }

    function updateChange() {
        var t = updateTotals();
        var paid = parseFloat(digits(amountEl.value)) || 0;
        var change = paid - t;
        if (changeEl) {
            changeEl.textContent = fmt(change > 0 ? change : 0);
            changeEl.classList.toggle('text-danger', change < 0);
        }
        if (amountEl) {
            amountEl.classList.toggle('is-invalid', paid > 0 && change < 0);
        }
    }

    function applyMethod() {
        var method = selectedMethod();
        var isCash = method === 'tunai';
        cashFields.classList.toggle('d-none', !isCash);
        refFields.classList.toggle('d-none', isCash);
        if (amountEl) amountEl.required = isCash;
        if (refInput) refInput.required = !isCash;
        if (isCash) {
            updateChange();
        } else {
            updateTotals();
        }
    }

    function formatAmountField() {
        var raw = digits(amountEl.value);
        amountEl.value = raw === '' ? '' : Number(raw).toLocaleString('id-ID');
    }

    if (amountEl) {
        amountEl.addEventListener('input', function () {
            formatAmountField();
            updateChange();
        });
    }
    if (discountEl) {
        discountEl.addEventListener('input', function () {
            var raw = digits(discountEl.value);
            discountEl.value = raw;
            updateChange();
        });
    }

    document.querySelectorAll('.kp-quick').forEach(function (btn) {
        btn.addEventListener('click', function () {
            amountEl.value = Number(btn.getAttribute('data-amount')).toLocaleString('id-ID');
            updateChange();
        });
    });

    form.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
        radio.addEventListener('change', applyMethod);
    });

    // Validasi sebelum kirim (server tetap memvalidasi ulang)
    form.addEventListener('submit', function (e) {
        var t = total();
        if (t <= 0) {
            e.preventDefault();
            window.alert('Total pembayaran tidak valid.');
            return;
        }
        if (selectedMethod() === 'tunai') {
            var paid = parseFloat(digits(amountEl.value)) || 0;
            if (paid < t) {
                e.preventDefault();
                window.alert('Uang dibayar kurang dari total ' + fmt(t) + '.');
                amountEl.focus();
                return;
            }
        } else if (refInput && refInput.value.trim() === '') {
            e.preventDefault();
            window.alert('Nomor referensi pembayaran wajib diisi.');
            refInput.focus();
        }
    });

    // Inisialisasi
    formatAmountField();
    applyMethod();
    updateChange();
})();
