-- ============================================================
-- 005_create_guest_user.sql
-- ============================================================
-- Tạo 1 user "guest" cố định trong DB với id = 1.
-- Dùng khi AUTH_ENABLED=false — tất cả giao dịch/danh mục
-- gắn vào user này thay vì user_id=0 (tránh lỗi FK).
--
-- Chạy SAU 001_create_users_table.sql
-- ============================================================

INSERT IGNORE INTO `users`
    (`id`, `username`, `email`, `password_hash`,
     `email_verified`, `is_active`, `email_verify_token`)
VALUES
    (1, 'guest', 'guest@localhost',
     -- password_hash('guest_no_login', PASSWORD_BCRYPT) — không dùng để đăng nhập
     '$2y$12$GuestAccountXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
     1, 1, NULL);

-- Đảm bảo auto_increment không đụng vào id=1
ALTER TABLE `users` AUTO_INCREMENT = 2;
