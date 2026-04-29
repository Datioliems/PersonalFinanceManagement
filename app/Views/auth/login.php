<?php
// ============================================================
// VIEW — app/Views/auth/login.php
// ============================================================
// Biến nhận từ AuthController::showLogin():
//   $csrf — CSRF token (string)
// ============================================================
$pageTitle = 'Đăng nhập';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-12 col-sm-10 col-md-6 col-lg-5 col-xl-4">

        <!-- Logo / tiêu đề -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center
                        bg-dark rounded-circle mb-3"
                 style="width:56px;height:56px">
                <i class="bi bi-wallet2 text-white fs-4"></i>
            </div>
            <h1 class="h4 fw-semibold mb-1">Chào mừng trở lại</h1>
            <p class="text-muted small">Đăng nhập vào tài khoản của bạn</p>
        </div>

        <!-- Form card -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/login" novalidate>
                    <!-- CSRF token — bắt buộc trong MỌI form POST -->
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label fw-medium">
                            Tên đăng nhập
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   id="username"
                                   name="username"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   placeholder="Nhập tên đăng nhập"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label fw-medium">
                                Mật khẩu
                            </label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Nhập mật khẩu">
                            <!-- Toggle hiện/ẩn mật khẩu -->
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="togglePassword('password', this)"
                                    tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4 form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="remember_me"
                               name="remember_me"
                               value="1">
                        <label class="form-check-label text-muted small" for="remember_me">
                            Nhớ mật khẩu trong 30 ngày
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-dark btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Link đăng ký -->
        <p class="text-center mt-3 text-muted small">
            Chưa có tài khoản?
            <a href="<?= BASE_URL ?>/register" class="text-dark fw-medium">Đăng ký ngay</a>
        </p>
        <p class="text-center mt-1 text-muted small">
            <a href="<?= BASE_URL ?>/forgot-password" class="text-muted">Quên mật khẩu?</a>
        </p>

    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
