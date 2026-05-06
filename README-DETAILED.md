# Đề 13 — Quản lý Tài chính Cá nhân
### PHP OOP · MVC · Template Method · Repository · Dependency Injection · Mail verify/reset

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
│   ├── index.php             ← ENTRY POINT duy nhất (Front Controller + bootstrap DI)
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
    │   ├── Container.php     ← IoC container mini
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
    │   ├── AuthService.php        ← login, register, remember me, mail verify/reset (TV1/TV5)
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
    │   ├── TransactionController.php ← /transactions CRUD gộp thu + chi (TV3/TV4)
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
        ├── transactions/     ← index, create, edit (TV3/TV4)
        ├── dashboard/        ← index.php với 2 Chart.js (TV5)
        └── report/           ← index.php + nút export CSV (TV5)
```

---

## Cài đặt & Chạy

```powershell
# 1. Copy cấu hình
copy .env.example .env

# 2. Tạo database
C:\xampp\mysql\bin\mysql.exe -u root -p -e "CREATE DATABASE de13_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Chạy migration
cd database\migrations
C:\xampp\mysql\bin\mysql.exe -u root -p de13_finance < 000_run_all.sql

# 4. Start server
C:\xampp\php\php.exe -S localhost:8000 -t public
```

Mở trình duyệt: `http://localhost:8000/register` để tạo tài khoản, sau đó có thể đăng nhập bằng username hoặc email.

---

## Luồng request

```
Browser → .htaccess → public/index.php
    │
    ├─ load autoload.php (PSR-4)
    ├─ create Container + bind interfaces
    ├─ load .env
    ├─ session_start()
    ├─ tryRememberLogin() (nếu có cookie)
    └─ Router::dispatch(method, uri)
           │
           ├─ match route
           ├─ run Middleware (AuthMiddleware nếu có)
            └─ Container::make(Controller) → action()
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
| **Dependency Injection** | `Container`, Service/Controller | Inject qua constructor + bootstrap |
| **PRG** | Tất cả Controller POST | Redirect sau POST tránh resubmit |
| **Interface** | `BudgetRepositoryInterface` | Contract tách khỏi implementation |
| **Mail Service** | `MailService` | Gửi email xác nhận và quên mật khẩu |

---

## Thư viện & Dependencies

### Frontend Libraries

**Chart.js** (CDN)
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
```
- **Mục đích**: Vẽ biểu đồ (bar, line, doughnut/pie)
- **Dùng ở**: Dashboard (`/`), Report (`/report`)
- **Loại chart**: 
  - Bar chart: Trend 4 tuần (income vs expense)
  - Line chart: Số dư lũy kế theo thời gian
  - Doughnut chart: Breakdown chi tiêu/thu nhập theo danh mục
- **Cách sử dụng**: ReportService format dữ liệu → JSON-encode → truyền vào View → Chart.js init

**Bootstrap 5** (CDN)
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
```
- **Mục đích**: CSS framework cho responsive grid, components (buttons, modals, etc.)
- **Dùng ở**: Tất cả trang

**Bootstrap Icons** (CDN)
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
```
- **Mục đích**: Icon set (bi-* classes)
- **Dùng ở**: Navbar, budget progress, transaction icons, category pickers

**Google Fonts** (CDN)
```html
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
```
- **Mục đích**: Custom font chữ Việt đẹp
- **Font chọn**: Be Vietnam Pro (nhẹ, hiện đại)

### Backend Libraries

**PHP Built-in** (No Composer needed)
- **PDO** (`php:pdo_mysql`): Database driver (MySQL via PDO)
- **Reflection API**: Container dùng để auto-resolve dependencies
- **fputcsv()**: Export CSV (không cần thư viện ngoài)
- **PHPMailer** (bundled): Email sending

**PHPMailer** (vendor/phpmailer/)
- **Mục đích**: Gửi email xác nhận tài khoản, reset password
- **Setup**: 3 cách fallback:
  1. PHPMailer thật từ GitHub (nếu có `vendor/phpmailer/src/PHPMailer.php`)
  2. Composer (`vendor/autoload.php`)
  3. Wrapper tự viết fallback (`vendor/phpmailer/PHPMailer.php`)
- **Cách sử dụng**: `MailService::sendVerification()`, `MailService::sendPasswordReset()`
- **Config**: `.env` chứa `MAIL_HOST`, `MAIL_USER`, `MAIL_PASS`, `MAIL_PORT`
- **Fallback nếu không config**: Dùng `php mail()` (sendmail/postfix)

---

### CSV Export

