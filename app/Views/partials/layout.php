<?php
// ============================================================
// LAYOUT — app/Views/partials/layout.php
// ============================================================
// Dùng bởi mọi View: require 'partials/layout.php'
// Hoặc gọi startLayout() / endLayout() nếu muốn block system
//
// Biến cần truyền vào: $pageTitle (optional)
// ============================================================
$pageTitle = $pageTitle ?? 'Quản lý Tài chính';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> — FinanceApp</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/dashboard">
            <i class="bi bi-wallet2"></i> FinanceApp
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPath === '/dashboard' ? 'active' : '' ?>"
                       href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/expenses') ? 'active' : '' ?>"
                       href="/expenses"><i class="bi bi-arrow-up-circle"></i> Chi tiêu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/incomes') ? 'active' : '' ?>"
                       href="/incomes"><i class="bi bi-arrow-down-circle"></i> Thu nhập</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/budget') ? 'active' : '' ?>"
                       href="/budget"><i class="bi bi-piggy-bank"></i> Ngân sách</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/categories') ? 'active' : '' ?>"
                       href="/categories"><i class="bi bi-tags"></i> Danh mục</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/report') ? 'active' : '' ?>"
                       href="/report"><i class="bi bi-bar-chart"></i> Báo cáo</a>
                </li>
            </ul>
            <span class="navbar-text me-3">
                <i class="bi bi-person-circle"></i>
                <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
            </span>
            <a href="/logout" class="btn btn-outline-light btn-sm">Đăng xuất</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4">
    <!-- Flash messages -->
    <?= \App\Helpers\FlashMessage::renderAll() ?>

    <!-- TODO: yield $content từ từng View -->
