<?php
// app/Views/transfers/create.php — TODO Ngày 6-7
// Biến nhận: $wallets (array), $csrf
$pageTitle = 'Chuyển tiền giữa ví';
require __DIR__ . '/../../Views/partials/layout.php';
?>
<h2 class="mb-4">Chuyển tiền giữa ví</h2>
<div class="row justify-content-center"><div class="col-md-6">
<div class="card"><div class="card-body">
<form method="POST" action="/transfers">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <div class="mb-3">
        <label class="form-label">Từ ví <span class="text-danger">*</span></label>
        <select class="form-select" name="from_wallet_id" required>
            <option value="">-- Chọn ví nguồn --</option>
            <?php foreach ($wallets as $w): ?>
            <option value="<?= $w['id'] ?>">
                <?= htmlspecialchars($w['name']) ?>
                (<?= number_format($w['balance'], 0, ',', '.') ?> VND)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Đến ví <span class="text-danger">*</span></label>
        <select class="form-select" name="to_wallet_id" required>
            <option value="">-- Chọn ví đích --</option>
            <?php foreach ($wallets as $w): ?>
            <option value="<?= $w['id'] ?>">
                <?= htmlspecialchars($w['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Số tiền (VND) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="amount"
               required min="1000" step="1000" placeholder="500000">
    </div>
    <div class="mb-3">
        <label class="form-label">Ngày chuyển</label>
        <input type="date" class="form-control" name="transfer_date"
               value="<?= date('Y-m-d') ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Ghi chú</label>
        <input type="text" class="form-control" name="note"
               placeholder="VD: Nộp tiền thuê nhà">
    </div>
    <div class="d-grid">
        <button type="submit" class="btn btn-warning">Chuyển tiền</button>
    </div>
</form>
</div></div>
</div></div>
<?php require __DIR__ . '/../../Views/partials/footer.php'; ?>
