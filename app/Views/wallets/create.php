<?php
// app/Views/wallets/create.php — TODO Ngày 6
$pageTitle = 'Thêm ví mới';
require __DIR__ . '/../../Views/partials/layout.php';
?>
<h2 class="mb-4">Thêm ví mới</h2>
<div class="row justify-content-center"><div class="col-md-6">
<div class="card"><div class="card-body">
<form method="POST" action="/wallets">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <div class="mb-3">
        <label class="form-label">Tên ví <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" required
               placeholder="VD: Tiền mặt, VCB, MoMo">
    </div>
    <div class="mb-3">
        <label class="form-label">Loại ví</label>
        <select class="form-select" name="type">
            <option value="cash">Tiền mặt</option>
            <option value="bank">Ngân hàng</option>
            <option value="e_wallet">Ví điện tử</option>
            <option value="credit">Thẻ tín dụng</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Số dư ban đầu (VND)</label>
        <input type="number" class="form-control" name="balance"
               value="0" min="0" step="1000">
    </div>
    <div class="row">
        <div class="col-6 mb-3">
            <label class="form-label">Màu ví</label>
            <input type="color" class="form-control form-control-color w-100"
                   name="color" value="#7F77DD">
        </div>
        <div class="col-6 mb-3">
            <label class="form-label">Icon (Bootstrap)</label>
            <input type="text" class="form-control" name="icon"
                   placeholder="bi-wallet2">
        </div>
    </div>
    <div class="d-grid">
        <button type="submit" class="btn btn-primary">Tạo ví</button>
    </div>
</form>
</div></div>
</div></div>
<?php require __DIR__ . '/../../Views/partials/footer.php'; ?>