**Cơ chế**: PHP built-in `fputcsv()` + stream output
```php
// app/Services/FinanceReport.php::exportCsvByRange()
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="Bao-cao-YYYY-MM-DD_YYYY-MM-DD.csv"');
echo "\xEF\xBB\xBF";  // BOM UTF-8 (để Excel đọc đúng tiếng Việt)

$out = fopen('php://output', 'w');
fputcsv($out, ['Ngày', 'Loại', 'Danh mục', 'Số tiền (đ)', 'Ghi chú']);
foreach ($rows as $row) {
    fputcsv($out, [$row['trans_date'], ...]);
}
```

**Tại sao không dùng thư viện?**
- `fputcsv()` đã hỗ trợ tốt: tự escape dấu phẩy, dấu ngoặc
- BOM UTF-8 đơn giản: `\xEF\xBB\xBF` ở đầu file
- Stream output: không cần tạo file tạm, ghi trực tiếp qua HTTP
- Lightweight: không phụ thuộc thư viện ngoài

**Cách dùng**:
- Click nút "Export CSV" ở trang Report → `/report/export?date_from=X&date_to=Y`
- Browser tự động download file `Bao-cao-YYYY-MM-DD_YYYY-MM-DD.csv`

---

## Cơ chế & Tính năng

### **Dependency Injection & Container**

```php
// Bootstrap (public/index.php)
$container = new \App\Core\Container();
$container->bind(BudgetRepositoryInterface::class, BudgetRepository::class);
$router = new \App\Core\Router($container);
```

- `Container::make(ClassName)` dùng **Reflection** để resolve constructor dependencies
- Router nhận Container qua constructor → khi dispatch, gọi `$container->make(ControllerClass)`
- Ví dụ: `BudgetController` cần `BudgetService` → `BudgetService` cần `BudgetRepository`
- Container tự động inject theo chain → không cần manually `new` instances

**Lợi ích**: 
- Tách coupling (dễ swap Repository/Service implementations)
- Dễ test (mock dependencies)
- Dễ maintain (thay đổi dependency logic ở 1 chỗ)

---

### Mail Service (Email Verification & Password Reset)

Config ở `.env`:
```dotenv
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your-email@gmail.com
MAIL_PASS=your-app-password
MAIL_FROM=noreply@de13finance.local
MAIL_FROM_NAME=FinanceApp
MAIL_DEBUG=0
```

**Fallback khi không config SMTP**:
- Dùng `php mail()` (local sendmail/postfix)
- Ghi log email vào `storage/logs/` để test localhost

**Cơ chế**:
```php
// app/Services/MailService.php
$from = $_ENV['MAIL_FROM'] ?? 'noreply@de13finance.local';
$fromName = $_ENV['MAIL_FROM_NAME'] ?? 'FinanceApp';

// Gửi verification email lúc register
$this->sendVerification($email, $name, $verifyUrl);

// Gửi password reset email
$this->sendPasswordReset($email, $name, $resetUrl);
```

- **Verification**: Người dùng phải click link email để active tài khoản trước khi login
- **Password Reset**: Tạo token hết hạn (2 giờ), gửi link reset qua email, xác minh rồi mới thay password

---

### Dual Login (Username OR Email)

```php
// app/Services/AuthService::login($credential, $password)
// Thử tìm user bằng username trước, nếu không thấy thì thử email
private function findByUsernameOrEmail(string $cred): ?User {
    return $this->userRepo->findByUsername($cred) ?? $this->userRepo->findByEmail($cred);
}
```

- Input form: `name="username"` nhưng nhận cả username hoặc email
- View label: "Tên đăng nhập hoặc email"
- Backend: `AuthService::login()` tự động thử cả 2 cách

---

### Password Strength Indicator

Real-time JavaScript feedback (client-side, ở `register.php`):

```javascript
function checkPasswordStrength(pw) {
    let score = 0;
    if (pw.length >= 8)  score++;      // ≥8 ký tự
    if (pw.length >= 12) score++;      // ≥12 ký tự
    if (/[A-Z]/.test(pw)) score++;     // Có chữ HOA
    if (/[0-9]/.test(pw)) score++;     // Có chữ số
    if (/[^A-Za-z0-9]/.test(pw)) score++; // Có ký tự đặc biệt
    
    // Score 0-4 → 5 levels
    const levels = [
        { w: '20%', cls: 'bg-danger',  label: 'Rất yếu' },    // score 0
        { w: '40%', cls: 'bg-danger',  label: 'Yếu' },        // score 1
        { w: '60%', cls: 'bg-warning', label: 'Trung bình' }, // score 2
        { w: '80%', cls: 'bg-info',    label: 'Mạnh' },       // score 3
        { w: '100%',cls: 'bg-success', label: 'Rất mạnh' },   // score 4+
    ];
    const lvl = levels[Math.min(score, 4)];
    updateBar(lvl);
}
```

- **Server-side validation** (AuthController register):
  - Minimum: 8 ký tự, 1 chữ HOA, 1 chữ số
