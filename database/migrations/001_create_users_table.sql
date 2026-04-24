-- ============================================================
-- MIGRATION 001 — users + login_logs
-- Đề 13: Quản lý Tài chính Cá nhân (1 ví duy nhất)
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `username`       VARCHAR(50)   NOT NULL,
    `email`          VARCHAR(150)  NOT NULL,
    `password_hash`  VARCHAR(255)  NOT NULL   COMMENT 'bcrypt — KHÔNG lưu plain text',
    `remember_token` VARCHAR(64)   NULL       COMMENT 'Token cho Remember Me (Ngày 6)',
    `token_expires_at` DATETIME,
    -- `is_active`      TINYINT(1)    NOT NULL   DEFAULT 1,
    `created_at`     DATETIME      NOT NULL   DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME      NOT NULL   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_username` (`username`),
    UNIQUE KEY `uq_users_email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tài khoản người dùng';

-- Ghi log đăng nhập (Ngày 6 — nâng cao)
CREATE TABLE IF NOT EXISTS `login_logs` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NULL     COMMENT 'NULL nếu username không tồn tại',
    `ip_address` VARCHAR(45)  NOT NULL,
    `status`     ENUM('success','failed') NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_ll_user` (`user_id`),
    CONSTRAINT `fk_ll_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
