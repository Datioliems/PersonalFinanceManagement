<?php
// ============================================================
// VIEW: Danh sách ví — app/Views/wallets/index.php
// TODO (Ngày 6): Implement UI
// Biến nhận: $overview['wallets'], $overview['net_worth'], $csrf
// ============================================================
$pageTitle = 'Quản lý ví';
require __DIR__ . '/../../Views/partials/layout.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Quản lý ví</h2>
    <a href="/wallets/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Thêm ví
    </a>
</div>

<!-- TODO: Card tổng tài sản -->
<!-- TODO: Grid các ví -->
<!-- TODO: Nút set default, nút ẩn ví -->
<div class="alert alert-info">TODO (Ngày 6): Implement wallet list view</div>

<?php require __DIR__ . '/../../Views/partials/footer.php'; ?>
