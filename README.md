# Đề 13 — Quản lý Tài chính Cá nhân (Tóm Tắt)

**Tác giả**: 5 thành viên team  
**Công nghệ**: PHP 8.2 · MySQL · Bootstrap 5 · Chart.js  
**Pattern**: MVC · Repository · Template Method · Dependency Injection  

---

## 1️⃣ Cài Đặt Nhanh

```powershell
# 1. Setup .env
copy .env.example .env

# 2. Import database từ SQL dump
C:\xampp\mysql\bin\mysql.exe -u root -p < database\de13_finance.sql

# 3. Start server
C:\xampp\php\php.exe -S localhost:8000 -t public
```

Mở `http://localhost:8000/register` → Tạo tài khoản → Đăng nhập  
📄 File `database/de13_finance.sql` chứa toàn bộ schema và dữ liệu mẫu

---

## 2️⃣ Cấu Trúc Thư Mục

```
app/
├── Core/              Router, Container, Database (Singleton)
├── Controllers/       AuthController, BudgetController, ...
├── Services/          AuthService, BudgetService, FinanceReport, ...
├── Repositories/      UserRepository, TransactionRepository, ...
├── Models/            Transaction (abstract + Template Method), Budget, Category
├── Middleware/        AuthMiddleware
├── Helpers/           CsrfTokenManager, FlashMessage, Paginator
└── Views/             auth, budget, categories, transactions, dashboard, report

database/
├── de13_finance.sql   ⭐ File SQL hoàn chỉnh — import trực tiếp
└── migrations/        (deprecated — dùng de13_finance.sql thay thế)

public/               index.php (entry point), css/, js/
```

---

## 3️⃣ Tính Năng Chính

| Tính năng | Route | TV | Công nghệ |
|-----------|-------|----|----|
| Đăng ký / Đăng nhập | `/auth/*` | TV1 | PHPMailer, Password hash (BCRYPT) |
| Dual login | `/login` | TV1 | Username OR Email |
| Budget (Ngân sách) | `/budget` | TV2 | Budget alerts, Progress bars |
| Danh mục | `/categories` | TV2 | Icon picker, Color selector |
| Giao dịch Thu/Chi | `/transactions` | TV3/TV4 | Filter, Sort, Paginate |
| Dashboard | `/` | TV5 | 4 Stat cards + 3 Charts (Chart.js) |
| Báo cáo | `/report` | TV5 | Chart + Export CSV |

---

## 4️⃣ Kiến Trúc Yêu Chốt

### Dependency Injection + Container
```php
// public/index.php
$container = new \App\Core\Container();
$container->bind(BudgetRepositoryInterface::class, BudgetRepository::class);
$router = new \App\Core\Router($container);
```
- **Container** auto-resolve dependencies qua Reflection
- **Router** dispatch request → `Container::make(Controller)` → action()

### Template Method (Transaction)
```php
// Models/Transaction.php
final public function process(): void {
    $this->validate();   // abstract
    $this->save();       // abstract
    $this->notify();     // abstract — khác nhau giữa Expense/Income
}
```

### Repository Pattern
```php
// Controllers gọi Service → Service gọi Repository → Repository chứa SQL
class BudgetService {
    public function __construct(
        private BudgetRepositoryInterface $budgetRepo,
        private TransactionRepository $txRepo
    ) {}
}
```

---

## 5️⃣ Thư Viện Dùng

### Frontend
- **Bootstrap 5**: CSS framework (responsive, components)
- **Chart.js**: Biểu đồ (bar, line, doughnut) — Dashboard, Report
- **Bootstrap Icons**: Icon set (Navbar, Budget, Categories)
- **Google Fonts**: Be Vietnam Pro (Vietnamese support)

### Backend
- **PDO**: MySQL database driver
- **PHPMailer**: Email (verification, password reset)
- **fputcsv()**: Export CSV (no external lib needed)
- **Reflection API**: Container dependency resolution

---

## 6️⃣ Phân Công 5 Thành Viên

