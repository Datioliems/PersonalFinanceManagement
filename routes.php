<?php
// ============================================================
// ROUTES — routes.php (Tổng hợp tất cả 5 thành viên)
// ============================================================
// TV1 — Auth:        /login, /register, /logout
// TV2 — Budget:      /categories, /budget
// TV3 — Expense:     /expenses
// TV4 — Income:      /incomes
// TV5 — Dashboard:   /, /dashboard, /report
// ============================================================

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CategoryController;
use App\Controllers\BudgetController;
use App\Controllers\TransactionController;
use App\Controllers\ReportController;

// ── Public (không cần đăng nhập) ─────────────────────────────
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->get('/logout',   [AuthController::class, 'logout']);

// ── Protected (cần đăng nhập — middleware 'auth') ─────────────

// TV5 — Dashboard
$router->get('/',          [DashboardController::class, 'index'], ['auth']);
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// TV3 & TV4 (Hợp nhất) — Giao dịch
$router->get('/transactions',               [TransactionController::class, 'index'],   ['auth']);
$router->get('/transactions/create',        [TransactionController::class, 'create'],  ['auth']);
$router->post('/transactions',              [TransactionController::class, 'store'],   ['auth']);
$router->get('/transactions/{id}/edit',     [TransactionController::class, 'edit'],    ['auth']);
$router->post('/transactions/{id}',         [TransactionController::class, 'update'],  ['auth']);
$router->post('/transactions/{id}/delete',  [TransactionController::class, 'destroy'], ['auth']);

// TV2 — Danh mục
$router->get('/categories',              [CategoryController::class, 'index'],   ['auth']);
$router->post('/categories',             [CategoryController::class, 'store'],   ['auth']);
$router->get('/categories/{id}/edit',    [CategoryController::class, 'edit'],    ['auth']);
$router->post('/categories/{id}',        [CategoryController::class, 'update'],  ['auth']);
$router->post('/categories/{id}/delete', [CategoryController::class, 'destroy'], ['auth']);

// TV2 — Ngân sách
$router->get('/budget',               [BudgetController::class, 'index'],    ['auth']);
$router->post('/budget',              [BudgetController::class, 'setLimit'], ['auth']);
$router->post('/budget/{id}/delete',  [BudgetController::class, 'destroy'],  ['auth']);

// TV5 — Báo cáo
$router->get('/report',         [ReportController::class, 'index'],  ['auth']);
$router->get('/report/export',  [ReportController::class, 'export'], ['auth']);

// ── Auth nâng cao ──────────────────────────────────────────
$router->get('/verify-email',     [AuthController::class, 'verifyEmail']);
$router->get('/forgot-password',  [AuthController::class, 'showForgot']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/reset-password',   [AuthController::class, 'showReset']);
$router->post('/reset-password',  [AuthController::class, 'resetPassword']);
