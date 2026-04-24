<?php
// ============================================================
// VIEW: Đăng nhập — app/Views/auth/login.php
// ============================================================
// Biến nhận từ AuthController::showLogin():
//   $csrf — CSRF token
// TODO (TV1 — Ngày 4): Hoàn thiện form UI
// ============================================================
$pageTitle = 'Đăng nhập';
require __DIR__ . '/../partials/layout.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4">
                    <i class="bi bi-wallet2 text-primary"></i> Đăng nhập
                </h4>

                <form method="POST" action="/login" novalidate>
                    <!-- CSRF token — bắt buộc trong mọi form POST -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username"
                               required autofocus autocomplete="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password"
                               required autocomplete="current-password">
                    </div>

                    <!-- TODO (TV1 Ngày 6): Thêm checkbox Remember Me -->
                    <!--
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                        <label class="form-check-label" for="remember">Nhớ mật khẩu 30 ngày</label>
                    </div>
                    -->

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Đăng nhập</button>
                    </div>
                </form>

                <hr>
                <p class="text-center mb-0 small">
                    Chưa có tài khoản?
                    <a href="/register">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