- **Client-side feedback** (register.php):
  - Thêm độ mạnh thể hiện → thanh tiến độ màu Bootstrap

---

## Phân công thành viên

| TV | Tên | Luồng | File chính |
|----|-----|-------|-----------|
| TV1 | Đạt | Auth | `AuthController`, `AuthService`, `UserRepository`, `MailService`, `Transaction` (abstract) |
| TV2 | Hoài | Budget | `BudgetController`, `CategoryController`, `BudgetService`, `Budget`, `Category` |
| TV3 | Quang | Chi tiêu | `TransactionController`, `ExpenseService`, `ExpenseTransaction`, `TransactionRepository` |
| TV4 | Hiếu | Thu nhập | `TransactionController`, `IncomeService`, `IncomeTransaction`, `FinanceReport` |
| TV5 | Hằng | Báo cáo | `DashboardController`, `ReportController`, `ReportService` |

---

## Chi tiết Logic Từng File

### Models — app/Models/

#### Transaction.php (abstract)
**Mục đích**: Định nghĩa Template Method pattern cho luồng xử lý giao dịch.

**Logic**:
- Lớp abstract chứa template method `final process()` — không ai override được
- Luồng cố định: `validate()` → `save()` → `notify()`
- Subclass (ExpenseTransaction, IncomeTransaction) implement 3 bước này
- `notify()` khác nhau giữa thu/chi:
  - **Expense**: Kiểm tra ngân sách, cảnh báo nếu vượt
  - **Income**: Chỉ ghi log

**Khi sửa**: Nếu cần thay đổi luồng xử lý giao dịch (add/edit/delete) → sửa đây.

---

#### ExpenseTransaction.php / IncomeTransaction.php
**Mục đích**: Implement template method cho chi tiêu/thu nhập.

**ExpenseTransaction**:
- `validate()`: Kiểm tra category, amount > 0, trans_date hợp lệ
- `save()`: Gọi `TransactionRepository::add()`
- `notify()`: Gọi `BudgetService::checkAlert()` → set budget alert

**IncomeTransaction**:
- `validate()`: Tương tự Expense
- `save()`: Gọi `TransactionRepository::add()`
- `notify()`: Chỉ ghi log (không check budget)

**Khi sửa**: 
- Đổi validation rules → sửa `validate()`
- Thêm xử lý sau khi lưu → sửa `notify()`

---

#### Budget.php / Category.php
**Mục đích**: Business objects đại diện cho budget và danh mục.

**Budget**:
- `getLimitAmount()`: Trả hạn mức ngân sách
- `isExceeded(float $spent)`: Kiểm tra đã vượt ngưỡng cảnh báo chưa
- `getUsagePercent(float $spent)`: Tính % sử dụng

**Category**:
- `getName()`, `getIcon()`, `getColor()`: Getter properties

**Khi sửa**: 
- Thay đổi logic check ngân sách (ví dụ: alert ở 80% thay vì 100%) → sửa `Budget::isExceeded()`

---

### Services — app/Services/

#### AuthService.php (TV1)
**Mục đích**: Xử lý auth (login, register, email verify, password reset).

**Logic chính**:
1. **login($credential, $password)**: Thử tìm user bằng username trước, nếu không có thì thử email
2. **register($username, $email, $password)**: Kiểm tra trùng, hash password, tạo verification token
3. **verifyEmail($token)**: Validate token, active account
4. **forgotPassword($email)**: Tạo reset token, gửi email
5. **resetPassword($token, $newPassword)**: Validate token, update password
6. **setRememberMeCookie($userId)**: Tạo "remember me" token, set cookie

**Injection**:
- `UserRepository $userRepo`: Truy cập DB users
- `?MailService $mailer`: Gửi email (optional, để test)

**Khi sửa**:
- Đổi logic dual login → sửa `login()` method
- Thêm validation khi register → sửa `register()`
- Thay đổi cơ chế remember me → sửa cookie logic

---

#### BudgetService.php (TV2)
**Mục đích**: Business logic cho ngân sách.

**Logic chính**:
1. **checkAlert($categoryId, $userId, $month, $year)**: 
   - Lấy tổng chi trong tháng của danh mục
   - Check xem đã vượt hạn mức chưa
   - Trả string cảnh báo hoặc null
2. **setLimit($userId, $categoryId, $limitAmount, $month, $year)**: 
   - Tạo/cập nhật budget (INSERT ... ON DUPLICATE KEY UPDATE)
3. **getBudgetSummary($userId, $month, $year)**:
   - Trả mảng budget của all categories trong tháng
   - Mỗi phần tử: `['name', 'spent', 'limit', 'usage_percent', 'is_exceeded', 'status_class']`

**Injection**:
- `BudgetRepositoryInterface $budgetRepo`: Repository budget
- `TransactionRepository $txRepo`: Lấy tổng chi

