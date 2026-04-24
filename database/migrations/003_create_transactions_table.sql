-- ============================================================
-- MIGRATION 003 — transactions (BẢNG TRUNG TÂM)
-- Chạy SAU 002
-- TV3 + TV4 cùng ghi/đọc bảng này — thống nhất schema ngày 1
-- ============================================================
-- Đề bài yêu cầu: ghi thu/chi theo danh mục
-- Không có ví — mọi giao dịch gộp chung 1 tài khoản user
-- ============================================================

CREATE TABLE IF NOT EXISTS `transactions` (
    `id`          INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED        NOT NULL  COMMENT 'BẮT BUỘC filter mọi query theo cột này',
    `category_id` INT UNSIGNED        NOT NULL,
    `type`        ENUM('income','expense') NOT NULL
                                      COMMENT 'income=IncomeTransaction | expense=ExpenseTransaction',
    `amount`      DECIMAL(15,2)       NOT NULL  COMMENT 'Luôn dương — type xác định chiều',
    `note`        VARCHAR(500)        NULL,
    `trans_date`  DATE                NOT NULL  COMMENT 'Ngày user nhập, không phải ngày tạo record',
    `created_at`  DATETIME            NOT NULL  DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME            NOT NULL  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,--Có được sửa không

    PRIMARY KEY (`id`),
    -- 3 composite index cho các query thường dùng nhất
    INDEX `idx_tx_user_date`     (`user_id`, `trans_date`),
    INDEX `idx_tx_user_type`     (`user_id`, `type`),
    INDEX `idx_tx_user_category` (`user_id`, `category_id`),

    CONSTRAINT `fk_tx_user`
        FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
    CONSTRAINT `fk_tx_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
        -- RESTRICT: không cho xoá danh mục đang có giao dịch
        -- CategoryController::destroy() phải bắt PDOException SQLSTATE 23000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tất cả giao dịch thu và chi của user';

-- ============================================================
-- Query mẫu — tham khảo khi viết TransactionRepository
-- ============================================================

-- findByType (TV3/TV4):
-- SELECT t.*, c.name as category_name, c.color as category_color
-- FROM transactions t JOIN categories c ON t.category_id = c.id
-- WHERE t.user_id = :uid AND t.type = :type
-- ORDER BY t.trans_date DESC LIMIT :limit OFFSET :offset;

-- getSumByCategory cho BudgetService::checkAlert():
-- SELECT COALESCE(SUM(amount), 0) FROM transactions
-- WHERE user_id = :uid AND category_id = :cid AND type = 'expense'
--   AND MONTH(trans_date) = :m AND YEAR(trans_date) = :y;

-- getSummaryByMonth cho FinanceReport::generateMonthly():
-- SELECT type, SUM(amount) as total FROM transactions
-- WHERE user_id = :uid AND MONTH(trans_date) = :m AND YEAR(trans_date) = :y
-- GROUP BY type;

-- getExpenseByCategory cho biểu đồ tròn Chart.js:
-- SELECT c.name, c.color, SUM(t.amount) as total
-- FROM transactions t JOIN categories c ON t.category_id = c.id
-- WHERE t.user_id = :uid AND t.type = 'expense'
--   AND MONTH(t.trans_date) = :m AND YEAR(t.trans_date) = :y
-- GROUP BY t.category_id ORDER BY total DESC;
