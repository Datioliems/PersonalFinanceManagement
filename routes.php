<?php
// ============================================================
// ROUTES — routes.php
// ============================================================
// Đăng ký tất cả route ở đây.
// Cú pháp: $router->get('/path', [Controller::class, 'method']);
//           $router->post('/path', [Controller::class, 'method']);
//
// Middleware 'auth' tự động chặn route nếu chưa đăng nhập.
// ============================================================

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ExpenseController;
use App\Controllers\IncomeController;
use App\Controllers\CategoryController;
use App\Controllers\BudgetController;
use App\Controllers\ReportController;

// --- Public routes (không cần đăng nhập) ---
$router->get('/login',            [AuthController::class, 'showLogin']);
$router->post('/login',           [AuthController::class, 'login']);
$router->get('/register',         [AuthController::class, 'showRegister']);
$router->post('/register',        [AuthController::class, 'register']);
$router->get('/logout',           [AuthController::class, 'logout']);

// --- Protected routes (cần đăng nhập — middleware auth) ---
$router->get('/',                 [DashboardController::class, 'index'],  ['auth']);
$router->get('/dashboard',        [DashboardController::class, 'index'],  ['auth']);

// Chi tiêu
$router->get('/expenses',         [ExpenseController::class, 'index'],    ['auth']);
$router->get('/expenses/create',  [ExpenseController::class, 'create'],   ['auth']);
$router->post('/expenses',        [ExpenseController::class, 'store'],    ['auth']);
$router->get('/expenses/{id}',    [ExpenseController::class, 'edit'],     ['auth']);
$router->post('/expenses/{id}',   [ExpenseController::class, 'update'],   ['auth']);
$router->post('/expenses/{id}/delete', [ExpenseController::class, 'destroy'], ['auth']);

// Thu nhập
$router->get('/incomes',          [IncomeController::class, 'index'],     ['auth']);
$router->get('/incomes/create',   [IncomeController::class, 'create'],    ['auth']);
$router->post('/incomes',         [IncomeController::class, 'store'],     ['auth']);
$router->get('/incomes/{id}',     [IncomeController::class, 'edit'],      ['auth']);
$router->post('/incomes/{id}',    [IncomeController::class, 'update'],    ['auth']);
$router->post('/incomes/{id}/delete', [IncomeController::class, 'destroy'], ['auth']);

// Danh mục
$router->get('/categories',       [CategoryController::class, 'index'],   ['auth']);
$router->post('/categories',      [CategoryController::class, 'store'],   ['auth']);
$router->post('/categories/{id}/delete', [CategoryController::class, 'destroy'], ['auth']);

// Ngân sách
$router->get('/budget',           [BudgetController::class, 'index'],     ['auth']);
$router->post('/budget',          [BudgetController::class, 'setLimit'],  ['auth']);

// Báo cáo
$router->get('/report',           [ReportController::class, 'index'],     ['auth']);
$router->get('/report/export',    [ReportController::class, 'export'],    ['auth']);

// ============================================================
// WALLET & TRANSFER ROUTES — Thêm vào Ngày 6
// ============================================================
use App\Controllers\WalletController;
use App\Controllers\TransferController;

$router->get('/wallets',                  [WalletController::class, 'index'],      ['auth']);
$router->get('/wallets/create',           [WalletController::class, 'create'],     ['auth']);
$router->post('/wallets',                 [WalletController::class, 'store'],      ['auth']);
$router->post('/wallets/{id}/default',    [WalletController::class, 'setDefault'], ['auth']);
$router->post('/wallets/{id}/deactivate', [WalletController::class, 'deactivate'], ['auth']);

$router->get('/transfers',        [TransferController::class, 'index'],  ['auth']);
$router->get('/transfers/create', [TransferController::class, 'create'], ['auth']);
$router->post('/transfers',       [TransferController::class, 'store'],  ['auth']);