**Khi sửa**:
- Thay đổi ngưỡng cảnh báo (ví dụ: cảnh báo khi vượt 90% thay vì 100%) → sửa `checkAlert()` logic
- Thêm/xoá field trong budget summary → sửa `getBudgetSummary()`

---

#### ExpenseService.php (TV3)
**Mục đích**: Business logic cho chi tiêu.

**Logic chính**:
1. **add($data, $userId)**:
   - Tạo `ExpenseTransaction` object
   - Set `BudgetService` để transaction biết cách check budget
   - Gọi `$tx->process()` (template method)
   - Trả budget alert (nếu có)
2. **update($id, $userId, $data)**: Cập nhật chi tiêu
3. **delete($id, $userId)**: Xoá chi tiêu

**Quan trọng**: Sau mỗi `add()`, alert được set vào transaction → Controller lấy alert này set flash message.

**Khi sửa**:
- Thêm validation → sửa `add()` hoặc `ExpenseTransaction::validate()`
- Thay đổi cách tính cảnh báo → sửa `BudgetService::checkAlert()`

---

#### IncomeService.php (TV4)
**Mục đích**: Business logic cho thu nhập.

**Logic chính**:
1. **add($data, $userId)**:
   - Tạo `IncomeTransaction`
   - Gọi `process()` (không check budget)
   - Void (không trả alert)
2. **update($id, $userId, $data)**: Cập nhật
3. **delete($id, $userId)**: Xoá

**Khác Expense**: Không check ngân sách, không trả alert.

**Khi sửa**: 
- Thêm logic riêng cho income (ví dụ: send notification khi income > threshold) → sửa `add()` hoặc `IncomeTransaction::notify()`

---

#### FinanceReport.php (TV4)
**Mục đích**: Generate báo cáo tài chính.

**Logic chính**:
1. **generateMonthly($startDate, $endDate, $userId)**:
   - Tính tổng income/expense trong range
   - Trả array `['income' => ..., 'expense' => ..., 'net' => ...]`
2. **generateRange()**: Tương tự
3. **exportCsv()**: Export transactions ra CSV

**Khi sửa**: Thay đổi format/tính toán báo cáo → sửa các generate method.

---

#### ReportService.php (TV5)
**Mục đích**: Format dữ liệu báo cáo cho Chart.js.

**Logic chính**:
- `getChartData()`: Trả dữ liệu format cho Chart.js (labels, datasets, etc.)
- Xử lý data từ FinanceReport → format tiêu chuẩn Chart.js

**Khi sửa**: Thêm report page → sửa đây.

---

### Controllers — app/Controllers/

#### AuthController.php (TV1)
**Routes**:
- GET `/login` → `showLogin()` (render form)
- POST `/login` → `login()` (xử lý login)
- GET `/register` → `showRegister()` (render form)
- POST `/register` → `register()` (xử lý register)
- POST `/logout` → `logout()`
- GET `/forgot-password` → `showForgot()`
- POST `/forgot-password` → `forgotPassword()`
- GET `/reset-password?token=...` → `showReset()`
- POST `/reset-password` → `resetPassword()`
- GET `/verify-email?token=...` → `verifyEmail()`

**Logic**:
- **register()**: Per-field validation → session store errors → redirect
- **login()**: Thử dual login (username/email), set remember me cookie (optional)
- Error mapping: Email duplicate errors → 'email' field, username duplicate → 'username' field

**Khi sửa**:
- Thêm validation field → sửa `register()` validation loop
- Thay đổi error message → sửa text cảnh báo
- Thêm step trong auth flow → thêm method mới

---

#### BudgetController.php (TV2)
**Routes**:
- GET `/budget` → `index()` (hiển thị budget tháng hiện tại)
- POST `/budget` → `setLimit()` (đặt hạn mức)
- POST `/budget/{id}/delete` → `destroy()`

**Logic chính**:
1. **index()**:
   - Lấy month/year từ GET (default tháng hiện tại)
   - Gọi `BudgetService::getBudgetSummary()` → mảng budget
   - Paginate (8 mục/trang)
   - Render view với summary, categories, paginator
2. **setLimit()**:
   - Validate limit > 0
   - Check "apply_to_end_of_year" checkbox
   - Gọi `BudgetService::setLimit()` → lưu hoặc cập nhật budget
   - Redirect với flash message

**Khi sửa**: Thay đổi số mục per page → sửa `$perPage` trong `index()`

---

#### TransactionController.php (TV3/TV4)
**Routes**:
- GET `/transactions` → `index()` (list transactions với filter/sort/pagination)
- GET `/transactions/create` → `showCreate()` (form add)
- POST `/transactions` → `create()` (xử lý add income hoặc expense)
- GET `/transactions/{id}/edit` → `showEdit()`
- POST `/transactions/{id}` → `update()`
- POST `/transactions/{id}/delete` → `delete()`

