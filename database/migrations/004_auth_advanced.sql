-- ============================================================
-- MIGRATION 004 — Auth nâng cao
-- Chạy SAU migration 001:
--   mysql -u root -p de13_finance < database/migrations/004_auth_advanced.sql
-- ============================================================

ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `email_verified`     TINYINT(1)  NOT NULL DEFAULT 0     AFTER `is_active`,
    ADD COLUMN IF NOT EXISTS `email_verify_token` VARCHAR(64) NULL                   AFTER `email_verified`,
    ADD COLUMN IF NOT EXISTS `login_attempts`     TINYINT     NOT NULL DEFAULT 0     AFTER `email_verify_token`,
    ADD COLUMN IF NOT EXISTS `locked_until`       DATETIME    NULL                   AFTER `login_attempts`;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `token_hash` VARCHAR(64)  NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_pr_token` (`token_hash`),
    CONSTRAINT `fk_pr_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột username vào login_logs nếu chưa có
ALTER TABLE `login_logs`
    ADD COLUMN IF NOT EXISTS `username` VARCHAR(50) NOT NULL DEFAULT '' AFTER `user_id`;

SELECT 'Migration 004 hoàn tất!' AS status;
