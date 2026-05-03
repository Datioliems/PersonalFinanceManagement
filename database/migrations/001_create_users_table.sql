-- ============================================================
-- TV1 — Migration: users + login_logs
-- Đề 13: Quản lý Tài chính Cá nhân
-- Chạy: mysql -u root -p de13_finance < 001_create_users_table.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `username`        VARCHAR(50)   NOT NULL,
    `email`           VARCHAR(150)  NOT NULL,
    `password_hash`   VARCHAR(255)  NOT NULL   COMMENT 'bcrypt — KHÔNG lưu plain text',
    `remember_token`  VARCHAR(64)   NULL       COMMENT 'SHA-256 hash của token trong cookie',
    `token_expires_at` DATETIME     NULL       COMMENT 'Token hết hạn sau 30 ngày',
    `is_active`       TINYINT(1)    NOT NULL   DEFAULT 1 COMMENT '0 = tài khoản bị khoá',
    `created_at`      DATETIME      NOT NULL   DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME      NOT NULL   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_username` (`username`),
    UNIQUE KEY `uq_users_email`    (`email`),
    INDEX `idx_users_remember`     (`remember_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tài khoản người dùng — TV1';

-- ============================================================
-- login_logs: ghi log mỗi lần đăng nhập (thành công + thất bại)
-- Dùng cho: tính năng nâng cao Ngày 6, brute-force detection
-- ============================================================
CREATE TABLE IF NOT EXISTS `login_logs` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NULL     COMMENT 'NULL nếu username không tồn tại',
    `ip_address` VARCHAR(45)  NOT NULL COMMENT 'IPv4 hoặc IPv6',
    `username`   VARCHAR(50)  NOT NULL COMMENT 'Ghi lại username đã nhập (kể cả sai)',
    `status`     ENUM('success','failed') NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_ll_user`   (`user_id`),
    INDEX `idx_ll_ip`     (`ip_address`),
    INDEX `idx_ll_time`   (`created_at`),
    CONSTRAINT `fk_ll_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Log đăng nhập — TV1';
