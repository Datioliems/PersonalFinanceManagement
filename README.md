# Đề 13 — Quản lý Tài chính Cá nhân
## OOP · MVC · Template Method · Repository · Dependency Injection

---

## Cấu trúc thư mục

```
de13_project/
├── app/
│   ├── Controllers/          ← Nhận request, gọi Service, trả View
│   │   ├── BaseController.php
│   │   ├── AuthController.php       (TV1)
│   │   ├── DashboardController.php  (TV5)
│   │   ├── ExpenseController.php    (TV3)
│   │   ├── IncomeController.php     (TV4)
│   │   ├── BudgetController.php     (TV2)
│   │   ├── CategoryController.php   (TV2)
│   │   └── ReportController.php     (TV5)
│   │
│   ├── Models/               ← Business objects & Template Method
│   │   ├── Transaction.php          (TV1) ← abstract class — ĐỌC TRƯỚC
│   │   ├── IncomeTransaction.php    (TV1)
│   │   ├── ExpenseTransaction.php   (TV3)
│   │   └── Budget.php               (TV2)
│   │
│   ├── Services/             ← Business logic layer
│   │   ├── AuthService.php          (TV5)
│   │   ├── BudgetService.php        (TV2)
│   │   └── FinanceReport.php        (TV4+TV5)
│   │
│   ├── Repositories/         ← Database access layer
│   │   ├── BaseRepository.php       (TV3)
│   │   ├── UserRepository.php       (TV1)
│   │   ├── TransactionRepository.php (TV3) ← TV4 và TV5 dùng lại
│   │   ├── CategoryRepository.php   (TV2)
│   │   └── BudgetRepository.php     (TV2)
│   │
│   ├── Middleware/
│   │   └── AuthMiddleware.php       (TV1)
│   │
│   ├── Helpers/
│   │   ├── Paginator.php            (TV3)
│   │   ├── CsrfTokenManager.php     (TV5)
│   │   └── FlashMessage.php         (dùng chung)
│   │
│   ├── Core/                 ← Framework nhỏ tự viết
│   │   ├── Router.php
│   │   └── Database.php             (TV3)
│   │
│   └── Views/                ← Giao diện PHP
│       ├── partials/
│       │   ├── layout.php    ← Header + Nav (TV1)
│       │   └── footer.php
│       ├── auth/             (TV1)
│       ├── expenses/         (TV3)
│       ├── incomes/          (TV4)
│       ├── budget/           (TV2)
│       ├── categories/       (TV2)
│       └── report/           (TV5)
│
├── config/
│   └── database.php
│
├── database/
│   └── migrations/
│       ├── 000_run_all.sql   ← Chạy file này để tạo toàn bộ DB
│       ├── 001_create_users_table.sql
│       ├── 002_create_categories_table.sql
│       ├── 003_create_transactions_table.sql
│       └── 004_create_budgets_table.sql
│
├── public/               ← Document root của web server
│   ├── index.php         ← Entry point duy nhất (Front Controller)
│   ├── .htaccess
│   ├── css/app.css
│   └── js/app.js
│
├── storage/logs/         ← Log files (gitignored)
├── tests/                ← PHPUnit tests
├── routes.php            ← Đăng ký tất cả routes
├── composer.json
├── .env.example          ← Copy thành .env rồi điền thông tin
└── .gitignore
```

---

## Cách setup từ đầu (chạy 1 lần)

### 1. Clone / tạo thư mục
```bash
# Copy toàn bộ thư mục này vào máy
cd de13_project
```

### 2. Cài Composer dependencies
```bash
composer install
```

### 3. Tạo file .env
```bash
cp .env.example .env
# Mở .env, điền DB_USER và DB_PASS của máy bạn
```

### 4. Tạo database
```bash
mysql -u root -p -e "CREATE DATABASE de13_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Chạy migration (tạo bảng)
```bash
cd database/migrations
mysql -u root -p de13_finance < 000_run_all.sql
cd ../..
```

### 6. Chạy web server
```bash
php -S localhost:8000 -t public
```

### 7. Mở trình duyệt
```
http://localhost:8000/login
```

---

## Luồng request hoạt động như thế nào?

```
Browser gửi GET /expenses
    ↓
public/index.php  (Front Controller)
    ↓ autoload + session_start
Router::dispatch('GET', '/expenses')
    ↓ tìm route phù hợp
AuthMiddleware::handle()
    ↓ kiểm tra $_SESSION['user_id']
