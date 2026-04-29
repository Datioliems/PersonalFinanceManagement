<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'FinanceApp') ?> — Quản lý Tài chính</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; background: #f5f5f4; }
        .navbar-brand { font-weight: 600; letter-spacing: -.3px; }
        .nav-link.active { font-weight: 500; }
        main { min-height: calc(100vh - 130px); }
        .card { border: 1px solid #e7e5e4; border-radius: 12px; }

        /* Màu cảnh báo ngân sách */
        .budget-safe    { color: #16a34a; }
        .budget-warning { color: #d97706; }
        .budget-danger  { color: #dc2626; }
    </style>
</head>
<body>

<!-- ── Navbar ──────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard">
            <i class="bi bi-wallet2 me-1"></i> FinanceApp
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <?php
            $cp = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            function navActive(string $prefix, string $current): string {
                return str_starts_with($current, $prefix) ? 'active' : '';
            }
            ?>

            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Menu khi đã đăng nhập -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $cp === '/dashboard' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/dashboard">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('/transactions', $cp) ?>"
                       href="<?= BASE_URL ?>/transactions">
                        <i class="bi bi-receipt"></i> Giao dịch
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('/budget', $cp) ?>"
                       href="<?= BASE_URL ?>/budget">
                        <i class="bi bi-piggy-bank"></i> Ngân sách
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('/categories', $cp) ?>"
                       href="<?= BASE_URL ?>/categories">
                        <i class="bi bi-tags"></i> Danh mục
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('/report', $cp) ?>"
                       href="<?= BASE_URL ?>/report">
                        <i class="bi bi-bar-chart-line"></i> Báo cáo
                    </a>
                </li>
            </ul>

            <!-- User dropdown -->
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#"
                       data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                        </a></li>
                    </ul>
                </li>
            </div>

            <?php else: ?>
            <!-- Menu khi chưa đăng nhập -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $cp === '/login' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/login">Đăng nhập</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $cp === '/register' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/register">Đăng ký</a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ── Main content ─────────────────────────────────────── -->
<main class="container py-4">
    <!-- Flash messages -->
    <?= \App\Helpers\FlashMessage::renderAll() ?>

