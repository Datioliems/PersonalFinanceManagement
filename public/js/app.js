// app.js — FinanceApp TV1

// Auto-dismiss flash alerts sau 5 giây
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert-dismissible').forEach(el => {
        setTimeout(() => {
            try { bootstrap.Alert.getOrCreateInstance(el).close(); } catch {}
        }, 5000);
    });
});

// Confirm trước khi xoá bằng Bootstrap Modal
document.addEventListener('DOMContentLoaded', () => {
    const confirmModalEl = document.getElementById('confirmModal');
    if (confirmModalEl) {
        const confirmModal = new bootstrap.Modal(confirmModalEl);
        const confirmBtn = document.getElementById('confirmModalBtn');
        const confirmMessage = document.getElementById('confirmModalMessage');
        let formToSubmit = null;

        document.querySelectorAll('[data-confirm]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                formToSubmit = btn.closest('form');
                confirmMessage.textContent = btn.dataset.confirm || 'Bạn có chắc chắn muốn xoá?';
                confirmModal.show();
            });
        });

        confirmBtn.addEventListener('click', () => {
            if (formToSubmit) {
                // Thêm hiệu ứng loading cho nút Đồng ý
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang xoá...';
                confirmBtn.disabled = true;
                formToSubmit.submit();
            }
        });
    }
});
