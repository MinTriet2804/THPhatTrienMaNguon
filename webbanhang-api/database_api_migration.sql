-- ============================================================
--  API Migration Script
--  Chạy script này sau khi đã có database webbanhang
--  để bổ sung các cột/bảng cần thiết cho Web API
-- ============================================================

USE webbanhang;

-- ============================================================
--  Thêm cột user_id vào bảng orders (nếu chưa có)
--  Cho phép liên kết đơn hàng với tài khoản người dùng
-- ============================================================
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER note,
ADD INDEX IF NOT EXISTS idx_user_id (user_id);

-- ============================================================
--  Tạo bảng payments (thanh toán đơn hàng)
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    id             INT            AUTO_INCREMENT PRIMARY KEY,
    order_id       INT            NOT NULL,
    payment_method VARCHAR(50)    NOT NULL DEFAULT 'cod',
    amount         DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
    status         ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(100)   DEFAULT NULL,
    paid_at        DATETIME       DEFAULT NULL,
    created_at     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Thêm chỉ mục tìm kiếm cho bảng product (tăng tốc API search)
-- ============================================================
ALTER TABLE product 
ADD INDEX IF NOT EXISTS idx_name (name),
ADD INDEX IF NOT EXISTS idx_category_id (category_id),
ADD INDEX IF NOT EXISTS idx_price (price);

-- ============================================================
--  Thêm chỉ mục cho bảng category
-- ============================================================
ALTER TABLE category 
ADD INDEX IF NOT EXISTS idx_name (name);

-- ============================================================
--  Kiểm tra kết quả
-- ============================================================
SELECT 'Migration hoàn tất!' AS status;
SHOW TABLES;
