-- ============================================================
-- MIGRATION 000 — Chạy toàn bộ schema (master file)
-- ============================================================
-- Cách chạy:
--   1. Tạo database:
--      mysql -u root -p -e "CREATE DATABASE de13_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
--
--   2. Chạy từng file theo thứ tự:
--      cd database/migrations
--      mysql -u root -p de13_finance < 001_create_users_table.sql
--      mysql -u root -p de13_finance < 002_create_categories_budgets.sql
--      mysql -u root -p de13_finance < 003_create_transactions.sql
--
-- Hoặc chạy cả 3 một lần:
--      mysql -u root -p de13_finance -e "
--        SOURCE 001_create_users_table.sql;
--        SOURCE 002_create_categories_budgets.sql;
--        SOURCE 003_create_transactions.sql;
--      "
--
-- THỨ TỰ BẮT BUỘC (FK constraints):
--   users → categories → transactions
--          → budgets   (cũng trong file 002)
-- ============================================================

SET NAMES utf8mb4;

SELECT 'Chạy migration 001: users...' AS status;
SOURCE 001_create_users_table.sql;

SELECT 'Chạy migration 002: categories + budgets...' AS status;
SOURCE 002_create_categories_budgets.sql;

SELECT 'Chạy migration 003: transactions...' AS status;
SOURCE 003_create_transactions.sql;

SELECT 'Migration hoàn tất!' AS status;
SHOW TABLES;
