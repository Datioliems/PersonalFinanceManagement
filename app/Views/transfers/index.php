<?php
// app/Views/transfers/index.php — TODO Ngày 7
$pageTitle = 'Lịch sử chuyển tiền';
require __DIR__ . '/../../Views/partials/layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Lịch sử chuyển tiền</h2>
    <a href="/transfers/create" class="btn btn-warning btn-sm">
        <i class="bi bi-arrow-left-right"></i> Chuyển tiền
    </a>
</div>
<!-- TODO (Ngày 7): Bảng lịch sử, mỗi row hiện: từ ví → đến ví, số tiền, ngày, ghi chú -->
<div class="alert alert-info">TODO (Ngày 7): Implement transfer history view</div>
<?php require __DIR__ . '/../../Views/partials/footer.php'; ?>
