-- ============================================================
-- HỆ THỐNG QUẢN LÝ VĂN BẢN CÁ NHÂN
-- DATABASE SQL DUMP
-- Database Name: mjhhuxsyhosting_qlvb
-- Host: localhost
-- User: mjhhuxsyhosting_admin
-- Password: Admin@123
-- Compatibility: MySQL 5.7+ / MariaDB 10.3+ / PHP 8.4 PDO
-- ============================================================

CREATE DATABASE IF NOT EXISTS `mjhhuxsyhosting_qlvb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mjhhuxsyhosting_qlvb`;

-- ------------------------------------------------------------
-- 1. BẢNG NGUỜI DÙNG (users)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `progress_logs`;
DROP TABLE IF EXISTS `documents`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` ENUM('Admin', 'User') NOT NULL DEFAULT 'User',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. BẢNG DANH MỤC VĂN BẢN (categories)
-- ------------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. BẢNG VĂN BẢN (documents)
-- ------------------------------------------------------------
CREATE TABLE `documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `doc_number` VARCHAR(100) NOT NULL,
  `summary` TEXT DEFAULT NULL,
  `auto_summary` TEXT DEFAULT NULL,
  `doc_type` ENUM('luu', 'thuc_hien') NOT NULL DEFAULT 'luu',
  `status` ENUM('chua_xu_ly', 'dang_thuc_hien', 'hoan_thanh') NOT NULL DEFAULT 'chua_xu_ly',
  `progress` INT NOT NULL DEFAULT 0,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `file_type` VARCHAR(50) DEFAULT NULL,
  `file_size` INT DEFAULT 0,
  `issued_date` DATE DEFAULT NULL,
  `due_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_docs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_docs_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. BẢNG NHẬT KÝ TIẾN ĐỘ (progress_logs)
-- ------------------------------------------------------------
CREATE TABLE `progress_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `old_progress` INT DEFAULT 0,
  `new_progress` INT DEFAULT 0,
  `old_status` VARCHAR(50) DEFAULT NULL,
  `new_status` VARCHAR(50) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_logs_doc` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DỮ LIỆU MẪU (SEED DATA)
-- Mật khẩu mặc định: Admin@123 (Mã hóa bcrypt)
-- ------------------------------------------------------------

-- Insert Users ($2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi = Admin@123)
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`) VALUES
(1, 'admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1e8hGq1r0Fj2x5B4g0v3a8d1c7e9b0W', 'Quản Trị Viên (Admin)', 'admin@qlvb.vn', 'Admin'),
(2, 'nguyenvana', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1e8hGq1r0Fj2x5B4g0v3a8d1c7e9b0W', 'Nguyễn Văn A', 'nguyenvana@qlvb.vn', 'User'),
(3, 'tranthib', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1e8hGq1r0Fj2x5B4g0v3a8d1c7e9b0W', 'Trần Thị B', 'tranthib@qlvb.vn', 'User');

-- Insert Categories
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Quyết định / Chỉ thị', 'Các quyết định hành chính, chỉ thị điều hành công việc'),
(2, 'Thông báo / Công văn', 'Công văn đi, công văn đến, thông báo nội bộ'),
(3, 'Kế hoạch / Báo cáo', 'Báo cáo định kỳ, kế hoạch công tác quý/năm'),
(4, 'Hợp đồng / Biên bản', 'Hợp đồng kinh tế, biên bản cuộc họp và thỏa thuận');

-- Insert Sample Documents
INSERT INTO `documents` (`id`, `user_id`, `category_id`, `title`, `doc_number`, `summary`, `auto_summary`, `doc_type`, `status`, `progress`, `file_path`, `file_type`, `file_size`, `issued_date`, `due_date`, `created_at`) VALUES
(1, 1, 1, 'Quyết định ban hành Quy chế Làm việc mới năm 2026', '124/QĐ-UBND', 'Quyết định áp dụng quy chế làm việc kết hợp làm việc từ xa và tại văn phòng cho toàn bộ nhân sự.', 'Căn cứ Luật Quản lý Hành chính. Ban hành quy chế làm việc mới áp dụng từ tháng 8 năm 2026. Tất cả các đơn vị có trách nhiệm thi hành nghiêm túc quy chế này.', 'luu', 'hoan_thanh', 100, 'uploads/124_QD_UBND.pdf', 'pdf', 1048576, '2026-08-01', '2026-08-05', '2026-08-01 08:30:00'),

(2, 2, 3, 'Kế hoạch Triển khai Nâng cấp Hệ thống CNTT Quý 3/2026', '45/KH-CNTT', 'Chi tiết kế hoạch bảo trì máy chủ, nâng cấp hạ tầng mạng và kiểm tra an toàn thông tin.', 'Kế hoạch nâng cấp hệ thống máy chủ cơ sở dữ liệu và hạ tầng mạng LAN. Thời gian thực hiện từ ngày 15/08 đến 30/08/2026. Phân công đội ngũ an ninh mạng chịu trách nhiệm túc trực 24/7.', 'thuc_hien', 'dang_thuc_hien', 65, 'uploads/45_KH_CNTT.docx', 'docx', 2097152, '2026-08-02', '2026-08-25', '2026-08-02 09:15:00'),

(3, 2, 2, 'Công văn v/v Chuẩn bị Báo cáo Kiểm toán Tài chính', '89/CV-KT', 'Yêu cầu các phòng ban rà soát chứng từ, lập báo cáo thu chi trình Ban Giám đốc.', 'Công văn yêu cầu hoàn thiện toàn bộ hồ sơ sổ sách kế toán trước ngày 20/08/2026. Các phòng ban phối hợp chặt chẽ với bộ phận Tài chính Kế toán.', 'thuc_hien', 'chua_xu_ly', 0, 'uploads/89_CV_KT.pdf', 'pdf', 524288, '2026-08-05', '2026-08-20', '2026-08-05 14:00:00'),

(4, 3, 4, 'Biên bản Nghiệm thu Hợp đồng Cung cấp Thiết bị Văn phòng', '12/BB-NT', 'Biên bản kiểm tra số lượng và chất lượng máy in, máy chiếu mới bàn giao.', 'Đã tiến hành nghiệm thu 15 máy in đa năng và 5 máy chiếu HD. Thiết bị hoạt động ổn định và đáp ứng đầy đủ thông số kỹ thuật theo hợp đồng.', 'luu', 'hoan_thanh', 100, 'uploads/12_BB_NT.pdf', 'pdf', 838860, '2026-08-06', NULL, '2026-08-06 11:20:00'),

(5, 2, 3, 'Báo cáo Đánh giá Tiến độ Dự án Quản lý Văn bản Cá nhân', '102/BC-QLVB', 'Báo cáo tình hình thiết kế cơ sở dữ liệu, xây dựng module Tóm tắt TF-IDF và xem file trực tuyến.', 'Hệ thống đã hoàn thiện 80% tính năng chính bao gồm phân quyền, thống kê Chart.js và trích xuất tóm tắt văn bản. Dự kiến hoàn thành nghiệm thu đúng hạn.', 'thuc_hien', 'dang_thuc_hien', 80, 'uploads/102_BC_QLVB.docx', 'docx', 1572864, '2026-08-08', '2026-08-18', '2026-08-08 16:45:00');

-- Insert Progress Logs
INSERT INTO `progress_logs` (`id`, `document_id`, `user_id`, `old_progress`, `new_progress`, `old_status`, `new_status`, `note`, `created_at`) VALUES
(1, 2, 2, 0, 30, 'chua_xu_ly', 'dang_thuc_hien', 'Khởi tạo kế hoạch và phân công nhân sự khảo sát hạ tầng', '2026-08-03 10:00:00'),
(2, 2, 2, 30, 65, 'dang_thuc_hien', 'dang_thuc_hien', 'Hoàn thành việc cấu hình máy chủ ảo hóa và sao lưu dữ liệu cũ', '2026-08-07 15:30:00'),
(3, 5, 2, 0, 50, 'chua_xu_ly', 'dang_thuc_hien', 'Hoàn thành thiết kế giao diện Bootstrap 5.3 Dark/Light mode', '2026-08-09 09:00:00'),
(4, 5, 2, 50, 80, 'dang_thuc_hien', 'dang_thuc_hien', 'Tích hợp Mammoth.js xem file Word và viết thuật toán Extractive TF-IDF', '2026-08-10 08:00:00');
