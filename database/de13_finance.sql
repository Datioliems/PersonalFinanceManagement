-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 06, 2026 lúc 11:13 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `de13_finance`
--
CREATE DATABASE IF NOT EXISTS `de13_finance` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `de13_finance`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `budgets`
--

CREATE TABLE IF NOT EXISTS `budgets` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `limit_amount` decimal(15,2) NOT NULL COMMENT 'Hạn mức chi tối đa trong tháng',
  `alert_threshold` tinyint(4) NOT NULL DEFAULT 80 COMMENT 'Cảnh báo khi chi >= X% hạn mức (mặc định 80%)',
  `month` tinyint(2) NOT NULL COMMENT '1–12',
  `year` smallint(4) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_budget_user_cat_month` (`user_id`,`category_id`,`month`,`year`),
  KEY `idx_budget_user_month` (`user_id`,`month`,`year`),
  KEY `fk_budget_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ngân sách tháng theo danh mục — TV2';

--
-- Đang đổ dữ liệu cho bảng `budgets`
--

INSERT INTO `budgets` (`id`, `user_id`, `category_id`, `limit_amount`, `alert_threshold`, `month`, `year`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 200000000.00, 40, 4, 2026, '2026-04-30 14:09:53', '2026-04-30 14:09:53'),
(2, 9, 15, 1000000000000.00, 80, 5, 2026, '2026-05-04 03:20:26', '2026-05-04 03:20:26'),
(3, 9, 13, 2000000.00, 80, 5, 2026, '2026-05-04 09:33:58', '2026-05-04 09:33:58'),
(4, 10, 16, 2000000.00, 85, 5, 2026, '2026-05-05 17:57:46', '2026-05-05 17:57:46'),
(5, 10, 17, 1800000.00, 50, 5, 2026, '2026-05-05 17:57:58', '2026-05-05 17:57:58'),
(7, 10, 23, 100000000.00, 80, 5, 2026, '2026-05-05 19:09:19', '2026-05-05 19:09:19'),
(8, 10, 23, 100000000.00, 80, 6, 2026, '2026-05-05 19:09:19', '2026-05-05 19:09:19'),
(9, 10, 23, 100000000.00, 80, 7, 2026, '2026-05-05 19:09:19', '2026-05-05 19:09:19'),
(10, 10, 23, 100000000.00, 80, 8, 2026, '2026-05-05 19:09:19', '2026-05-05 19:09:19'),
(11, 10, 23, 100000000.00, 80, 9, 2026, '2026-05-05 19:09:19', '2026-05-05 19:09:19'),
(12, 10, 23, 100000000.00, 80, 10, 2026, '2026-05-05 19:09:19', '2026-05-05 19:09:19'),
(13, 10, 23, 100000000.00, 80, 11, 2026, '2026-05-05 19:09:19', '2026-05-05 19:09:19'),
(14, 10, 23, 100000000.00, 80, 12, 2026, '2026-05-05 19:09:19', '2026-05-05 19:09:19'),
(15, 10, 22, 10000.00, 80, 5, 2026, '2026-05-05 22:10:12', '2026-05-05 22:10:12'),
(16, 10, 22, 10000.00, 80, 6, 2026, '2026-05-05 22:10:12', '2026-05-05 22:10:12'),
(17, 10, 22, 10000.00, 80, 7, 2026, '2026-05-05 22:10:12', '2026-05-05 22:10:12'),
(18, 10, 22, 10000.00, 80, 8, 2026, '2026-05-05 22:10:12', '2026-05-05 22:10:12'),
(19, 10, 22, 10000.00, 80, 9, 2026, '2026-05-05 22:10:12', '2026-05-05 22:10:12'),
(20, 10, 22, 10000.00, 80, 10, 2026, '2026-05-05 22:10:12', '2026-05-05 22:10:12'),
(21, 10, 22, 10000.00, 80, 11, 2026, '2026-05-05 22:10:12', '2026-05-05 22:10:12'),
(22, 10, 22, 10000.00, 80, 12, 2026, '2026-05-05 22:10:12', '2026-05-05 22:10:12'),
(24, 14, 28, 100.00, 80, 5, 2026, '2026-05-07 03:18:57', '2026-05-07 03:18:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'Mỗi user có danh mục riêng',
  `name` varchar(100) NOT NULL COMMENT 'VD: Ăn uống, Đi lại, Lương',
  `type` enum('income','expense','both') NOT NULL DEFAULT 'both' COMMENT 'Giới hạn danh mục khi chọn form thu/chi',
  `icon` varchar(50) DEFAULT NULL COMMENT 'Bootstrap icon: bi-cup-hot, bi-car-front',
  `color` varchar(7) DEFAULT NULL COMMENT 'Hex cho Chart.js: #FF6384',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_name_user` (`user_id`,`name`),
  KEY `idx_cat_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mục thu/chi — TV2';

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `name`, `type`, `icon`, `color`, `created_at`) VALUES
(1, 3, 'Ăn uống', 'expense', 'bi-arrow-90deg-up', '#6366f1', '2026-04-30 14:08:49'),
(2, 3, 'Đi lại', 'expense', NULL, '#a5ba08', '2026-04-30 14:09:29'),
(3, 3, 'Lương', 'income', NULL, '#6366f1', '2026-04-30 14:09:43'),
(4, 6, 'Ăn uống', 'expense', NULL, '#FF6384', '2026-04-30 14:39:20'),
(5, 6, 'Đi lại', 'expense', NULL, '#36A2EB', '2026-04-30 14:39:20'),
(6, 6, 'Giải trí', 'expense', NULL, '#FFCE56', '2026-04-30 14:39:20'),
(7, 6, 'Hóa đơn', 'expense', NULL, '#4BC0C0', '2026-04-30 14:39:20'),
(8, 6, 'Mua sắm', 'expense', NULL, '#9966FF', '2026-04-30 14:39:20'),
(9, 6, 'Lương', 'income', NULL, '#4BC0C0', '2026-04-30 14:39:20'),
(10, 3, 'Bán khóa học', 'income', 'bi-piggy-bank', '#6366f1', '2026-04-30 19:50:07'),
(11, 3, 'Thuê cơ sở mặt băng', 'expense', 'bi-building', '#16a34a', '2026-04-30 19:51:06'),
(13, 9, 'Ăn uống', 'expense', 'bi-stars', '#7c5a71', '2026-05-04 01:46:35'),
(14, 9, 'Lương', 'income', 'bi-laptop', '#6366f1', '2026-05-04 01:46:43'),
(15, 9, 'Alo', 'expense', 'bi-receipt-cutoff', '#9f1239', '2026-05-04 03:16:21'),
(16, 10, 'Ăn uống', 'expense', NULL, '#a21caf', '2026-05-05 17:57:00'),
(17, 10, 'Mua sắm', 'expense', NULL, '#1d4ed8', '2026-05-05 17:57:06'),
(18, 10, 'Lương', 'income', NULL, '#65a30d', '2026-05-05 17:57:12'),
(19, 10, 'Học Bổng', 'income', 'bi-apple', '#ca8a04', '2026-05-05 17:57:21'),
(20, 10, 'Từ thiện', 'expense', 'bi-stars', '#16a34a', '2026-05-05 18:01:20'),
(21, 10, 'Tặng quà', 'expense', 'bi-cake', '#ea580c', '2026-05-05 18:01:34'),
(22, 10, 'Trả nợ', 'expense', 'bi-cart3', '#0d9488', '2026-05-05 18:01:53'),
(23, 10, 'Đi lại', 'expense', NULL, '#be185d', '2026-05-05 18:05:29'),
(27, 14, 'Bán hàng', 'income', NULL, '#0d9488', '2026-05-05 22:35:06'),
(28, 14, 'Ăn uống', 'expense', NULL, '#a21caf', '2026-05-06 16:39:45'),
(29, 1, 'aa', 'expense', NULL, '#9f1239', '2026-05-06 19:39:26'),
(30, 1, 'aaa', 'income', 'bi-laptop', '#1d4ed8', '2026-05-06 19:39:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `login_logs`
--

CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL nếu username không tồn tại',
  `ip_address` varchar(45) NOT NULL COMMENT 'IPv4 hoặc IPv6',
  `username` varchar(50) NOT NULL COMMENT 'Ghi lại username đã nhập (kể cả sai)',
  `status` enum('success','failed') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ll_user` (`user_id`),
  KEY `idx_ll_ip` (`ip_address`),
  KEY `idx_ll_time` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log đăng nhập — TV1';

--
-- Đang đổ dữ liệu cho bảng `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `ip_address`, `username`, `status`, `created_at`) VALUES
(1, NULL, '::1', 'Adius', 'failed', '2026-04-30 02:54:08'),
(2, NULL, '::1', 'Adius', 'failed', '2026-04-30 03:07:31'),
(3, 3, '::1', 'Khoado', 'failed', '2026-04-30 03:18:46'),
(4, 3, '::1', 'Khoado', 'success', '2026-04-30 03:19:11'),
(5, NULL, '::1', 'coockikim@gmail.com', 'failed', '2026-04-30 03:20:43'),
(6, NULL, '::1', 'Adius', 'failed', '2026-04-30 03:20:49'),
(7, NULL, '::1', 'coockikim@gmail.com', 'failed', '2026-04-30 03:24:51'),
(8, NULL, '::1', 'coockikim@gmail.com', 'failed', '2026-04-30 03:25:15'),
(9, NULL, '::1', 'coockikim@gmail.com', 'failed', '2026-04-30 03:25:16'),
(10, NULL, '::1', 'Adius', 'failed', '2026-04-30 03:25:22'),
(11, NULL, '::1', 'Adius', 'failed', '2026-04-30 03:25:24'),
(12, 5, '::1', 'wangchuk_508', 'failed', '2026-04-30 14:07:01'),
(13, NULL, '::1', 'Adius', 'failed', '2026-04-30 14:08:22'),
(14, NULL, '::1', 'Ngonlu124', 'failed', '2026-04-30 14:08:25'),
(15, 3, '::1', 'Khoado', 'success', '2026-04-30 14:08:27'),
(16, 3, '::1', 'Khoado', 'success', '2026-04-30 19:47:55'),
(17, 9, '::1', 'PhucNgu', 'success', '2026-05-03 22:05:35'),
(18, 9, '::1', 'PhucNgu', 'success', '2026-05-04 01:46:17'),
(19, NULL, '::1', 'admin', 'failed', '2026-05-04 03:19:03'),
(20, NULL, '::1', 'admin', 'failed', '2026-05-04 03:19:05'),
(21, 9, '::1', 'PhucNgu', 'success', '2026-05-04 03:20:00'),
(22, NULL, '::1', 'Ngonlu124', 'failed', '2026-05-04 09:33:13'),
(23, NULL, '::1', 'Adius', 'failed', '2026-05-04 09:33:18'),
(24, 9, '::1', 'PhucNgu', 'success', '2026-05-04 09:33:41'),
(25, 9, '::1', 'PhucNgu', 'success', '2026-05-05 12:57:15'),
(26, 9, '::1', 'PhucNgu', 'success', '2026-05-05 17:52:53'),
(27, 10, '::1', 'Adis', 'failed', '2026-05-05 17:55:55'),
(28, 10, '::1', 'Adis', 'failed', '2026-05-05 17:55:57'),
(29, 10, '::1', 'Adis', 'success', '2026-05-05 17:56:32'),
(30, 14, '::1', 'admin', 'success', '2026-05-05 22:33:48'),
(31, 14, '::1', 'admin', 'success', '2026-05-05 22:34:59'),
(32, 14, '::1', 'admin', 'success', '2026-05-05 23:09:33'),
(33, 14, '::1', 'admin', 'success', '2026-05-05 23:11:19'),
(34, 14, '::1', 'admin', 'failed', '2026-05-05 23:38:32'),
(35, 14, '::1', 'admin', 'failed', '2026-05-05 23:38:39'),
(36, 14, '::1', 'admin', 'failed', '2026-05-05 23:38:47'),
(37, 14, '::1', 'admin', 'failed', '2026-05-05 23:38:51'),
(38, 14, '::1', 'admin', 'success', '2026-05-06 16:38:56'),
(39, 14, '::1', 'admin', 'success', '2026-05-06 16:39:20'),
(40, 17, '::1', 'HoaiKhon', 'failed', '2026-05-06 19:27:47'),
(41, NULL, '::1', 'PhucNgu', 'failed', '2026-05-06 19:27:50'),
(42, 14, '::1', 'admin', 'success', '2026-05-06 19:27:52'),
(43, 14, '::1', 'admin', 'success', '2026-05-06 19:39:58'),
(44, 14, '::1', 'admin', 'success', '2026-05-06 23:36:27'),
(45, 14, '::1', 'admin', 'success', '2026-05-06 23:45:27'),
(46, 14, '::1', 'admin', 'failed', '2026-05-06 23:46:31'),
(47, 14, '::1', 'admin', 'success', '2026-05-06 23:46:42'),
(48, 14, '::1', 'admin', 'success', '2026-05-06 23:46:58'),
(49, 14, '::1', 'admin', 'success', '2026-05-07 03:07:29'),
(50, 14, '::1', 'admin', 'success', '2026-05-07 03:53:10'),
(51, NULL, '::1', 'Adius', 'failed', '2026-05-07 03:53:28'),
(52, NULL, '::1', 'Khoado', 'failed', '2026-05-07 03:53:33'),
(53, NULL, '::1', 'PhucNgu', 'failed', '2026-05-07 03:53:36'),
(54, NULL, '::1', 'Ngonlu124', 'failed', '2026-05-07 03:53:41'),
(55, 5, '::1', 'wangchuk_508', 'failed', '2026-05-07 03:53:44'),
(56, 14, '::1', 'admin', 'success', '2026-05-07 03:53:49'),
(57, 14, '::1', 'admin', 'success', '2026-05-07 03:54:29'),
(58, 19, '::1', 'DatNg', 'success', '2026-05-07 03:55:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pr_token` (`token_hash`),
  KEY `fk_pr_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used`, `created_at`) VALUES