**Logic chính**:
1. **index()**:
   - Filter: type (income/expense), category, date range
   - Sort: date_desc (default), date_asc, amount_desc, amount_asc
   - Paginate (10 items/page)
   - Lấy daily summary → stat cards
   - Lấy over-budget list để cảnh báo
2. **create()**:
   - Validate CSRF, input
   - Gọi `ExpenseService::add()` hoặc `IncomeService::add()`
   - Nếu expense có alert → set flash message
   - Redirect
3. **update()** / **delete()**: Tương tự

**Khi sửa**: Thêm filter mới → sửa `index()` query logic

---

#### DashboardController.php (TV5)
**Routes**:
- GET `/` → `index()` (dashboard 1)
- GET `/dashboard` → `dashboard()` (dashboard 2)

**Logic**: Lấy summary tháng hiện tại, generate chart data → render với 2 Chart.js

**Khi sửa**: Thay đổi khoảng thời gian/metrics → sửa data lấy từ Service

---

#### CategoryController.php (TV2)
**Routes**:
- GET `/categories` → `index()`
- GET `/categories/create` → `showCreate()`
- POST `/categories` → `create()`
- GET `/categories/{id}/edit` → `showEdit()`
- POST `/categories/{id}` → `update()`
- POST `/categories/{id}/delete` → `delete()`

**Logic**: CRUD danh mục, mỗi method validate → gọi Repository → redirect

**Khi sửa**: Thêm validation → sửa các validate block

---

#### ReportController.php (TV5)
**Routes**:
- GET `/report` → `index()` (render report page với chart)
- GET `/report/export` → `export()` (download CSV)

**Logic**: Lấy data từ `FinanceReport`, format data qua `ReportService`, render hoặc export

---

### Views — app/Views/

Tất cả Views là PHP templates, nhận data từ Controller qua `$this->render()`.

#### Global Layout (partials/layout.php)
**Mục đích**: Master template chứa navbar, Bootstrap, icons, CSS chung.

**Cấu trúc**:
```html
<!DOCTYPE html>
<head>
  Bootstrap 5 + Bootstrap Icons + Google Fonts
  Navbar (global, tất cả trang)
  CSS global (layout.css, navbar.css)
  CSS riêng trang (via $extraCss, $extraCss2 từ Controller)
<body>
  <nav class="navbar">...</nav>
  <main>
    ... nội dung từ child view ...
  </main>
  <footer>...</footer>
  Chart.js (lazy load - chỉ load nếu $needChartJs = true)
```

**Tính năng**:
- Purple gradient buttons: `.btn-primary`, `.btn-dark`, `.btn-add`
- Modern cards với hover effects
- Responsive: grid cột thay đổi theo kích thước màn hình
- Dropdown animations
- Table styling với `border-spacing`

**Khi sửa**:
- Thay đổi navbar (logo, menu) → sửa navbar section
- Thêm global CSS (tất cả trang dùng) → sửa style block
- Thêm library JS/CSS khác → sửa head section

---

#### Auth Views (auth/login.php, register.php, forgot.php, reset.php)
**Mục đích**: Các trang xác thực (login/register/reset password).

**Cấu trúc chung**:
- Tâm trí: form card ở giữa màn hình
- Input group: icon + input field + error message
- CSRF token (hidden)
- Per-field error highlighting

**register.php** — Chi tiết:
- Username: pattern `[a-zA-Z0-9_]+`, minlength 3
- Email: type="email", built-in validation
- Password: type="password" + toggle button (show/hide)
- **Password Strength Indicator**: Real-time JS feedback
  - Thanh tiến độ (`<div class="progress">`)
  - Text "Độ mạnh: ..." cập nhật khi gõ
  - Function `checkPasswordStrength()` ở cuối file
- Confirm password: client-side check (submit preventDefault nếu không khớp)

**login.php** — Chi tiết:
- Label: "Tên đăng nhập hoặc email" (dual login)
- Checkbox "Ghi nhớ đăng nhập"
- Link "Quên mật khẩu?" → `/forgot-password`

**forgot.php** / **reset.php** — Chi tiết:
- forgot: Nhập email, gửi reset link
- reset: Nhập password mới, cần token từ URL

**Khi sửa**:
- Thêm validation field → sửa input trong form
- Đổi validation rule → sửa pattern/minlength
- Đổi error message styling → sửa `.invalid-feedback` CSS

---

#### Dashboard View (dashboard/index.php)
**Mục đích**: Trang chủ, hiển thị overview tài chính.

