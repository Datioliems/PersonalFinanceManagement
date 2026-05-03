<?php
// ============================================================
// VIEW — app/Views/auth/register.php
// ============================================================
/** @var string $csrf */
/** @var array  $old         — ['username','email'] khôi phục sau lỗi */
/** @var array  $fieldErrors — ['username'=>[...],'email'=>[...],...] */
$old         = $old         ?? [];
$fieldErrors = $fieldErrors ?? [];
$pageTitle = 'Đăng ký tài khoản';
$extraCss  = BASE_URL . '/css/auth.css';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5">

        <!-- Header -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center
                        bg-dark rounded-circle mb-3"
                 style="width:56px;height:56px">
                <i class="bi bi-person-plus text-white fs-4"></i>
            </div>
            <h1 class="h4 fw-semibold mb-1">Tạo tài khoản mới</h1>
            <p class="text-muted small">Điền thông tin để bắt đầu quản lý tài chính</p>
        </div>

        <!-- Form card -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/register" novalidate id="registerForm">
                    <!-- CSRF -->
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label fw-medium">
                            Tên đăng nhập <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text"
                                   class="form-control <?= isset($fieldErrors['username']) ? 'is-invalid' : '' ?>"
                                   id="username"
                                   name="username"
                                   required
                                   autofocus
                                   minlength="3"
                                   maxlength="50"
                                   pattern="[a-zA-Z0-9_]+"
                                   placeholder="Tối thiểu 3 ký tự, chỉ a-z, 0-9, _"
                                   value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (!empty($fieldErrors['username'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars(implode(' ', $fieldErrors['username'])) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">Chỉ chữ cái, số và dấu gạch dưới (_)</div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">
                            Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email"
                                   class="form-control <?= isset($fieldErrors['email']) ? 'is-invalid' : '' ?>"
                                   id="email"
                                   name="email"
                                   required
                                   autocomplete="email"
                                   placeholder="example@gmail.com"
                                   value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (!empty($fieldErrors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars(implode(' ', $fieldErrors['email'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">
                            Mật khẩu <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password"
                                   class="form-control <?= isset($fieldErrors['password']) ? 'is-invalid' : '' ?>"
                                   id="password"
                                   name="password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   placeholder="Ít nhất 8 ký tự, 1 HOA, 1 số"
                                value="<?= htmlspecialchars($old['password'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   oninput="checkPasswordStrength(this.value)">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="togglePassword('password', this)"
                                    tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if (!empty($fieldErrors['password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars(implode(' ', $fieldErrors['password'])) ?></div>
                            <?php endif; ?>
                        </div>
                        <!-- Thanh độ mạnh mật khẩu -->
                        <div class="mt-2">
                            <div class="progress" style="height:4px">
                                <div id="strengthBar"
                                     class="progress-bar"
                                     style="width:0%;transition:width .3s"></div>
                            </div>
                            <small id="strengthText" class="text-muted"></small>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirm" class="form-label fw-medium">
                            Xác nhận mật khẩu <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-lock-fill text-muted"></i>
                            </span>
                            <input type="password"
                                   class="form-control <?= isset($fieldErrors['confirm']) ? 'is-invalid' : '' ?>"
                                   id="password_confirm"
                                   name="password_confirm"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                placeholder="Nhập lại mật khẩu"
                                value="<?= htmlspecialchars($old['password_confirm'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (!empty($fieldErrors['confirm'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars(implode(' ', $fieldErrors['confirm'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-dark btn-lg">
                            <i class="bi bi-person-check me-2"></i>Tạo tài khoản
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Link đăng nhập -->
        <p class="text-center mt-3 text-muted small">
            Đã có tài khoản?
            <a href="<?= BASE_URL ?>/login" class="text-dark fw-medium">Đăng nhập</a>
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

function checkPasswordStrength(pw) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const levels = [
        { w: '20%', cls: 'bg-danger',  label: 'Rất yếu'  },
        { w: '40%', cls: 'bg-danger',  label: 'Yếu'      },
        { w: '60%', cls: 'bg-warning', label: 'Trung bình'},
        { w: '80%', cls: 'bg-info',    label: 'Mạnh'     },
        { w: '100%',cls: 'bg-success', label: 'Rất mạnh' },
    ];
    const lvl = levels[Math.min(score, 4)];
    bar.style.width   = lvl.w;
    bar.className     = 'progress-bar ' + lvl.cls;
    text.textContent  = pw.length ? 'Độ mạnh: ' + lvl.label : '';
}

// Client-side validate confirm password
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const pw  = document.getElementById('password').value;
    const pw2 = document.getElementById('password_confirm').value;
    if (pw !== pw2) {
        e.preventDefault();
        const confirmInput = document.getElementById('password_confirm');
        confirmInput.classList.add('is-invalid');
        let fb = confirmInput.closest('.input-group').querySelector('.invalid-feedback');
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            confirmInput.closest('.input-group').appendChild(fb);
        }
        fb.textContent = 'Hai mật khẩu không khớp nhau!';
        confirmInput.focus();
    }
});
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>