| TV | Tên | Luồng |
|----|-----|-------|
| TV1 | Đạt | Auth (login, register, email verify, password reset) |
| TV2 | Hoài | Budget + Categories (ngân sách, danh mục) |
| TV3 | Quang | Expense transactions (chi tiêu, alerts) |
| TV4 | Hiếu | Income transactions, FinanceReport (thu nhập) |
| TV5 | Hằng | Dashboard, Report, ReportService (biểu đồ) |

---

## 7️⃣ Luồng Request Chính

```
Browser Request
    ↓
.htaccess (rewrite) → public/index.php
    ↓
Bootstrap: Container + Bindings + Session + Remember Me
    ↓
Router::dispatch(method, uri)
    ↓
Middleware (AuthMiddleware)
    ↓
Container::make(ControllerClass)
    ↓
Controller::action() 
    → call Service (business logic)
    → call Repository (SQL)
    → render(view, data)
    ↓
HTML Response
```

---

## 8️⃣ Design Patterns

| Pattern | Cách dùng |
|---------|-----------|
| **Dependency Injection** | Container resolve dependencies qua constructor |
| **Singleton** | Database (1 PDO per request) |
| **Repository** | Tách SQL khỏi Service layer |
| **Template Method** | Transaction::process() = validate→save→notify |
| **Front Controller** | Mọi request qua public/index.php |
| **PRG (Post-Redirect-Get)** | Form POST → redirect → GET (tránh resubmit) |

---

## 9️⃣ Tính Năng Nổi Bật

### Dual Login
- Input: Tên đăng nhập hoặc email
- Backend thử username trước, rồi email
- `AuthService::login($credential, $password)`

### Budget Alerts
- Check sau mỗi expense transaction
- So sánh: spent vs limit
- Alert: "Đã chi X đ / Y đ (Z%)"

### Password Strength Indicator
- Real-time JavaScript feedback
- 5 levels: Rất yếu → Rất mạnh
- Scoring: 8+ ký tự, chữ HOA, số, ký tự đặc biệt

### Dashboard Charts
- **Bar**: 4 tuần gần nhất (income vs expense)
- **Donut**: Chi tiêu/thu nhập theo danh mục
- **Stat Cards**: Số dư, chênh lệch, tổng chi/thu

### CSV Export
- No external library (dùng `fputcsv()`)
- UTF-8 BOM để Excel đọc tiếng Việt
- Format: Ngày | Loại | Danh mục | Số tiền | Ghi chú

---

## 🔟 Cách Sửa Từng Phần

| Cần sửa | File |
|---------|------|
| Thêm field login, đổi validation | `AuthController::register()`, `AuthService::login()` |
| Đổi threshold budget alert | `BudgetService::checkAlert()` |
| Thêm loại chart | `DashboardController::index()`, `ReportService` |
| Thay đổi giao diện | `Views/` tương ứng + CSS files |
| Thay đổi logic chi/thu | `ExpenseService`, `IncomeService` |

---

## 🔐 Security

- ✅ CSRF token (form POST)
- ✅ Password hash (BCRYPT)
- ✅ Session + Remember Me (secure cookie)
- ✅ Email verification (registration)
- ✅ Password reset (token hết hạn)
- ✅ Auth middleware (protect routes)

---

## 📝 Deployment

### Requirements
- PHP 8.x + PDO MySQL + mbstring
- MySQL 5.7+
- Document root: `/public`

### Steps
1. Upload project (skip `.env`)
2. Create database & import migrations
3. Create `.env` trên server (production config)
4. Set document root tới `/public`
5. Enable `mod_rewrite` (Apache) hoặc `try_files` (Nginx)

---

## 📞 Support

**Error**: Email không gửi được?
- Check `.env`: `MAIL_HOST`, `MAIL_USER`, `MAIL_PASS`
- Fallback: Local `php mail()` (ghi log tại `storage/logs/`)

**Error**: Không login được?
- Check CSRF token → Submit form lại
- Check database (users table)
- Check session (cookie browser)

**Error**: Chart không hiển thị?
- Check browser console (JavaScript errors)
- Check `$needChartJs = true` ở Controller
- Check Chart.js CDN load

---

**Full Documentation**: Xem [README.md](README.md) để chi tiết từng file, method, logic.
