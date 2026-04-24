<?php
// ============================================================
// AUTOLOAD — autoload.php
// ============================================================
// Thay thế hoàn toàn Composer autoload.
// Dùng spl_autoload_register() để tự động require file
// theo chuẩn PSR-4:
//
//   Namespace: App\Controllers\AuthController
//   → File:    app/Controllers/AuthController.php
//
// Cách hoạt động:
//   1. PHP gặp class chưa load (VD: new AuthController())
//   2. Gọi hàm autoload bên dưới
//   3. Hàm chuyển namespace → đường dẫn file
//   4. require_once file đó
//
// KHÔNG cần Composer, KHÔNG cần framework.
// ============================================================

spl_autoload_register(function (string $class): void {
    // Chỉ xử lý namespace bắt đầu bằng "App\"
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        // Không phải App namespace → bỏ qua
        return;
    }

    // Bỏ prefix "App\" → còn lại "Controllers\AuthController"
    $relative = substr($class, strlen($prefix));

    // Chuyển \ thành / → "Controllers/AuthController"
    $relative = str_replace('\\', DIRECTORY_SEPARATOR, $relative);

    // Ghép đường dẫn đầy đủ → ".../app/Controllers/AuthController.php"
    $file = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relative . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
    // Nếu không tìm thấy: PHP sẽ báo lỗi "Class not found" tự nhiên
});