**Cấu trúc**:
```
4 Stat Cards (row g-3):
  ├─ Số dư ví hiện tại (success nếu >= 0, danger nếu < 0)
  ├─ Tổng chênh lệch theo kỳ (income - expense)
  ├─ Tổng chi tiêu (luôn red)
  └─ Tổng thu nhập (luôn green)

3 Charts (row g-4):
  ├─ Bar Chart: 4 tuần gần nhất (income vs expense)
  ├─ Donut Chart: Thu nhập theo danh mục (tháng hiện tại)
  └─ Donut Chart: Chi tiêu theo danh mục (tháng hiện tại)
```

**Dữ liệu từ Controller**:
- `$summary`: `['period_balance', 'income', 'expense']`
- `$walletBalance`: Số dư tính lũy kế từ đầu tháng
- `$chartJson`: JSON chứa bar data + 2 donut data

**Chart.js Setup**:
```html
<canvas id="incomeChart"></canvas>
<script>
  const charts = JSON.parse('<?= $chartJson ?>');
  new Chart(ctx, { type: 'bar', data: charts.bar, ... });
</script>
```

**CSS** (dashboard.css):
- `.stat-card`: Card với icon, label, value, period
- `.chart-card`: Wrapper cho chart canvas
- Color classes: `.text-success`, `.text-danger`, `.text-info`

**Khi sửa**:
- Thêm stat card mới → thêm phần tử vào mảng `$cards`
- Thay đổi khoảng thời gian chart (ví dụ 8 tuần thay vì 4) → sửa `getTrend(8, ...)` call
- Thay đổi chart type (ví dụ line thay vì bar) → sửa Chart.js config

---

#### Budget View (budget/index.php)
**Mục đích**: Hiển thị budget tháng hiện tại, set/update limit.

**Cấu trúc**:
```
Page Header: Title + subtitle + month navigator

Table:
  ├─ Columns: Category name, Budget limit, Spent, %, Bar, Actions
  ├─ Mỗi row: 1 danh mục
  ├─ Progress bar: % màu (green, yellow, red)
  └─ Actions: Edit, Delete

Modal (Edit):
  ├─ Select category
  ├─ Input limit amount
  ├─ Checkbox "Apply to end of year"
  └─ Submit / Cancel

Pagination: Page x/y, Prev/Next buttons
```

**Dữ liệu từ Controller**:
- `$summary`: Mảng budget trang hiện tại (paginated)
- `$allSummary`: Toàn bộ budget tháng (dùng cho modal JS)
- `$cats`: Danh sách danh mục (dropdown)
- `$pager`: Paginator object

**Tính năng**:
- Month navigator: Nút prev/next month, hiển thị tháng/năm
- Progressive bar: Width = (spent / limit) * 100%
- Row click → mở modal edit budget
- Status bar color:
  - Green: < 70% → OK
  - Yellow: 70-99% → Warning
  - Red: >= 100% → Exceeded

**Khi sửa**:
- Thêm column vào table → thêm `<td>` trong loop
- Thay đổi color status logic → sửa status calculation
- Đổi items per page → sửa `$perPage` ở BudgetController

---

#### Transactions View (transactions/index.php, create.php, edit.php)
**Mục đích**: CRUD giao dịch (thu/chi).

**index.php** — Chi tiết:
```
Filters & Navigation:
  ├─ Date range picker (start_date, end_date)
  ├─ Type filter: All / Income / Expense
  ├─ Category dropdown
  ├─ Sort dropdown: Date (newest/oldest), Amount (high/low)
  └─ Search button

3 Summary Cards (từng loại):
  ├─ Total Income
  ├─ Total Expense
  └─ Net Balance

Alert: Over-budget warning (nếu có danh mục vượt budget)

Table:
  ├─ Columns: Date, Category, Note, Amount, Type, Actions
  ├─ Amount màu xanh (income) / đỏ (expense)
  └─ Edit / Delete buttons per row

Pagination: Page x/y
```

**Dữ liệu từ Controller**:
- `$items`: Danh sách transaction paginated
- `$summary`: Tổng thu/chi range hiện tại
- `$dailySummary`: Tổng từng ngày (cho sparkline/chart)
- `$overBudgets`: Danh sách danh mục vượt budget
- `$cats`: Categories dropdown
- `$pager`: Paginator

**create.php / edit.php** — Chi tiết:
- Type selector: Income / Expense (radio button)
- Category dropdown (dynamic - thay đổi khi chọn type)
- Amount input (currency format)
- Date picker (default hôm nay)
- Note textarea
- Submit / Cancel

**Khi sửa**:
- Thêm column table → thêm `<td>` hoặc `<th>`
- Thay đổi filter options → sửa filter form
- Đổi sort logic → thêm option vào `$sort` dropdown

---

#### Categories View (categories/index.php, edit.php)
**Mục đích**: Quản lý danh mục (CRUD).

