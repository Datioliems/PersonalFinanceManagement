<?php
/** 
 * @var string $csrf 
 * @var string $token 
 */
$pageTitle = 'Đặt lại mật khẩu';
$extraCss  = BASE_URL . '/css/auth.css';
require BASE_PATH . '/app/Views/partials/layout.php';
?>
<div class="row justify-content-center mt-4">
<div class="col-12 col-sm-9 col-md-6 col-lg-4">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width:56px;height:56px">
            <i class="bi bi-shield-lock text-danger fs-4"></i>
        </div>
        <h1 class="h4 fw-semibold mb-1">Đặt lại mật khẩu</h1>
        <p class="text-muted small">Link có hiệu lực trong 30 phút</p>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/reset-password" id="resetForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                <input type="hidden" name="token"      value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
                <div class="mb-3">
                    <label class="form-label fw-medium">Mật khẩu mới <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" id="pw1" name="password" class="form-control"
                               required minlength="8" placeholder="Ít nhất 8 ký tự, 1 hoa, 1 số"
                               oninput="checkStr(this.value)">
                        <button type="button" class="btn btn-outline-secondary" onclick="tog('pw1',this)" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="progress mt-2" style="height:4px"><div id="sBar" class="progress-bar" style="width:0%"></div></div>
                    <small id="sTxt" class="text-muted"></small>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock-fill text-muted"></i></span>
                        <input type="password" id="pw2" name="password_confirm" class="form-control" required minlength="8" placeholder="Nhập lại">
                        <button type="button" class="btn btn-outline-secondary" onclick="tog('pw2',this)" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger fw-medium">
                        <i class="bi bi-check-lg me-1"></i>Cập nhật mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<script>
function tog(id,btn){const f=document.getElementById(id),i=btn.querySelector('i');f.type=f.type==='password'?'text':'password';i.className=f.type==='password'?'bi bi-eye':'bi bi-eye-slash';}
function checkStr(pw){let s=0;if(pw.length>=8)s++;if(pw.length>=12)s++;if(/[A-Z]/.test(pw))s++;if(/[0-9]/.test(pw))s++;if(/[^A-Za-z0-9]/.test(pw))s++;const l=[{w:'20%',c:'bg-danger',t:'Rất yếu'},{w:'40%',c:'bg-danger',t:'Yếu'},{w:'60%',c:'bg-warning',t:'Trung bình'},{w:'80%',c:'bg-info',t:'Mạnh'},{w:'100%',c:'bg-success',t:'Rất mạnh'}][Math.min(s,4)];document.getElementById('sBar').style.width=l.w;document.getElementById('sBar').className='progress-bar '+l.c;document.getElementById('sTxt').textContent=pw?'Độ mạnh: '+l.t:'';}
document.getElementById('resetForm').addEventListener('submit',e=>{if(document.getElementById('pw1').value!==document.getElementById('pw2').value){e.preventDefault();alert('Hai mật khẩu không khớp!');}});
</script>
<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
