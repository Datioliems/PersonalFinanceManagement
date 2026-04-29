<?php
// ============================================================
// AUTOLOAD — autoload.php
// ============================================================
// Thay Composer bằng spl_autoload_register() thủ công.
// Chuyển namespace App\X\Y → app/X/Y.php
// Không cần cài thêm gì — PHP thuần.
// ============================================================

spl_autoload_register(function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $relative = substr($class, 4);
    $file = BASE_PATH
        . DIRECTORY_SEPARATOR . 'app'
        . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative)
        . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
