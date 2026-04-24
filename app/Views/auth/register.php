<?php
// app/Views/auth/register.php — TV1 Ngày 4
$pageTitle = 'Đăng ký tài khoản';
require __DIR__ . '/../partials/layout.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4">Đăng ký</h4>
                <form method="POST" action="/register" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" name="username" required>
                        <!-- TODO: hiển thị lỗi validation nếu có -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu (tối thiểu 8 ký tự)</label>
                        <input type="password" class="form-control" name="password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" name="password_confirm" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Tạo tài khoản</button>
                    </div>
                </form>
                <hr>
                <p class="text-center mb-0 small">
                    Đã có tài khoản? <a href="/login">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
