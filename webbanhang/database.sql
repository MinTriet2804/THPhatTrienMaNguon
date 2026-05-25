-- ============================================================
--  TechStore — Database Script hoàn chỉnh
--  Đọc từ source code: models, controllers, views
--  Database : webbanhang
--  Charset  : utf8mb4 (tiếng Việt đầy đủ)
--  Engine   : InnoDB (hỗ trợ foreign key, transaction)
-- ============================================================

CREATE DATABASE IF NOT EXISTS webbanhang
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE webbanhang;

-- ============================================================
--  XÓA BẢNG CŨ (đúng thứ tự tránh lỗi foreign key)
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS category;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  BẢNG 1: category
--  Dùng bởi: CategoryModel, ProductModel (JOIN)
--  Cột cần: id, name, description
-- ============================================================
CREATE TABLE category (
    id          INT            AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)   NOT NULL,
    description TEXT           DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  BẢNG 2: product
--  Dùng bởi: ProductModel (SELECT/INSERT/UPDATE/DELETE)
--  JOIN với category ON product.category_id = category.id
--  Cột cần: id, name, description, price, image, category_id
--  ON DELETE SET NULL — xóa danh mục không xóa sản phẩm
-- ============================================================
CREATE TABLE product (
    id          INT            AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)   NOT NULL,
    description TEXT           DEFAULT NULL,
    price       DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
    image       VARCHAR(255)   DEFAULT NULL,
    category_id INT            DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  BẢNG 3: orders
--  Dùng bởi: OrderModel (createOrder, getAllOrders, getOrderById,
--            updateStatus, deleteOrder, getStats)
--  Cột cần: id, fullname, phone, address, note,
--           payment_method, total_amount, status, created_at
--  status ENUM khớp với $allowed trong OrderModel::updateStatus()
-- ============================================================
CREATE TABLE orders (
    id             INT            AUTO_INCREMENT PRIMARY KEY,
    fullname       VARCHAR(150)   NOT NULL,
    phone          VARCHAR(20)    NOT NULL,
    address        TEXT           NOT NULL,
    note           TEXT           DEFAULT NULL,
    payment_method VARCHAR(50)    NOT NULL DEFAULT 'cod',
    total_amount   DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
    status         ENUM('pending','confirmed','shipping','delivered','cancelled')
                   NOT NULL DEFAULT 'pending',
    created_at     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  BẢNG 4: order_items
--  Dùng bởi: OrderModel (createOrderItems, getOrderItems)
--  Cột cần: id, order_id, product_id, product_name, price, quantity
--  Lưu snapshot tên + giá tại thời điểm đặt hàng
--  ON DELETE CASCADE — xóa order thì xóa luôn items
-- ============================================================
CREATE TABLE order_items (
    id           INT            AUTO_INCREMENT PRIMARY KEY,
    order_id     INT            NOT NULL,
    product_id   INT            DEFAULT NULL,
    product_name VARCHAR(200)   NOT NULL,
    price        DECIMAL(15,2)  NOT NULL,
    quantity     INT            NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  DỮ LIỆU MẪU: category
--  ID cố định để khớp với nav menu trong header.php:
--  /Product/category/1 → Điện thoại
--  /Product/category/2 → Laptop
--  /Product/category/3 → Máy tính bảng
--  /Product/category/4 → Phụ kiện
--  /Product/category/5 → Âm thanh
-- ============================================================
INSERT INTO category (id, name, description) VALUES
(1, 'Điện thoại',    'Điện thoại di động các hãng'),
(2, 'Laptop',        'Máy tính xách tay'),
(3, 'Máy tính bảng', 'Tablet các hãng'),
(4, 'Phụ kiện',      'Phụ kiện điện tử'),
(5, 'Âm thanh',      'Tai nghe, loa, thiết bị âm thanh');

-- ============================================================
--  DỮ LIỆU MẪU: product
--  Cột image để trống '' — upload ảnh thật qua giao diện web
--  price kiểu DECIMAL(15,2) — không dùng dấu chấm hàng nghìn
-- ============================================================
INSERT INTO product (name, description, price, image, category_id) VALUES
-- Điện thoại (category_id = 1)
('iPhone 15 Pro Max',
 'Chip A17 Pro, màn hình Super Retina XDR 6.7 inch, camera 48MP, khung titan, pin 4422mAh sạc nhanh 27W.',
 29990000.00, '', 1),

('Samsung Galaxy S24 Ultra',
 'Chip Snapdragon 8 Gen 3, màn hình Dynamic AMOLED 6.8 inch 120Hz, camera 200MP, bút S Pen tích hợp.',
 27990000.00, '', 1),

-- Laptop (category_id = 2)
('MacBook Air M3',
 'Chip Apple M3 8 nhân, màn hình Liquid Retina 13.6 inch, RAM 8GB, SSD 256GB, pin 18 giờ, 1.24kg.',
 26490000.00, '', 2),

('Asus ROG Strix G16',
 'CPU Intel Core i9-14900HX, GPU RTX 4070 8GB, RAM 16GB DDR5, SSD 1TB NVMe, màn hình 16 inch 240Hz QHD.',
 32500000.00, '', 2),

-- Máy tính bảng (category_id = 3)
('iPad Air 6',
 'Chip M2, màn hình Liquid Retina 11 inch, hỗ trợ Apple Pencil Pro và Magic Keyboard, Wi-Fi 6E, 128GB.',
 16990000.00, '', 3),

('Samsung Galaxy Tab S9',
 'Chip Snapdragon 8 Gen 2, màn hình Dynamic AMOLED 11 inch 120Hz, RAM 8GB, S Pen kèm theo, IP68.',
 18490000.00, '', 3),

-- Phụ kiện (category_id = 4)
('Sạc dự phòng Anker 20000mAh',
 'Dung lượng 20000mAh, sạc nhanh 22.5W, 2 cổng USB-A + 1 USB-C, công nghệ PowerIQ.',
 650000.00, '', 4),

('Chuột Logitech MX Master 3S',
 'Cảm biến 8000 DPI, cuộn MagSpeed siêu êm, kết nối Bluetooth + USB Receiver, pin 70 ngày.',
 2290000.00, '', 4),

-- Âm thanh (category_id = 5)
('Sony WH-1000XM5',
 'Tai nghe chống ồn hàng đầu, driver 30mm, pin 30 giờ, kết nối multipoint 2 thiết bị cùng lúc.',
 8490000.00, '', 5),

('JBL Charge 5',
 'Loa Bluetooth chống nước IP67, pin 20 giờ, công suất 40W, hỗ trợ sạc thiết bị khác qua USB.',
 3290000.00, '', 5);
