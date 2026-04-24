-- ============================================================
-- MIGRATION 006 — Gắn wallet_id vào transactions
-- Chạy SAU migration 005
-- ============================================================
-- QUAN TRỌNG: wallet_id cho phép NULL để không phá vỡ
-- các giao dịch cũ đã tạo ở Ngày 1-5 (trước khi có ví).
-- Service sẽ tự fill wallet_id = ví default nếu NULL.
-- ============================================================

ALTER TABLE `transactions`
    ADD COLUMN `wallet_id` INT UNSIGNED NULL
        COMMENT 'NULL = giao dịch trước khi có tính năng ví'
        AFTER `user_id`,
    ADD INDEX `idx_tx_user_wallet` (`user_id`, `wallet_id`),
    ADD CONSTRAINT `fk_tx_wallet`
        FOREIGN KEY (`wallet_id`) REFERENCES `wallets`(`id`)
        ON DELETE SET NULL;

-- Sau khi thêm ví, cập nhật giao dịch cũ vào ví default:
-- UPDATE transactions t
-- JOIN wallets w ON t.user_id = w.user_id AND w.is_default = 1
-- SET t.wallet_id = w.id
-- WHERE t.wallet_id IS NULL;