(2, 14, 'a9b14e950a6d5a547bf3717939ed9fad3114201fe3f5af5613c9598732154a2c', '2026-05-05 23:40:23', 1, '2026-05-05 23:10:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'BẮT BUỘC filter mọi query theo cột này',
  `category_id` int(10) UNSIGNED NOT NULL,
  `type` enum('income','expense') NOT NULL COMMENT 'income=IncomeTransaction | expense=ExpenseTransaction',
  `amount` decimal(15,2) NOT NULL COMMENT 'Luôn dương — type xác định chiều tiền',
  `note` varchar(500) DEFAULT NULL,
  `trans_date` date NOT NULL COMMENT 'Ngày user nhập — không phải created_at',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tx_user_date` (`user_id`,`trans_date`),
  KEY `idx_tx_user_type` (`user_id`,`type`),
  KEY `idx_tx_user_category` (`user_id`,`category_id`),
  KEY `fk_tx_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tất cả giao dịch thu và chi — TV3+TV4';

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `category_id`, `type`, `amount`, `note`, `trans_date`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'expense', 15000000.00, '', '2026-04-17', '2026-04-30 14:10:17', '2026-04-30 14:10:17'),
(2, 6, 9, 'income', 15000000.00, 'Lương tháng 4', '2026-04-01', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(3, 6, 4, 'expense', 150000.00, 'Ăn trưa', '2026-04-02', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(4, 6, 5, 'expense', 200000.00, 'Taxi', '2026-04-03', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(5, 6, 4, 'expense', 300000.00, 'Ăn cơm nhóm', '2026-04-05', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(6, 6, 6, 'expense', 500000.00, 'Xem phim', '2026-04-06', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(7, 6, 4, 'expense', 250000.00, 'Ăn tối', '2026-04-08', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(8, 6, 7, 'expense', 1000000.00, 'Tiền điện', '2026-04-10', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(9, 6, 8, 'expense', 2000000.00, 'Mua quần áo', '2026-04-12', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(10, 6, 4, 'expense', 400000.00, 'Nhà hàng', '2026-04-15', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(11, 6, 5, 'expense', 150000.00, 'Xăng xe', '2026-04-18', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(12, 6, 6, 'expense', 300000.00, 'Game', '2026-04-20', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(13, 6, 4, 'expense', 500000.00, 'Ăn ngoài', '2026-04-25', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(14, 6, 7, 'expense', 500000.00, 'Internet', '2026-04-28', '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(16, 3, 3, 'income', 100000.00, '', '2026-04-30', '2026-04-30 19:46:43', '2026-04-30 19:46:43'),
(17, 3, 10, 'income', 140000000.00, '', '2026-04-30', '2026-04-30 19:47:02', '2026-04-30 19:50:47'),
(18, 3, 1, 'expense', 1000000.00, 'Mua nước ngọt cho ai', '2026-04-30', '2026-04-30 19:50:39', '2026-04-30 19:50:39'),
(19, 3, 11, 'expense', 80000000.00, '', '2026-04-30', '2026-04-30 19:51:19', '2026-04-30 19:51:27'),
(20, 3, 11, 'expense', 90000000.00, '', '2026-03-24', '2026-04-30 19:52:14', '2026-04-30 19:52:14'),
(21, 3, 10, 'income', 80000000.00, '', '2026-03-20', '2026-04-30 19:52:25', '2026-04-30 19:52:25'),
(22, 9, 15, 'expense', 1000000.00, '', '2026-05-04', '2026-05-04 03:20:52', '2026-05-04 03:21:11'),
(23, 9, 14, 'income', 100000.00, '', '2026-05-04', '2026-05-04 09:45:31', '2026-05-04 09:45:31'),
(24, 10, 19, 'income', 15900000.00, '', '2026-03-31', '2026-05-05 17:58:26', '2026-05-05 17:58:26'),
(25, 10, 18, 'income', 1000000.00, '', '2026-05-05', '2026-05-05 17:59:18', '2026-05-05 17:59:18'),
(26, 10, 22, 'expense', 2500000.00, 'Trả nợ mẹ hehehe', '2026-04-09', '2026-05-05 18:03:07', '2026-05-05 18:04:30'),
(27, 10, 21, 'expense', 2000000.00, '', '2026-05-05', '2026-05-05 18:04:53', '2026-05-05 18:04:53'),
(28, 10, 23, 'expense', 50000.00, '', '2026-05-05', '2026-05-05 18:05:40', '2026-05-05 18:05:40'),
(29, 14, 28, 'expense', 1000000000.00, '', '2026-05-06', '2026-05-06 16:40:12', '2026-05-06 16:40:12'),
(30, 14, 27, 'income', 1000000.00, '', '2026-05-07', '2026-05-07 03:19:32', '2026-05-07 03:19:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'bcrypt — KHÔNG lưu plain text',
  `remember_token` varchar(64) DEFAULT NULL COMMENT 'SHA-256 hash của token trong cookie',
  `token_expires_at` datetime DEFAULT NULL COMMENT 'Token hết hạn sau 30 ngày',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = tài khoản bị khoá',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verify_token` varchar(64) DEFAULT NULL,
  `login_attempts` tinyint(4) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_remember` (`remember_token`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tài khoản người dùng — TV1';

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `remember_token`, `token_expires_at`, `is_active`, `email_verified`, `email_verify_token`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'guest', 'guest@localhost', '$2y$10$jtxhIaAJNW/35UXwqp0eoumoq.yiCixCVe0UvmYlILlew4CgIaq3G', NULL, NULL, 1, 1, NULL, 0, NULL, '2026-05-05 22:22:17', '2026-05-06 19:24:42'),
(3, 'Dat', 'datng2809@gmail.com', '$2y$10$q3Y8hPXmy6j2Vu7HbW6Id.FQ0jqF.yK01o/eM1tF3UqP/WZFDZLka', NULL, NULL, 1, 1, NULL, 0, NULL, '2026-04-30 03:17:45', '2026-05-07 03:52:10'),
(4, 'Khoado11', 'coockikim@gmail.com', '$2y$10$5AgveD3qAq9EzAcV8D.dcOT4rC4jIOT8t.RWeMgd6MA5FGe8Sej7u', NULL, NULL, 1, 0, '7de407e80f82c26155b557da44a56aa89bf5a8a7b3e6a432c76f324bc64ede75', 0, NULL, '2026-04-30 03:25:07', '2026-04-30 03:25:07'),
(5, 'wangchuk_508', 'hello@gmail.com', '$2y$10$ntBRapLFCvqhFhnvO0lHw.QyTCyFji.61898RfAhPLQxy3SNnxFGe', NULL, NULL, 1, 0, 'fe9c6cb347a6c832ea696a1f9c2ec27b70566c98ef5bfd46afd9feb1ed6cc524', 0, NULL, '2026-04-30 14:06:17', '2026-04-30 14:06:17'),
(6, 'test', 'test@example.com', '$2y$10$YpHDeU.LiC68uOVqoU7GjuZkDxi8YlmFe6/4qI4Jcym3f.SszyCFG', NULL, NULL, 1, 0, NULL, 0, NULL, '2026-04-30 14:39:20', '2026-04-30 14:39:20'),
(7, 'Hang123', 'viethang82005@gmail.com', '$2y$10$6qulzTCy5dImnjUrDHywb.JZBaHTnBp.4RmagKMrLT1/fEA5Utd5C', NULL, NULL, 1, 0, '7c8d5b33a33fc2b715fd8fa6de38936805fe244b56e34cf3dd755f3d7d57f3c3', 0, NULL, '2026-05-03 21:10:24', '2026-05-03 21:10:24'),
(8, 'testuser517483654', 'test1828770261@test.com', '$2y$10$Z5jwjlCNC04sYrA8roffFOSd8ApTmldMS42nd7p6D2kyx2Ci4Fc1S', NULL, NULL, 1, 0, '7f27840aadb085d1f4f4be9fda66d3bca5bea362a101a2748ac53fbe07641bad', 0, NULL, '2026-05-03 21:58:32', '2026-05-03 21:58:32'),
(9, 'Phuc', 'phuc@gmail.com', '$2y$10$.bNtezlXzZplllYHSvOIG.HURdZqX809MKnVSb7EgD8LI35csib.K', NULL, NULL, 1, 1, NULL, 0, NULL, '2026-05-03 22:03:47', '2026-05-05 22:32:35'),
(10, 'Adis', 'tuandat2809@gmail.com', '$2y$10$QHTTM2KZ297.Xwac7KyD8uaJMjMWdikQi4dmP4/XqhXHZ6VUe7Fom', NULL, NULL, 1, 1, NULL, 0, NULL, '2026-05-05 17:55:46', '2026-05-07 03:52:27'),
(14, 'admin', 'phuc80888@gmail.com', '$2y$10$jGAJjQSq/lF8B7gq6sWqmOzKRgtaNXRqE6jjd1BG.gZMmDBOMnfcW', NULL, NULL, 1, 1, NULL, 0, NULL, '2026-05-05 22:33:12', '2026-05-07 03:54:55'),
(16, 'Penhoxday', 'alon@gmail.com', '$2y$10$cU/WQirQzpNCv3L5BpKqrOflQL46CpBs0NNOREAODVFlu3tiPxOr6', NULL, NULL, 1, 0, '1b51658a3455087ad6d3c6127f12d413ca017dee8a56dd8bed1e30337e431622', 0, NULL, '2026-05-05 23:13:37', '2026-05-05 23:13:37'),
(17, 'HoaiKhon', 'thuhoai2612005@gmail.com', '$2y$10$uxiv1MzR00R76XAOEWO4Ne8r/z6EJZh5uN0unLVuoZilTfQPrJtFq', NULL, NULL, 1, 0, '4213a60f66d96e3c1fae612c844cdd12e3f7110c093e6e3ed385ad1d26c652d8', 0, NULL, '2026-05-05 23:16:22', '2026-05-05 23:16:22'),
(18, 'gues', 'guet@localhost', '$2y$10$jtxhIaAJNW/35UXwqp0eoumoq.yiCixCVe0UvmYlILlew4CgIaq3G', NULL, NULL, 1, 1, NULL, 0, NULL, '2026-05-05 22:22:17', '2026-05-05 22:22:17'),
(19, 'DatNg', 'tuandat28092005@gmail.com', '$2y$10$G1feI0BYxpj8q0yCDvGzau4jMY9yjvMom9I1ZQb5o/T.fTkD9z9KC', NULL, NULL, 1, 1, NULL, 0, NULL, '2026-05-07 03:55:24', '2026-05-07 03:55:45');

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `fk_budget_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_budget_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_cat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `fk_ll_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_tx_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
