<?php
/** @var string $csrf */
$pageTitle = 'Quên mật khẩu';
$extraCss  = BASE_URL . '/css/auth.css';
require BASE_PATH . '/app/Views/partials/layout.php';
?>
<div class="row justify-content-center mt-4">
<div class="col-12 col-sm-9 col-md-6 col-lg-4">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle mb-3" style="width:56px;height:56px">
            <i class="bi bi-key text-warning fs-4"></i>
        </div>
        <h1 class="h4 fw-semibold mb-1">Quên mật khẩu?</h1>
        <p class="text-muted small">Nhập email để nhận link đặt lại mật khẩu</p>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/forgot-password">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                <div class="mb-4">
                    <label class="form-label fw-medium">Địa chỉ email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control" required placeholder="your@email.com" autofocus>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-warning fw-medium">
                        <i class="bi bi-send me-1"></i>Gửi link đặt lại mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
    <p class="text-center mt-3 text-muted small">
        <a href="<?= BASE_URL ?>/login" class="text-dark">← Quay lại đăng nhập</a>
    </p>
</div>
</div>
<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
