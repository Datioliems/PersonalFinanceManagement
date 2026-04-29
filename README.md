# Đề 13 — Quản lý Tài chính Cá nhân
### PHP OOP · MVC · Template Method · Repository · Dependency Injection

---

## Cấu trúc dự án

```
de13_finance/
│
├── autoload.php              ← PSR-4 autoload (không cần Composer)
├── routes.php                ← Tất cả routes của 5 thành viên
├── .env.example              ← Mẫu cấu hình môi trường
│
├── public/                   ← Document root (trỏ web server vào đây)
│   ├── index.php             ← ENTRY POINT duy nhất (Front Controller)
│   ├── .htaccess             ← Apache rewrite → index.php
│   └── js/app.js
│
├── config/
│   └── database.php          ← Đọc $_ENV, trả config PDO
│
├── database/migrations/
│   ├── 000_run_all.sql       ← Chạy file này để tạo toàn bộ DB
│   ├── 001_create_users_table.sql
│   ├── 002_create_categories_budgets.sql
│   └── 003_create_transactions.sql
│
└── app/
    ├── Core/
    │   ├── Database.php      ← Singleton PDO
    │   └── Router.php        ← Front Controller / Dispatcher
    │
    ├── Middleware/
    │   └── AuthMiddleware.php ← Chặn route nếu chưa đăng nhập
    │
    ├── Helpers/
    │   ├── CsrfTokenManager.php ← Generate + validate CSRF token
    │   ├── FlashMessage.php     ← Flash message qua redirect
    │   └── Paginator.php        ← Phân trang tái sử dụng
    │
    ├── Models/               ← [M] Business Objects
    │   ├── Transaction.php        ← abstract + Template Method (TV1)
    │   ├── IncomeTransaction.php  ← kế thừa Transaction (TV1/TV4)
    │   ├── ExpenseTransaction.php ← kế thừa Transaction (TV3)
    │   ├── Budget.php             ← isExceeded(), alert_threshold (TV2)
    │   └── Category.php           ← (TV2)
    │
    ├── Repositories/         ← [R] Database Access Layer (chỉ nơi viết SQL)
    │   ├── BaseRepository.php     ← abstract, $this->db PDO (TV3)
    │   ├── UserRepository.php     ← bảng users + login_logs (TV1)
    │   ├── CategoryRepository.php ← bảng categories (TV2)
    │   ├── BudgetRepository.php   ← bảng budgets + interface (TV2)
    │   └── TransactionRepository.php ← bảng transactions (TV3, TV4/TV5 dùng lại)
    │
    ├── Services/             ← [S] Business Logic Layer
    │   ├── AuthService.php        ← login, register, remember me (TV1/TV5)
    │   ├── BudgetService.php      ← checkAlert(), getBudgetSummary() (TV2)
    │   ├── ExpenseService.php     ← add, update, delete expense (TV3)
    │   ├── IncomeService.php      ← add, update, delete income (TV4)
    │   ├── FinanceReport.php      ← generateMonthly(), exportCsv() (TV4)
    │   └── ReportService.php      ← Chart.js data format (TV5)
    │
    ├── Controllers/          ← [C] HTTP Layer
    │   ├── BaseController.php     ← render(), redirect(), currentUserId()
    │   ├── AuthController.php     ← /login, /register, /logout (TV1)
    │   ├── CategoryController.php ← /categories CRUD (TV2)
    │   ├── BudgetController.php   ← /budget (TV2)
    │   ├── ExpenseController.php  ← /expenses CRUD (TV3)
    │   ├── IncomeController.php   ← /incomes CRUD (TV4)
    │   ├── DashboardController.php← / và /dashboard (TV5)
    │   └── ReportController.php   ← /report, /report/export (TV5)
    │
    └── Views/                ← [V] PHP Templates
        ├── partials/
        │   ├── layout.php    ← header, nav, Bootstrap (TV1)
        │   └── footer.php    ← JS, Chart.js lazy load
        ├── auth/             ← login.php, register.php (TV1)
        ├── categories/       ← index.php (TV2)
        ├── budget/           ← index.php với thanh tiến độ màu (TV2)
        ├── expenses/         ← index, create, edit (TV3)
        ├── incomes/          ← index, create, edit (TV4)
        ├── dashboard/        ← index.php với 2 Chart.js (TV5)
        └── report/           ← index.php + nút export CSV (TV5)
```

