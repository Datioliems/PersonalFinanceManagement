-- ============================================================
-- CORE MIGRATION (Ngày 1) — 4 bảng cốt lõi
-- Chạy file này trước để bắt đầu làm bài
-- ============================================================
-- Sau khi hoàn thành yêu cầu đề bài (Ngày 1-5),
-- chạy tiếp 000_run_wallet.sql để thêm tính năng ví (Ngày 6)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

SOURCE 001_create_users_table.sql;
SOURCE 002_create_categories_table.sql;
SOURCE 003_create_transactions_table.sql;
SOURCE 004_create_budgets_table.sql;

SET FOREIGN_KEY_CHECKS = 1;

SELECT '4 bảng cốt lõi đã sẵn sàng!' AS status;
SHOW TABLES;
