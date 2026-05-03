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
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Navbar (global) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/navbar.css">
    <!-- CSS riêng theo trang (set $extraCss trước khi require layout) -->
    <?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($extraCss, ENT_QUOTES) ?>">
    <?php endif; ?>

    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; background: #f8fafc; }
        .navbar-brand { font-weight: 600; letter-spacing: -.3px; }
        .nav-link.active { font-weight: 500; }
        main { min-height: calc(100vh - 80px); }
        
        /* Modern Cards */
        .card { 
            border: 1px solid #f1f5f9; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        }

        /* Dropdown Effects */
        .dropdown-menu {
            border: 1px solid #f1f5f9;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-radius: 12px;
            animation: dropdownFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 8px;
        }
        .dropdown-item {
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }
        .dropdown-item:hover {
            background-color: #f8fafc;
            transform: translateX(4px);
            color: #0f172a;
        }
        @keyframes dropdownFade {
            from { opacity: 0; transform: translateY(-10px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Table Effects */
        .table {
            border-collapse: separate;
            border-spacing: 0 6px;
            background: transparent;
        }
        .table > thead {
            background: transparent !important;
        }
        .table > thead > tr > th {
            border-bottom: none;
            background: transparent !important;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding-bottom: 4px;
        }
        .table > tbody > tr {
            background: #fff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .table > tbody > tr > td {
            border-top: none;
            border-bottom: none;
            vertical-align: middle;
            padding: 12px 16px;
        }
        .table > tbody > tr > td:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        .table > tbody > tr > td:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .table-hover > tbody > tr:hover {
            transform: translateY(-2px) scale(1.002);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            background-color: #fff !important; /* Override bootstrap */
            z-index: 1;
            position: relative;
        }

        /* Buttons & Forms */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            border-color: #93c5fd;
        }
        .btn {
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn:active {
            transform: translateY(1px);
        }

        /* Màu cảnh báo ngân sách */
        .budget-safe    { color: #16a34a; }
        .budget-warning { color: #d97706; }
        .budget-danger  { color: #dc2626; }

        /* Page Headers (dùng cho Báo cáo, Danh mục, Ngân sách) */
        .page-header-shared {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            gap: 1rem;
        }
        .page-title {
            font-size: 1.45rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin-bottom: .2rem !important;
        }
        .page-subtitle {
            font-size: .83rem !important;
            color: #94a3b8 !important;
            margin: 0 !important;
        }

        /* Popup giao dịch — style chung */
        .tx-th {
            font-size: .75rem !important;
            font-weight: 700 !important;
            letter-spacing: .05em !important;
            text-transform: uppercase !important;
            color: #64748b !important;
            padding: .65rem .75rem !important;
            white-space: nowrap;
        }
        .tx-td {
            vertical-align: middle !important;
            padding: .65rem .75rem !important;
            font-size: .85rem !important;
        }
        #txModalBody .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        #txModalBody .table thead th {
            border-bottom: 2px solid #f1f5f9;
            background: #f8fafc;
        }
    </style>
</head>
<body>

<?php
$cp = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!function_exists('navActive')) {
    function navActive(string $prefix, string $current): string {
        return str_starts_with($current, $prefix) ? 'active' : '';
    }
}
$isLoggedIn = isset($_SESSION['user_id']);
$username   = htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES);
$avatarChar = mb_strtoupper(mb_substr($username, 0, 1) ?: 'U');
?>

<!-- ════════════════════════════════════════════════════════
     NAVBAR
════════════════════════════════════════════════════════ -->
<nav class="app-navbar" role="navigation" aria-label="Main navigation">
    <div class="container">

        <!-- Brand -->
        <a class="nav-brand" href="<?= BASE_URL ?>/dashboard" aria-label="FinanceApp - Trang chủ">
            <span class="nav-brand-icon"><i class="bi bi-wallet2"></i></span>
            <span class="nav-brand-text">
                FinanceApp
                <span class="nav-brand-sub">Quản lý tài chính</span>
            </span>
        </a>

        <?php if ($isLoggedIn): ?>

        <!-- Desktop nav links -->
        <ul class="nav-menu" id="desktopNav">
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

        <!-- Desktop user dropdown -->
        <!-- <div class="nav-user dropdown">
            <a class="nav-user-btn dropdown-toggle"
               href="#" id="userDropdown"
               data-bs-toggle="dropdown"
               aria-expanded="false">
                <span class="nav-user-avatar"><?= $avatarChar ?></span>
                <span class="d-none d-xl-inline"><?= $username ?></span>
                <i class="bi bi-chevron-down nav-user-chevron"></i>
            </a>
            <ul class="dropdown-menu nav-user-dropdown dropdown-menu-end"
                aria-labelledby="userDropdown">
                <li>
                    <div class="nav-user-info">
                        <span class="u-name"><?= $username ?></span>
                        <span class="u-role">Thành viên</span>
                    </div>
                </li>
                <li>
                    <a class="nav-dropdown-item danger"
                       href="<?= BASE_URL ?>/logout">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </a>
                </li>
            </ul>
        </div> -->

        <!-- Hamburger (mobile) -->
        <button class="nav-toggler" id="navToggler"
                aria-label="Toggle navigation" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <?php else: ?>

        <!-- Auth links (chưa đăng nhập) -->
        <div class="nav-auth-links ms-auto">
            <a class="btn-nav-login" href="<?= BASE_URL ?>/login">Đăng nhập</a>
            <a class="btn-nav-register" href="<?= BASE_URL ?>/register">Đăng ký</a>
        </div>

        <?php endif; ?>
    </div><!-- /.container -->
</nav>

<?php if ($isLoggedIn): ?>
<!-- ── Mobile drawer ────────────────────────────────────── -->
<div class="nav-mobile-panel" id="mobilePanel" aria-hidden="true">

    <!-- User info
    <div class="mobile-user-section">
        <span class="mobile-user-avatar"><?= $avatarChar ?></span>
        <div>
            <span class="mobile-user-name"><?= $username ?></span>
            <span class="mobile-user-sub">Tài khoản cá nhân</span>
        </div>
    </div> -->

    <!-- Nav items -->
    <ul class="mobile-nav-list">
        <li><a class="nav-link <?= $cp === '/dashboard' ? 'active' : '' ?>"
               href="<?= BASE_URL ?>/dashboard">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a></li>
        <li><a class="nav-link <?= navActive('/transactions', $cp) ?>"
               href="<?= BASE_URL ?>/transactions">
            <i class="bi bi-receipt"></i> Giao dịch
        </a></li>
        <li><a class="nav-link <?= navActive('/budget', $cp) ?>"
               href="<?= BASE_URL ?>/budget">
            <i class="bi bi-piggy-bank"></i> Ngân sách
        </a></li>
        <li><a class="nav-link <?= navActive('/categories', $cp) ?>"
               href="<?= BASE_URL ?>/categories">
            <i class="bi bi-tags"></i> Danh mục
        </a></li>
        <li><a class="nav-link <?= navActive('/report', $cp) ?>"
               href="<?= BASE_URL ?>/report">
            <i class="bi bi-bar-chart-line"></i> Báo cáo
        </a></li>
    </ul>

    <div class="mobile-divider"></div>

    <!-- <a class="mobile-logout" href="<?= BASE_URL ?>/logout">
        <i class="bi bi-box-arrow-right"></i> Đăng xuất
    </a> -->
</div>
<!-- Backdrop mờ -->
<div class="nav-backdrop" id="navBackdrop"></div>
<?php endif; ?>

<script>
/* Navbar toggle (mobile) */
(function () {
    var toggler  = document.getElementById('navToggler');
    var panel    = document.getElementById('mobilePanel');
    var backdrop = document.getElementById('navBackdrop');
    if (!toggler || !panel) return;

    function openMenu() {
        toggler.classList.add('open');
        panel.classList.add('open');
        panel.setAttribute('aria-hidden', 'false');
        if (backdrop) backdrop.style.display = 'block';
        document.body.style.overflow = 'hidden';
        toggler.setAttribute('aria-expanded', 'true');
    }
    function closeMenu() {
        toggler.classList.remove('open');
        panel.classList.remove('open');
        panel.setAttribute('aria-hidden', 'true');
        if (backdrop) backdrop.style.display = 'none';
        document.body.style.overflow = '';
        toggler.setAttribute('aria-expanded', 'false');
    }
    toggler.addEventListener('click', function () {
        panel.classList.contains('open') ? closeMenu() : openMenu();
    });
    if (backdrop) backdrop.addEventListener('click', closeMenu);
    panel.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', closeMenu);
    });
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeMenu();
    });

    /* User dropdown chevron sync */
    var userBtn = document.getElementById('userDropdown');
    if (userBtn) {
        userBtn.addEventListener('show.bs.dropdown',   function () { this.classList.add('show');    });
        userBtn.addEventListener('hidden.bs.dropdown', function () { this.classList.remove('show'); });
    }
})();
</script>

<style>
/* Backdrop overlay cho mobile drawer */
.nav-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .4);
    z-index: 1028;
}
</style>

<!-- ── Main content ─────────────────────────────────────── -->
<main class="container py-4">
    <!-- Flash messages -->
    <?= \App\Helpers\FlashMessage::renderAll() ?>

