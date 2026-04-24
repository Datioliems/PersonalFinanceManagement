-- ============================================================
-- MIGRATION 002 — categories
-- Chạy SAU 001
-- ============================================================

CREATE TABLE IF NOT EXISTS `categories` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED  NOT NULL  COMMENT 'Mỗi user có danh mục riêng',
    `name`       VARCHAR(100)  NOT NULL  COMMENT 'VD: Ăn uống, Đi lại, Lương',
    `type`       ENUM('income','expense','both') NOT NULL DEFAULT 'both'
                               COMMENT 'Giới hạn danh mục xuất hiện ở form thu/chi',
    `icon`       VARCHAR(50)   NULL      COMMENT 'Bootstrap icon: bi-cart, bi-car-front...',
    `color`      VARCHAR(7)    NULL      COMMENT 'Hex cho Chart.js: #FF6384',
    `created_at` DATETIME      NOT NULL  DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_cat_user` (`user_id`),
    UNIQUE KEY `uq_cat_name_user` (`user_id`, `name`),
    CONSTRAINT `fk_cat_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Danh mục thu/chi — mỗi user tự quản lý';

-- Seed danh mục mẫu (chạy sau khi đã có user test id=1)
-- INSERT INTO categories (user_id, name, type, icon, color) VALUES
-- (1,'Ăn uống','expense','bi-cup-hot','#FF6384'),
-- (1,'Đi lại','expense','bi-car-front','#36A2EB'),
-- (1,'Giải trí','expense','bi-controller','#FFCE56'),
-- (1,'Hóa đơn','expense','bi-receipt','#4BC0C0'),
-- (1,'Mua sắm','expense','bi-bag','#9966FF'),
-- (1,'Lương','income','bi-cash-coin','#4BC0C0'),
-- (1,'Thu nhập khác','income','bi-plus-circle','#FF9F40');
