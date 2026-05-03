<?php
// Danh sách icon phổ biến cho quản lý tài chính cá nhân
$commonIcons = [
    'bi-tag','bi-tags','bi-bag','bi-cart','bi-cart3','bi-basket','bi-basket2',
    'bi-cup-hot','bi-cup-straw','bi-egg-fried','bi-apple',
    'bi-house','bi-shop','bi-building','bi-bank',
    'bi-car-front','bi-fuel-pump','bi-bus-front','bi-train-freight-front','bi-airplane','bi-bicycle',
    'bi-heart-pulse','bi-hospital','bi-bandaid','bi-prescription2',
    'bi-book','bi-mortarboard','bi-journal-text',
    'bi-controller','bi-dice-5','bi-film','bi-music-note-beamed','bi-ticket-perforated',
    'bi-piggy-bank','bi-wallet2','bi-cash-stack','bi-cash-coin','bi-credit-card',
    'bi-receipt','bi-receipt-cutoff','bi-graph-up-arrow',
    'bi-gift','bi-balloon','bi-cake','bi-stars',
    'bi-laptop','bi-phone','bi-tv','bi-headphones','bi-camera','bi-watch',
    'bi-tools','bi-wrench','bi-hammer','bi-scissors','bi-palette','bi-brush',
    'bi-droplet','bi-lightning','bi-fire','bi-lightbulb',
    'bi-tree','bi-flower1','bi-moon','bi-cloud-sun','bi-umbrella',
    'bi-person','bi-people','bi-briefcase','bi-emoji-smile',
    'bi-box-seam','bi-bookmark','bi-clipboard-data',
];
?>

<!--
  ── Icon Picker Panel ──────────────────────────────────────────
  Dùng một popup panel duy nhất (không phải Bootstrap modal).
  Trigger: bất kỳ element nào có attribute [data-icon-picker="<inputId>"].
  Hoạt động đúng cả khi trigger nằm trong modal lẫn ngoài modal.
  ──────────────────────────────────────────────────────────────
-->
<div id="iconPickerPanel" role="dialog" aria-modal="true" aria-label="Chọn Icon"
     style="display:none;position:fixed;z-index:9999;
            background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;
            box-shadow:0 8px 40px rgba(0,0,0,.22);padding:14px;
            width:340px;max-height:370px;overflow-y:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <span style="font-weight:700;font-size:.9rem;color:#1e293b">Chọn Icon</span>
        <button type="button" id="iconPanelClose"
                style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.2rem;line-height:1;padding:2px 6px"
                aria-label="Đóng">✕</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:5px">
        <?php foreach ($commonIcons as $ico): ?>
        <button type="button" class="ip-btn" data-icon="<?= $ico ?>"
                title="<?= $ico ?>"
                style="width:38px;height:38px;border:1px solid #e2e8f0;border-radius:8px;
                       background:#f8fafc;cursor:pointer;display:flex;align-items:center;
                       justify-content:center;transition:background .1s,border-color .1s">
            <i class="<?= $ico ?>" style="font-size:1.05rem;pointer-events:none"></i>
        </button>
        <?php endforeach; ?>
    </div>
</div>
<!-- Backdrop trong suốt để click ngoài đóng panel -->
<div id="iconPickerBackdrop" style="display:none;position:fixed;inset:0;z-index:9998"></div>

<style>
.ip-btn:hover { background:#e2e8f0 !important; border-color:#94a3b8 !important; }
.ip-btn:focus { outline:2px solid #6366f1; outline-offset:1px; }
</style>

<script>
(function () {
    'use strict';

    /* ─── State ─────────────────────────────────────────────── */
    let _targetInputId = null;

    const panel    = document.getElementById('iconPickerPanel');
    const backdrop = document.getElementById('iconPickerBackdrop');
    const closeBtn = document.getElementById('iconPanelClose');

    /* ─── Mở panel ──────────────────────────────────────────── */
    function openPanel(anchorEl, inputId) {
        _targetInputId = inputId;

        // Tính vị trí: ngay dưới anchor, tránh tràn viewport
        const rect = anchorEl.getBoundingClientRect();
        let top  = rect.bottom + 6;
        let left = rect.left;

        const pw = 340, ph = 370;
        if (left + pw > window.innerWidth  - 8) left = window.innerWidth  - pw - 8;
        if (left < 8) left = 8;
        if (top  + ph > window.innerHeight - 8) top  = Math.max(8, rect.top - ph - 6);

        panel.style.top     = top  + 'px';
        panel.style.left    = left + 'px';
        panel.style.display = 'block';
        backdrop.style.display = 'block';
        panel.focus?.();
    }

    /* ─── Đóng panel ─────────────────────────────────────────── */
    function closePanel() {
        panel.style.display    = 'none';
        backdrop.style.display = 'none';
        _targetInputId = null;
    }

    /* ─── Áp dụng icon vào input ─────────────────────────────── */
    function applyIcon(iconClass) {
        if (!_targetInputId) return;
        const el = document.getElementById(_targetInputId);
        if (!el) return;
        el.value = iconClass;
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /* ─── Sự kiện icon buttons ───────────────────────────────── */
    panel.querySelectorAll('.ip-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            applyIcon(btn.getAttribute('data-icon'));
            closePanel();
        });
    });

    /* ─── Đóng khi click backdrop / nút × ───────────────────── */
    backdrop.addEventListener('click', closePanel);
    closeBtn.addEventListener('click', closePanel);

    /* ─── Đóng bằng Escape ───────────────────────────────────── */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.style.display !== 'none') closePanel();
    });

    /* ─── Listener: mọi trigger [data-icon-picker] ──────────── */
    // Dùng event delegation tại document (capture) để bắt mọi trigger,
    // kể cả trigger bên trong Bootstrap modal.
    document.addEventListener('click', function (e) {
        // Nếu click đang nằm trong panel thì bỏ qua (tránh xung đột)
        if (panel.contains(e.target)) return;

        const trigger = e.target.closest('[data-icon-picker]');
        if (!trigger) return;

        // Ngăn Bootstrap xử lý data-bs-toggle (nếu còn sót)
        e.stopPropagation();
        e.preventDefault();

        const inputId = trigger.getAttribute('data-icon-picker');
        openPanel(trigger, inputId);
    }, true); // capture phase

    /* ─── API toàn cục (dùng khi cần gọi từ JS khác) ───────── */
    window.openIconPicker = openPanel;
})();
</script>