**index.php** — Chi tiết:
```
Header + Add button

Table:
  ├─ Columns: Icon, Name, Color, Type (Income/Expense), Actions
  ├─ Icon picker modal (40+ icons)
  └─ Color picker (12 màu sắc)

Modal (Edit/Create):
  ├─ Select icon
  ├─ Category name
  ├─ Color picker
  ├─ Type (Income/Expense)
  └─ Save / Cancel
```

**Khi sửa**: 
- Thêm icon mới → sửa icon list ở icon_picker_modal.php
- Thay đổi màu palette → sửa color array

---

#### Report View (report/index.php)
**Mục đích**: Báo cáo tài chính chi tiết.

**Cấu trúc**:
```
Date range picker + Export CSV button

Summary section:
  ├─ Tổng chi tiêu theo danh mục
  ├─ Tổng thu nhập theo danh mục
  └─ Net balance

Charts:
  ├─ Bar: Tổng thu/chi theo tháng (6 tháng gần nhất)
  ├─ Line: Trending số dư
  └─ Pie: Breakdown chi tiêu

Export:
  └─ CSV button → download transactions
```

**Khi sửa**: 
- Thêm report metric → sửa ReportService + report view
- Thay đổi chart type → sửa Chart.js config

---

### CSS Files — public/css/

#### navbar.css
**Mục đích**: Styling navbar toàn cầu.

**Tính năng**:
- Sticky navbar (fixed top)
- Responsive: hamburger menu mobile, horizontal menu desktop
- Active link highlight

---

#### auth.css
**Mục đích**: Styling trang xác thực.

**Tính năng**:
- `.auth-wrapper`: Căn giữa form (flexbox)
- `.auth-card`: Form card với border + shadow
- `.strength-bar`: Password strength bar (4px, animated width)
- `.strength-bar` colors: Đỏ (yếu) → Xanh (mạnh)
- Entrance animation: `@keyframes authFadeUp`

---

#### dashboard.css
**Mục đích**: Styling dashboard.

**Tính năng**:
- `.stat-card`: Mini card với icon + value + period
- `.chart-card`: Wrapper cho chart canvas
- Stat card colors: Green (positive), Red (negative)

---

#### budget.css, categories.css, transactions.css, report.css
**Mục đích**: Styling cho từng trang.

**Chung**: 
- Table styling (border, hover, striped)
- Modal styling
- Form styling
- Modal animations
- Progress bar colors

**Khi sửa**:
- Thay đổi màu chủ đề → sửa CSS variables hoặc `.bg-*` classes
- Thêm animation → thêm `@keyframes` block

---

## Dashboard Flow (End-to-End)

**Khi user truy cập `/dashboard` hoặc `/`:**

1. **DashboardController::index()** nhận request:
   ```php
   $uid = $this->currentUserId();
   $monthStart = date('Y-m-01');
   $monthEnd = date('Y-m-t');
   ```

2. **Lấy dữ liệu từ Services**:
   ```php
   $summary = $reportService->getSummaryByRange($monthStart, $monthEnd, $uid);
   // → Trả ['income' => X, 'expense' => Y, 'period_balance' => X-Y]
   
   $walletBalance = $reportService->getWalletBalance($monthEnd, $uid);
   // → Số dư lũy kế đến cuối tháng
   
   $barData = $reportService->getTrend(4, $uid);
   // → 4 tuần gần nhất: [week1_income, week1_expense, ...]
   
   $incomeDonut = $reportService->getByCategoryByRange($monthStart, $monthEnd, $uid, 'income');
   // → [{name: 'Salary', value: 5000}, ...]
   
   $expenseDonut = $reportService->getByCategoryByRange($monthStart, $monthEnd, $uid, 'expense');
   // → [{name: 'Food', value: 500}, ...]
   ```

3. **Format Chart Data**:
   ```php
   $chartJson = json_encode([
       'bar' => $barData,
       'incomeDonut' => $incomeDonut,
       'expenseDonut' => $expenseDonut,
   ], JSON_UNESCAPED_UNICODE);
   ```

4. **Render View** (dashboard/index.php):
   ```php
   $this->render('dashboard/index', [
       'summary' => $summary,
       'walletBalance' => $walletBalance,
       'chartJson' => $chartJson,
       'needChartJs' => true,  // Load Chart.js library
   ]);
   ```

5. **View Rendering**:
   - **Stat Cards**: 4 cards (wallet, balance, expense, income)
     - Color: Green nếu positive, Red nếu negative
     - Format: `+5,000đ` hoặc `-1,000đ`
   - **Bar Chart**: Trend 4 tuần, 2 bars per week (income vs expense)
   - **Donut Charts**: 2 charts bên dưới (income breakdown, expense breakdown)