---

## Cài đặt & Chạy

```bash
# 1. Copy cấu hình
cp .env.example .env
# Mở .env, điền DB_USER và DB_PASS của máy

# 2. Tạo database
mysql -u root -p -e "CREATE DATABASE de13_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Chạy migration
cd database/migrations
mysql -u root -p de13_finance < 001_create_users_table.sql
mysql -u root -p de13_finance < 002_create_categories_budgets.sql
mysql -u root -p de13_finance < 003_create_transactions.sql
cd ../..

# 4. Start server
php -S localhost:8000 -t public
```

Mở trình duyệt: **http://localhost:8000/register** → đăng ký → đăng nhập → bắt đầu dùng.

---

## Luồng request

```
Browser → .htaccess → public/index.php
    │
    ├─ load autoload.php (PSR-4)
    ├─ load .env
    ├─ session_start()
    ├─ tryRememberLogin() (nếu có cookie)
    └─ Router::dispatch(method, uri)
           │
           ├─ match route
           ├─ run Middleware (AuthMiddleware nếu có)
           └─ new Controller() → action()
                  │
                  ├─ gọi Service (business logic)
                  │      └─ gọi Repository (SQL)
                  │             └─ Database::getInstance() (PDO)
                  └─ render(view, data) → HTML
```

---

## Design Patterns

| Pattern | File | Mô tả |
|---------|------|-------|
| **Template Method** | `Models/Transaction.php` | `final process()` = validate→save→notify |
| **Repository** | `Repositories/*` | Tách SQL khỏi business logic |
| **Singleton** | `Core/Database.php` | 1 PDO duy nhất/request |
| **Front Controller** | `public/index.php` | Mọi request qua 1 điểm |
| **Dependency Injection** | Tất cả Service/Controller | Inject qua constructor |
| **PRG** | Tất cả Controller POST | Redirect sau POST tránh resubmit |
| **Interface** | `BudgetRepositoryInterface` | Contract tách khỏi implementation |

---

## Phân công thành viên

| TV | Tên | Luồng | File chính |
|----|-----|-------|-----------|
| TV1 | Đạt | Auth | `AuthController`, `AuthService`, `UserRepository`, `Transaction` (abstract) |
| TV2 | Hoài | Budget | `BudgetController`, `CategoryController`, `BudgetService`, `Budget`, `Category` |
| TV3 | Quang | Chi tiêu | `ExpenseController`, `ExpenseService`, `ExpenseTransaction`, `TransactionRepository` |
| TV4 | Hiếu | Thu nhập | `IncomeController`, `IncomeService`, `IncomeTransaction`, `FinanceReport` |
| TV5 | Hằng | Báo cáo | `DashboardController`, `ReportController`, `ReportService` |

---

## Câu hỏi hay bị hỏi khi demo

**1. `process()` là `final` để làm gì?**
Đảm bảo thứ tự validate→save→notify không thể bị subclass phá vỡ.

**2. Tại sao dùng Repository Pattern thay vì viết SQL thẳng trong Controller?**
Tách biệt SQL khỏi logic nghiệp vụ. Nếu đổi DB, chỉ sửa Repository — Controller và Service không thay đổi.

**3. Dependency Injection hoạt động thế nào ở đây?**
`new BudgetService(new BudgetRepository(), new TransactionRepository())` — inject qua constructor. Service không tự `new` Repository bên trong — dễ thay và dễ test.

**4. CSRF token bảo vệ gì?**
Chống Cross-Site Request Forgery — kẻ tấn công không thể giả mạo request vì không biết token trong session.

**5. Tại sao mọi query đều có `WHERE user_id = ?`?**
Multi-user app — thiếu điều kiện này thì user A xem/xoá được dữ liệu của user B.
