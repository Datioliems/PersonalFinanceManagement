// ============================================================
// app.js — Đề 13 Finance App
// ============================================================

// Auto-dismiss flash alerts sau 4 giây
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert-dismissible').forEach(el => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            bsAlert.close();
        }, 4000);
    });
});

// Confirm trước khi xoá
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm(btn.dataset.confirm || 'Bạn có chắc muốn xoá?')) {
            e.preventDefault();
        }
    });
});
