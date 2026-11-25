/* SipOrder - logika keranjang POS */
(function () {
    'use strict';

    var URLs = {
        add: window.SO_BASE_URL + '/cart/add',
        update: window.SO_BASE_URL + '/cart/update',
        remove: window.SO_BASE_URL + '/cart/remove',
        clear: window.SO_BASE_URL + '/cart/clear'
    };

    var cartItemsEl = document.getElementById('cartItems');
    var cartCountEl = document.getElementById('cartCount');
    var cartSubtotalEl = document.getElementById('cartSubtotal');
    var cartTotalEl = document.getElementById('cartTotal');
    var problemsEl = document.getElementById('cartProblems');
    var checkoutBtn = document.getElementById('btnCheckout');
    var clearBtn = document.getElementById('btnClearCart');
    if (!cartItemsEl) return;

    var addModalEl = document.getElementById('addMenuModal');
    var addModal = addModalEl && window.bootstrap ? new bootstrap.Modal(addModalEl) : null;

    var CUSTOM_OPTIONS = {};
    var customOptionsEl = document.getElementById('customOptions');
    if (customOptionsEl) {
        try { CUSTOM_OPTIONS = JSON.parse(customOptionsEl.textContent) || {}; } catch (err) { CUSTOM_OPTIONS = {}; }
    }

    function esc(s) {
        return String(s === null || s === undefined ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function toast(message, ok) {
        var wrap = document.getElementById('kpToast');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = 'kpToast';
            wrap.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:1080;display:flex;flex-direction:column;gap:8px;';
            document.body.appendChild(wrap);
        }
        var el = document.createElement('div');
        el.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger') + ' shadow mb-0 py-2 px-3';
        el.style.minWidth = '240px';
        el.innerHTML = '<i class="bi ' + (ok ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') + ' me-2"></i>' + esc(message);
        wrap.appendChild(el);
        window.setTimeout(function () { el.remove(); }, 3000);
    }

    function renderCustomization(type) {
        var wrap = document.getElementById('modalOptions');
        var additionalNote = document.getElementById('modalAdditionalNote');
        if (!wrap) return;

        var groups = CUSTOM_OPTIONS[type] || CUSTOM_OPTIONS.noncoffee || [];
        var html = '';
        groups.forEach(function (group, gi) {
            var groupLabel = esc(group.label);
            html += '<div class="kp-opt-group" data-group-label="' + groupLabel + '">';
            html += '  <div class="kp-opt-title">' + groupLabel + '</div>';
            html += '  <div class="row g-1">';
            (group.options || []).forEach(function (opt, oi) {
                var id = 'opt_' + gi + '_' + oi;
                html += '    <div class="col-6">';
                html += '      <div class="form-check">';
                html += '        <input class="form-check-input kp-opt" type="radio" name="custom_group_' + gi + '" value="' + esc(opt) + '" id="' + id + '">';
                html += '        <label class="form-check-label" for="' + id + '">' + esc(opt) + '</label>';
                html += '      </div>';
                html += '    </div>';
            });
            var otherId = 'opt_' + gi + '_other';
            html += '    <div class="col-6">';
            html += '      <div class="form-check">';
            html += '        <input class="form-check-input kp-opt-other" type="radio" name="custom_group_' + gi + '" value="__other__" id="' + otherId + '">';
            html += '        <label class="form-check-label" for="' + otherId + '">Lainnya</label>';
            html += '      </div>';
            html += '    </div>';
            html += '  </div>';
            html += '  <input type="text" class="form-control form-control-sm mt-1 d-none kp-opt-other-input" maxlength="30" placeholder="Tulis permintaan khusus...">';
            html += '</div>';
        });

        if (groups.length === 0) {
            html = '<div class="text-muted small">Tambahkan catatan khusus jika diperlukan.</div>';
        }
        wrap.innerHTML = html;

        wrap.querySelectorAll('.kp-opt-other').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var group = radio.closest('.kp-opt-group');
                var input = group ? group.querySelector('.kp-opt-other-input') : null;
                if (input) {
                    input.classList.toggle('d-none', !radio.checked);
                    if (radio.checked) input.focus();
                }
            });
        });
        wrap.querySelectorAll('.kp-opt').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var group = radio.closest('.kp-opt-group');
                var input = group ? group.querySelector('.kp-opt-other-input') : null;
                if (input) input.classList.add('d-none');
            });
        });

        if (additionalNote) additionalNote.value = '';
    }

    function collectCustomization() {
        var selected = [];
        document.querySelectorAll('#modalOptions .kp-opt-group').forEach(function (group) {
            var checked = group.querySelector('input[type="radio"]:checked');
            if (!checked) return;

            var label = group.getAttribute('data-group-label') || '';
            if (checked.value === '__other__') {
                var input = group.querySelector('.kp-opt-other-input');
                var custom = input ? input.value.trim() : '';
                selected.push(label + ': ' + (custom !== '' ? 'Lainnya (' + custom + ')' : 'Lainnya'));
            } else {
                selected.push(label + ': ' + checked.value);
            }
        });

        var additionalNote = document.getElementById('modalAdditionalNote');
        if (additionalNote && additionalNote.value.trim() !== '') {
            selected.push('Catatan: ' + additionalNote.value.trim());
        }

        return selected.join(', ');
    }

    function renderCart(payload) {
        if (typeof payload === 'object' && payload.count !== undefined) {
            cartCountEl.textContent = payload.count;
        }

        if (problemsEl) {
            if (payload.problems && payload.problems.length) {
                problemsEl.innerHTML = '<div class="alert alert-warning py-2 small mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>' + esc(payload.problems.join(' ')) + '</div>';
            } else {
                problemsEl.innerHTML = '';
            }
        }

        if (!payload.items || payload.items.length === 0) {
            cartItemsEl.innerHTML = '<div class="kp-empty py-5"><i class="bi bi-bag"></i><p>Keranjang masih kosong.</p></div>';
        } else {
            var html = '';
            payload.items.forEach(function (it) {
                html += '<div class="kp-cart-item" data-key="' + esc(it.key !== undefined ? it.key : it.menu_id) + '">';
                html += '  <div class="d-flex justify-content-between align-items-start gap-2">';
                html += '    <div class="flex-grow-1">';
                html += '      <div class="kp-cart-item-name">' + esc(it.menu_name) + '</div>';
                html += '      <div class="text-muted small">' + esc(it.price_fmt) + '</div>';
                if (!it.is_available) {
                    html += '      <span class="badge badge-kp-danger">Tidak tersedia</span>';
                }
                html += '    </div>';
                html += '    <button type="button" class="btn btn-sm btn-outline-danger kp-remove" title="Hapus">&times;</button>';
                html += '  </div>';
                html += '  <div class="d-flex justify-content-between align-items-center mt-2 gap-2">';
                html += '    <div class="input-group input-group-sm" style="width:auto">';
                html += '      <button type="button" class="btn btn-outline-secondary kp-qty-btn kp-dec"><i class="bi bi-dash"></i></button>';
                html += '      <input type="text" class="form-control kp-qty-input kp-qty" value="' + it.quantity + '" inputmode="numeric">';
                html += '      <button type="button" class="btn btn-outline-secondary kp-qty-btn kp-inc"><i class="bi bi-plus"></i></button>';
                html += '    </div>';
                html += '    <div class="fw-bold text-kp-green">' + esc(it.subtotal_fmt) + '</div>';
                html += '  </div>';
                html += '  <input type="text" class="form-control form-control-sm mt-2 kp-notes" placeholder="Catatan / customisasi" maxlength="255" value="' + esc(it.notes) + '">';
                html += '</div>';
            });
            cartItemsEl.innerHTML = html;
        }

        if (cartSubtotalEl) cartSubtotalEl.textContent = payload.subtotal_fmt || 'Rp 0';
        if (cartTotalEl) cartTotalEl.textContent = payload.total_fmt || 'Rp 0';

        if (checkoutBtn) {
            var valid = !!payload.valid;
            checkoutBtn.classList.toggle('disabled', !valid);
            checkoutBtn.setAttribute('aria-disabled', valid ? 'false' : 'true');
        }
    }

    function handle(promise) {
        return promise.then(function (res) {
            if (res && res.message) {
                toast(res.message, !!res.ok);
            }
            renderCart(res || {});
            return res;
        }).catch(function () {
            toast('Koneksi gagal. Coba lagi.', false);
        });
    }

    // Tombol "Tambah" pada kartu menu -> buka modal
    document.querySelectorAll('.kp-add-menu').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('modalMenuId').value = btn.getAttribute('data-id');
            document.getElementById('modalMenuName').textContent = btn.getAttribute('data-name');
            document.getElementById('modalMenuPrice').textContent = btn.getAttribute('data-price');
            document.getElementById('modalQty').value = 1;
            renderCustomization(btn.getAttribute('data-custom-type') || 'noncoffee');
            if (addModal) addModal.show();
        });
    });

    var qtyInput = document.getElementById('modalQty');
    if (qtyInput) {
        document.getElementById('modalQtyMinus').addEventListener('click', function () {
            qtyInput.value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) - 1);
        });
        document.getElementById('modalQtyPlus').addEventListener('click', function () {
            qtyInput.value = Math.min(99, (parseInt(qtyInput.value, 10) || 1) + 1);
        });
    }

    var confirmAdd = document.getElementById('modalAddConfirm');
    if (confirmAdd) {
        confirmAdd.addEventListener('click', function () {
            var id = document.getElementById('modalMenuId').value;
            var qty = parseInt(qtyInput.value, 10) || 1;
            var notes = collectCustomization();
            confirmAdd.disabled = true;
            handle(window.SO.postForm(URLs.add, { menu_id: id, quantity: qty, notes: notes }))
                .finally(function () { confirmAdd.disabled = false; if (addModal) addModal.hide(); });
        });
    }

    // Event delegation untuk kontrol di dalam keranjang
    cartItemsEl.addEventListener('click', function (e) {
        var item = e.target.closest('.kp-cart-item');
        if (!item) return;
        var key = item.getAttribute('data-key');
        var qtyEl = item.querySelector('.kp-qty');
        var current = parseInt(qtyEl.value, 10) || 1;

        if (e.target.closest('.kp-inc')) {
            handle(window.SO.postForm(URLs.update, { line_key: key, quantity: current + 1 }));
        } else if (e.target.closest('.kp-dec')) {
            handle(window.SO.postForm(URLs.update, { line_key: key, quantity: current - 1 }));
        } else if (e.target.closest('.kp-remove')) {
            handle(window.SO.postForm(URLs.remove, { line_key: key }));
        }
    });

    cartItemsEl.addEventListener('change', function (e) {
        var item = e.target.closest('.kp-cart-item');
        if (!item) return;
        var key = item.getAttribute('data-key');
        if (e.target.classList.contains('kp-qty')) {
            var v = parseInt(e.target.value, 10);
            if (isNaN(v) || v < 1) v = 1;
            handle(window.SO.postForm(URLs.update, { line_key: key, quantity: v }));
        } else if (e.target.classList.contains('kp-notes')) {
            var qty = item.querySelector('.kp-qty');
            handle(window.SO.postForm(URLs.update, {
                line_key: key,
                quantity: qty ? qty.value : 1,
                notes: e.target.value
            }));
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (window.confirm('Kosongkan seluruh keranjang?')) {
                handle(window.SO.postForm(URLs.clear, {}));
            }
        });
    }

    // Blokir checkout bila item tidak valid
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function (e) {
            if (checkoutBtn.classList.contains('disabled')) {
                e.preventDefault();
                toast('Perbaiki masalah pada keranjang terlebih dahulu.', false);
            }
        });
    }

    // Render keranjang awal dari server (JSON tertanam)
    var initial = document.getElementById('initialCart');
    if (initial) {
        try {
            renderCart(JSON.parse(initial.textContent));
        } catch (err) { /* abaikan */ }
    }
})();
