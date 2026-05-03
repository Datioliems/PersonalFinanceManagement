// app.js — FinanceApp TV1

// Auto-dismiss flash alerts sau 5 giây
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert-dismissible').forEach(el => {
        setTimeout(() => {
            try { bootstrap.Alert.getOrCreateInstance(el).close(); } catch {}
        }, 5000);
    });
});

// Confirm trước khi xoá
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm(btn.dataset.confirm || 'Bạn có chắc chắn muốn xoá?')) {
            e.preventDefault();
        }
    });
});
