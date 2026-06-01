-- ============================================================
--  TechStore — Migration Script
--  Dùng khi database cũ đã tồn tại và chỉ cần nâng cấp
--  bảng account thêm các cột mới.
--  Chạy file này THAY VÌ database.sql nếu đã có dữ liệu.
-- ============================================================

USE webbanhang;

-- Thêm cột email nếu chưa có
ALTER TABLE account
    ADD COLUMN IF NOT EXISTS email          VARCHAR(150) DEFAULT NULL UNIQUE AFTER fullname,
    ADD COLUMN IF NOT EXISTS avatar         VARCHAR(255) DEFAULT NULL AFTER role,
    ADD COLUMN IF NOT EXISTS phone          VARCHAR(20)  DEFAULT NULL AFTER avatar,
    ADD COLUMN IF NOT EXISTS address        TEXT         DEFAULT NULL AFTER phone,
    ADD COLUMN IF NOT EXISTS is_active      TINYINT(1)   NOT NULL DEFAULT 1 AFTER address,
    ADD COLUMN IF NOT EXISTS is_verified    TINYINT(1)   NOT NULL DEFAULT 0 AFTER is_active,
    ADD COLUMN IF NOT EXISTS verify_token   VARCHAR(100) DEFAULT NULL AFTER is_verified,
    ADD COLUMN IF NOT EXISTS remember_token VARCHAR(100) DEFAULT NULL AFTER verify_token,
    ADD COLUMN IF NOT EXISTS created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER remember_token;

-- Đặt tất cả tài khoản cũ là đã xác thực và đang hoạt động
UPDATE account SET is_verified = 1, is_active = 1 WHERE is_verified = 0;

-- Cập nhật lại password admin thành hash bcrypt nếu đang lưu plain text
-- Admin : username = admin | password = 12345678@Admin
UPDATE account
SET password = '$2y$10$OmzWkLGi51RlBK7hDtbrGultUPZ7xtmMddyG/lpSwtj2MCKinT2Iu'
WHERE username = 'admin'
  AND password NOT LIKE '$2y$%';

-- Tạo bảng password_resets nếu chưa có
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(150) NOT NULL,
    token      VARCHAR(100) NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
