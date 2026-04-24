-- ============================================================
-- MASTER MIGRATION — Đề 13: Quản lý Tài chính Cá nhân
-- 4 bảng, 1 ví duy nhất (đúng theo yêu cầu đề bài)
-- ============================================================
-- Cách chạy:
--   1. Tạo DB:
--      mysql -u root -p -e "CREATE DATABASE de13_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
--   2. Chạy migration:
--      cd database/migrations
--      mysql -u root -p de13_finance < 000_run_all.sql
--
-- Thứ tự bắt buộc (FK):
--   users → categories → transactions
--                      → budgets
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

SOURCE 001_create_users_table.sql;
SOURCE 002_create_categories_table.sql;
SOURCE 003_create_transactions_table.sql;
SOURCE 004_create_budgets_table.sql;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migration hoàn tất! 4 bảng đã sẵn sàng.' AS status;
SHOW TABLES;