ExpenseController::index()
    ↓ gọi TransactionRepository
    ↓ tạo Paginator
render('expenses/index', $data)
    ↓ extract($data) → include view file
Browser nhận HTML
```

---

## Design Patterns đã dùng

| Pattern | File | Giải thích |
|---------|------|-----------|
| **Template Method** | `Models/Transaction.php` | `process()` = final, gọi validate→save→notify |
| **Repository** | `Repositories/*` | Tách SQL khỏi business logic |
| **Singleton** | `Core/Database.php` | 1 kết nối PDO duy nhất |
| **Front Controller** | `public/index.php` | Mọi request đi qua 1 điểm |
| **Dependency Injection** | Tất cả Service/Controller | Inject qua constructor |
| **PRG (Post/Redirect/Get)** | Tất cả Controller POST | Tránh submit form 2 lần |

---

## Phân công & TODO theo ngày

### Ngày 1 — Họp chung + nền tảng
| Ai | File cần làm xong |
|----|------------------|
| **Cả team** | Thống nhất schema, chạy migration |
| **TV1** | `Models/Transaction.php` (abstract — cả team đọc) |
| **TV2** | `Models/Budget.php`, `BudgetRepositoryInterface` |
| **TV3** | `Core/Database.php` (Singleton PDO) |

### Ngày 2 — Model & Repository
| Ai | File |
|----|------|
| **TV1** | `Models/IncomeTransaction.php`, `Repositories/UserRepository.php` |
| **TV2** | `Repositories/BudgetRepository.php`, `CategoryRepository.php` |
| **TV3** | `Repositories/TransactionRepository.php` |
| **TV4** | `Services/FinanceReport.php` (generateMonthly, exportCsv) |
| **TV5** | `Services/AuthService.php`, `Helpers/CsrfTokenManager.php` |

### Ngày 3 — Service & Controller
| Ai | File |
|----|------|
| **TV1** | `Controllers/AuthController.php` |
| **TV2** | `Services/BudgetService.php`, `Controllers/BudgetController.php`, `CategoryController.php` |
| **TV3** | `Models/ExpenseTransaction.php`, `Controllers/ExpenseController.php` |
| **TV4** | `Controllers/IncomeController.php` |
| **TV5** | `Services/FinanceReport.php` (getByCategory, getTrend), `Controllers/ReportController.php` |

### Ngày 4 — View & UI
| Ai | View folder |
|----|------------|
| **TV1** | `Views/auth/`, `Views/partials/layout.php` |
| **TV2** | `Views/budget/`, `Views/categories/` |
| **TV3** | `Views/expenses/` |
| **TV4** | `Views/incomes/` |
| **TV5** | `Views/report/dashboard.php`, `Views/report/index.php` |

### Ngày 5 — Tích hợp
- TV1: `Middleware/AuthMiddleware.php` áp dụng cho Router
- TV3: Ghép ExpenseController ↔ BudgetService (checkAlert)
- TV4: Ghép IncomeController ↔ ReportService (dashboard update)
- TV5: Test báo cáo với data thật

### Ngày 6 — Nâng cao
- TV3: `Helpers/Paginator.php` vào ExpenseController
- TV4: Filter + sort vào IncomeController
- TV5: `FinanceReport::exportCsv()` + Chart.js tuần

### Ngày 7 — Demo
- Chuẩn bị giải thích pattern của phần mình
- Test toàn luồng từ đăng ký → báo cáo

---

## Câu hỏi giám khảo hay hỏi

1. **Template Method khác gì Strategy Pattern?**
   > Template Method: subclass override *bước*, không override *luồng*. Strategy: thay toàn bộ thuật toán.

2. **Tại sao process() là `final`?**
   > Đảm bảo thứ tự validate→save→notify luôn được giữ. Subclass chỉ được thay đổi *nội dung* từng bước.

3. **Repository Pattern giải quyết vấn đề gì?**
   > Tách biệt SQL khỏi business logic. Nếu đổi từ MySQL sang MongoDB, chỉ cần viết lại Repository, không sửa Service hay Controller.

4. **Dependency Injection là gì?**
   > Truyền dependency từ bên ngoài vào thay vì `new` bên trong. Giúp dễ test (có thể mock), dễ thay implementation.

5. **Tại sao mọi query đều phải có `WHERE user_id = ?`?**
   > Đây là ứng dụng multi-user. Nếu thiếu điều kiện này, user A có thể xem/xoá dữ liệu của user B.
