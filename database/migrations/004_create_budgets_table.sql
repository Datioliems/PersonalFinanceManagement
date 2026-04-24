-- ============================================================
-- MIGRATION 004 — budgets
-- Chạy SAU 002
-- Đề bài yêu cầu: đặt ngân sách theo danh mục/tháng, cảnh báo vượt
-- ============================================================

CREATE TABLE IF NOT EXISTS `budgets` (
    `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED   NOT NULL,
    `category_id`     INT UNSIGNED   NOT NULL,
    `limit_amount`    DECIMAL(15,2)  NOT NULL  COMMENT 'Hạn mức chi tối đa trong tháng',
    `alert_threshold` TINYINT        NOT NULL  DEFAULT 80
                                     COMMENT 'Cảnh báo khi chi >= X% hạn mức. Mặc định 80%.',
    `month`           TINYINT(2)     NOT NULL  COMMENT '1-12',
    `year`            SMALLINT(4)    NOT NULL,
    `created_at`      DATETIME       NOT NULL  DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME       NOT NULL  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    -- Mỗi user chỉ có 1 budget cho 1 danh mục mỗi tháng
    UNIQUE KEY `uq_budget_user_cat_month` (`user_id`, `category_id`, `month`, `year`),
    INDEX `idx_budget_user_month` (`user_id`, `month`, `year`),

    CONSTRAINT `fk_budget_user`
        FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
    CONSTRAINT `fk_budget_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Ngân sách tháng theo danh mục';

-- ============================================================
-- INSERT ... ON DUPLICATE KEY UPDATE (upsert — BudgetRepository::upsert):
-- INSERT INTO budgets (user_id, category_id, limit_amount, alert_threshold, month, year)
-- VALUES (:uid, :cid, :limit, :threshold, :m, :y)
-- ON DUPLICATE KEY UPDATE
--   limit_amount = VALUES(limit_amount),
--   alert_threshold = VALUES(alert_threshold);

-- Lấy budget + số đã chi trong tháng (BudgetController::index):
-- SELECT b.*, c.name as category_name, c.color,
--        COALESCE((
--            SELECT SUM(amount) FROM transactions
--            WHERE user_id = b.user_id AND category_id = b.category_id
--              AND type = 'expense'
--              AND MONTH(trans_date) = b.month AND YEAR(trans_date) = b.year
--        ), 0) as spent
-- FROM budgets b JOIN categories c ON b.category_id = c.id
-- WHERE b.user_id = :uid AND b.month = :m AND b.year = :y;
-- ============================================================
