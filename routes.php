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
use App\Controllers\ExpenseController;
use App\Controllers\IncomeController;
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

// TV3 — Chi tiêu
$router->get('/expenses',               [ExpenseController::class, 'index'],   ['auth']);
$router->get('/expenses/create',        [ExpenseController::class, 'create'],  ['auth']);
$router->post('/expenses',              [ExpenseController::class, 'store'],   ['auth']);
$router->get('/expenses/{id}/edit',     [ExpenseController::class, 'edit'],    ['auth']);
$router->post('/expenses/{id}',         [ExpenseController::class, 'update'],  ['auth']);
$router->post('/expenses/{id}/delete',  [ExpenseController::class, 'destroy'], ['auth']);

// TV4 — Thu nhập
$router->get('/incomes',               [IncomeController::class, 'index'],   ['auth']);
$router->get('/incomes/create',        [IncomeController::class, 'create'],  ['auth']);
$router->post('/incomes',              [IncomeController::class, 'store'],   ['auth']);
$router->get('/incomes/{id}/edit',     [IncomeController::class, 'edit'],    ['auth']);
$router->post('/incomes/{id}',         [IncomeController::class, 'update'],  ['auth']);
$router->post('/incomes/{id}/delete',  [IncomeController::class, 'destroy'], ['auth']);

// TV2 — Danh mục
$router->get('/categories',              [CategoryController::class, 'index'],   ['auth']);
$router->post('/categories',             [CategoryController::class, 'store'],   ['auth']);
$router->post('/categories/{id}/delete', [CategoryController::class, 'destroy'], ['auth']);

// TV2 — Ngân sách
$router->get('/budget',               [BudgetController::class, 'index'],    ['auth']);
$router->post('/budget',              [BudgetController::class, 'setLimit'], ['auth']);
$router->post('/budget/{id}/delete',  [BudgetController::class, 'destroy'],  ['auth']);

// TV5 — Báo cáo
$router->get('/report',         [ReportController::class, 'index'],  ['auth']);
$router->get('/report/export',  [ReportController::class, 'export'], ['auth']);
