-- ============================================================
-- WALLET MIGRATION (Ngày 6) — Tính năng nhiều ví
-- Chạy SAU khi 000_run_core.sql đã chạy và bài cốt lõi xong
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

SOURCE 005_create_wallets_table.sql;
SOURCE 006_alter_transactions_add_wallet.sql;
SOURCE 007_create_transfers_table.sql;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Tính năng ví đã sẵn sàng! Tổng 7 bảng.' AS status;
SHOW TABLES;
