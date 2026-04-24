-- ============================================================
-- MIGRATION 007 — transfers (Ngày 6-7)
-- Chuyển tiền giữa ví — không phải thu, không phải chi
-- Chạy SAU migration 005
-- ============================================================

CREATE TABLE IF NOT EXISTS `transfers` (
    `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED   NOT NULL,
    `from_wallet_id` INT UNSIGNED   NOT NULL,
    `to_wallet_id`   INT UNSIGNED   NOT NULL,
    `amount`         DECIMAL(15,2)  NOT NULL,
    `note`           VARCHAR(500)   NULL,
    `transfer_date`  DATE           NOT NULL,
    `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_tr_user`   (`user_id`),
    INDEX `idx_tr_from`   (`from_wallet_id`),
    INDEX `idx_tr_to`     (`to_wallet_id`),

    CONSTRAINT `fk_tr_user` FOREIGN KEY (`user_id`)
        REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tr_from` FOREIGN KEY (`from_wallet_id`)
        REFERENCES `wallets`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tr_to`   FOREIGN KEY (`to_wallet_id`)
        REFERENCES `wallets`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `chk_tr_diff`
        CHECK (`from_wallet_id` != `to_wallet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Chuyển tiền giữa các ví';

-- TransferService phải dùng PDO::beginTransaction():
-- BEGIN → INSERT transfers → UPDATE balance ví A → UPDATE balance ví B → COMMIT
-- Nếu lỗi → ROLLBACK (tránh mất tiền 1 chiều)