6. **Chart.js Initialization** (ở footer):
   ```html
   <script>
     const charts = JSON.parse('<?= $chartJson ?>');
     
     // Bar chart
     new Chart(document.getElementById('trendChart').getContext('2d'), {
         type: 'bar',
         data: charts.bar,
         options: { ... responsive, plugins ... }
     });
     
     // Donut charts
     new Chart(document.getElementById('incomeChart'), {
         type: 'doughnut',
         data: charts.incomeDonut,
     });
     new Chart(document.getElementById('expenseChart'), {
         type: 'doughnut',
         data: charts.expenseDonut,
     });
   </script>
   ```

**Key Points**:
- ReportService chỉ format dữ liệu (labels, datasets format Chart.js)
- FinanceReport tính toán metrics (sum, average, etc.)
- JSON-encode chart data để truyền safely vào JavaScript
- `$needChartJs = true` → footer lazy-load Chart.js library
- **Khi sửa**: Thay đổi số charts → sửa Dashboard View + ReportService getter methods

---

## Quick Start (copy .env and run)

#### AuthController.php (TV1)
**Routes**:
- GET `/login` → `showLogin()` (render form)
- POST `/login` → `login()` (xử lý login)
- GET `/register` → `showRegister()` (render form)
- POST `/register` → `register()` (xử lý register)
- POST `/logout` → `logout()`
- GET `/forgot-password` → `showForgot()`
- POST `/forgot-password` → `forgotPassword()`
- GET `/reset-password?token=...` → `showReset()`
- POST `/reset-password` → `resetPassword()`
- GET `/verify-email?token=...` → `verifyEmail()`

**Logic**:
- **register()**: Per-field validation → session store errors → redirect
- **login()**: Thử dual login (username/email), set remember me cookie (optional)
- Error mapping: Email duplicate errors → 'email' field, username duplicate → 'username' field

**Khi sửa**:
- Thêm validation field → sửa `register()` validation loop
- Thay đổi error message → sửa text cảnh báo
- Thêm step trong auth flow → thêm method mới

---

#### BudgetController.php (TV2)
**Routes**:
- GET `/budget` → `index()` (hiển thị budget tháng hiện tại)
- POST `/budget` → `setLimit()` (đặt hạn mức)
- POST `/budget/{id}/delete` → `destroy()`

**Logic chính**:
1. **index()**:
   - Lấy month/year từ GET (default tháng hiện tại)
   - Gọi `BudgetService::getBudgetSummary()` → mảng budget
   - Paginate (8 mục/trang)
   - Render view với summary, categories, paginator
2. **setLimit()**:
   - Validate limit > 0
   - Check "apply_to_end_of_year" checkbox
   - Gọi `BudgetService::setLimit()` → lưu hoặc cập nhật budget
   - Redirect với flash message

**Khi sửa**: Thay đổi số mục per page → sửa `$perPage` trong `index()`

---

#### TransactionController.php (TV3/TV4)
**Routes**:
- GET `/transactions` → `index()` (list transactions với filter/sort/pagination)
- GET `/transactions/create` → `showCreate()` (form add)
- POST `/transactions` → `create()` (xử lý add income hoặc expense)
- GET `/transactions/{id}/edit` → `showEdit()`
- POST `/transactions/{id}` → `update()`
- POST `/transactions/{id}/delete` → `delete()`

**Logic chính**:
1. **index()**:
   - Filter: type (income/expense), category, date range
   - Sort: date_desc (default), date_asc, amount_desc, amount_asc
   - Paginate (10 items/page)
   - Lấy daily summary → stat cards
   - Lấy over-budget list để cảnh báo
2. **create()**:
   - Validate CSRF, input
   - Gọi `ExpenseService::add()` hoặc `IncomeService::add()`
   - Nếu expense có alert → set flash message
   - Redirect
3. **update()** / **delete()**: Tương tự

**Khi sửa**: Thêm filter mới → sửa `index()` query logic

---

#### DashboardController.php (TV5)
**Routes**:
- GET `/` → `index()` (dashboard 1)
- GET `/dashboard` → `dashboard()` (dashboard 2)

**Logic**: Lấy summary tháng hiện tại, generate chart data → render với 2 Chart.js

**Khi sửa**: Thay đổi khoảng thời gian/metrics → sửa data lấy từ Service

---

#### CategoryController.php (TV2)
**Routes**:
- GET `/categories` → `index()`
- GET `/categories/create` → `showCreate()`
- POST `/categories` → `create()`
- GET `/categories/{id}/edit` → `showEdit()`
- POST `/categories/{id}` → `update()`
- POST `/categories/{id}/delete` → `delete()`

**Logic**: CRUD danh mục, mỗi method validate → gọi Repository → redirect

**Khi sửa**: Thêm validation → sửa các validate block

---

#### ReportController.php (TV5)
**Routes**:
- GET `/report` → `index()` (render report page với chart)
- GET `/report/export` → `export()` (download CSV)

**Logic**: Lấy data từ `FinanceReport`, format data qua `ReportService`, render hoặc export

