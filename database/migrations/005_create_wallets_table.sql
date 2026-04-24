-- ============================================================
-- MIGRATION 005 — wallets (TÍNH NĂNG NÂNG CAO — Ngày 6)
-- Chạy SAU khi 001–004 đã chạy thành công
-- ============================================================
-- Chiến lược B1:
--   Ngày 1-5: chạy 000_run_core.sql (4 bảng cốt lõi)
--   Ngày 6:   chạy file này để thêm tính năng ví
--   Ngày 6:   chạy 006_alter_transactions_add_wallet.sql để gắn ví vào giao dịch
-- ============================================================

CREATE TABLE IF NOT EXISTS `wallets` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NOT NULL,
    `name`       VARCHAR(100)    NOT NULL  COMMENT 'VD: Tiền mặt, VCB, MoMo',
    `type`       ENUM('cash','bank','e_wallet','credit') NOT NULL DEFAULT 'cash',
    `balance`    DECIMAL(15,2)   NOT NULL  DEFAULT 0.00
                                 COMMENT 'Số dư — cập nhật mỗi khi có giao dịch',
    `currency`   VARCHAR(3)      NOT NULL  DEFAULT 'VND',
    `color`      VARCHAR(7)      NULL      COMMENT 'Hex cho UI',
    `icon`       VARCHAR(50)     NULL      COMMENT 'Bootstrap icon name',
    `is_default` TINYINT(1)      NOT NULL  DEFAULT 0
                                 COMMENT '1 = ví được chọn sẵn trong form',
    `is_active`  TINYINT(1)      NOT NULL  DEFAULT 1,
    `created_at` DATETIME        NOT NULL  DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME        NOT NULL  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_wallets_user` (`user_id`),
    CONSTRAINT `fk_wallets_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Ví tài chính — tính năng nâng cao Ngày 6';
