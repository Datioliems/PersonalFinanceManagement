-- ============================================================
-- TV2 — Migration: categories + budgets
-- Đề 13: Quản lý Tài chính Cá nhân
-- Chạy SAU 001_create_users_table.sql (cần bảng users trước)
-- ============================================================

-- ── Bảng categories ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED  NOT NULL  COMMENT 'Mỗi user có danh mục riêng',
    `name`       VARCHAR(100)  NOT NULL  COMMENT 'VD: Ăn uống, Đi lại, Lương',
    `type`       ENUM('income','expense','both') NOT NULL DEFAULT 'both'
                               COMMENT 'Giới hạn danh mục khi chọn form thu/chi',
    `icon`       VARCHAR(50)   NULL      COMMENT 'Bootstrap icon: bi-cup-hot, bi-car-front',
    `color`      VARCHAR(7)    NULL      COMMENT 'Hex cho Chart.js: #FF6384',
    `created_at` DATETIME      NOT NULL  DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX       `idx_cat_user`      (`user_id`),
    UNIQUE KEY  `uq_cat_name_user`  (`user_id`, `name`),
    CONSTRAINT  `fk_cat_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Danh mục thu/chi — TV2';

-- ── Bảng budgets ─────────────────────────────────────────────
-- Mỗi user đặt hạn mức cho từng danh mục mỗi tháng.
-- UNIQUE KEY đảm bảo 1 budget cho 1 danh mục/tháng → dùng được UPSERT.
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `budgets` (
    `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED   NOT NULL,
    `category_id`     INT UNSIGNED   NOT NULL,
    `limit_amount`    DECIMAL(15,2)  NOT NULL  COMMENT 'Hạn mức chi tối đa trong tháng',
    `alert_threshold` TINYINT        NOT NULL  DEFAULT 80
                                     COMMENT 'Cảnh báo khi chi >= X% hạn mức (mặc định 80%)',
    `month`           TINYINT(2)     NOT NULL  COMMENT '1–12',
    `year`            SMALLINT(4)    NOT NULL,
    `created_at`      DATETIME       NOT NULL  DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME       NOT NULL  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_budget_user_cat_month` (`user_id`, `category_id`, `month`, `year`),
    INDEX      `idx_budget_user_month`    (`user_id`, `month`, `year`),
    CONSTRAINT `fk_budget_user`
        FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
    CONSTRAINT `fk_budget_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Ngân sách tháng theo danh mục — TV2';

-- ============================================================
-- Seed danh mục mẫu (uncomment và thay user_id = 1 khi cần test)
-- ============================================================
-- INSERT INTO categories (user_id, name, type, icon, color) VALUES
-- (1, 'Ăn uống',       'expense', 'bi-cup-hot',    '#FF6384'),
-- (1, 'Đi lại',        'expense', 'bi-car-front',  '#36A2EB'),
-- (1, 'Giải trí',      'expense', 'bi-controller', '#FFCE56'),
-- (1, 'Hóa đơn',       'expense', 'bi-receipt',    '#4BC0C0'),
-- (1, 'Mua sắm',       'expense', 'bi-bag',        '#9966FF'),
-- (1, 'Y tế',          'expense', 'bi-heart-pulse', '#FF9F40'),
-- (1, 'Lương',         'income',  'bi-cash-coin',  '#4BC0C0'),
-- (1, 'Thu nhập khác', 'income',  'bi-plus-circle','#36A2EB');
