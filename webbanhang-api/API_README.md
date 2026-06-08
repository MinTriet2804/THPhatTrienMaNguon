# TechStore Web API — Tài liệu hướng dẫn

> **Chuyển đổi từ MVC sang RESTful Web API với PHP + PDO + Session**

---

## 📁 Cấu trúc thư mục API

```
webbanhang/
├── api/
│   ├── index.php                          ← API Router (Entry point)
│   ├── .htaccess                          ← Routing rules cho Apache
│   ├── helpers/
│   │   ├── Response.php                   ← Chuẩn hóa JSON response
│   │   └── Auth.php                       ← Kiểm tra session / phân quyền
│   └── controllers/
│       ├── ProductApiController.php       ← API sản phẩm
│       ├── CategoryApiController.php      ← API danh mục
│       ├── CartApiController.php          ← API giỏ hàng
│       ├── OrderApiController.php         ← API đơn hàng
│       ├── PaymentApiController.php       ← API thanh toán
│       └── AuthApiController.php         ← API xác thực
├── app/
│   ├── models/                            ← Models cập nhật (tương thích API + MVC)
│   └── ...
├── database_api_migration.sql             ← Migration cho bảng payments + user_id
├── TechStore_API_Postman_Collection.json  ← Postman Collection để test
└── .htaccess                              ← Cập nhật route /api/*
```

---

## ⚙️ Cài đặt

### 1. Setup Database
```sql
-- Chạy database.sql để tạo database gốc
SOURCE webbanhang/database.sql;

-- Chạy migration để thêm bảng payments và cột user_id
SOURCE webbanhang/database_api_migration.sql;
```

### 2. Cấu hình
Chỉnh sửa `app/config/database.php`:
```php
private $host     = "localhost";
private $db_name  = "webbanhang";
private $username = "root";
private $password = "";
```

### 3. Apache / XAMPP
- Bật `mod_rewrite`
- Đặt project vào `htdocs/webbanhang/`
- Truy cập: `http://localhost/webbanhang/api/products`

---

## 🌐 Base URL
```
http://localhost/webbanhang/api
```

---

## 📌 Quy ước chung

### Format response thành công
```json
{
  "success": true,
  "message": "Thành công",
  "data": { ... }
}
```

### Format response lỗi
```json
{
  "success": false,
  "message": "Mô tả lỗi",
  "errors": {
    "field": "Chi tiết lỗi"
  }
}
```

### HTTP Status Codes
| Code | Ý nghĩa |
|------|---------|
| 200  | OK — Thành công |
| 201  | Created — Tạo mới thành công |
| 400  | Bad Request — Thiếu/sai tham số |
| 401  | Unauthorized — Chưa đăng nhập |
| 403  | Forbidden — Không đủ quyền |
| 404  | Not Found — Không tìm thấy |
| 405  | Method Not Allowed |
| 409  | Conflict — Vi phạm ràng buộc |
| 422  | Unprocessable Entity — Validation lỗi |
| 500  | Internal Server Error |

---

## 🔐 API Xác thực

| Method | Endpoint | Mô tả | Auth |
|--------|----------|-------|------|
| POST | `/api/auth/login` | Đăng nhập | Không |
| POST | `/api/auth/logout` | Đăng xuất | User |
| POST | `/api/auth/register` | Đăng ký | Không |
| GET  | `/api/auth/me` | Thông tin user hiện tại | User |

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "12345678@Admin"
}
```

**Tài khoản mặc định:**
- Admin: `admin` / `12345678@Admin`
- User: `user1` / `12345678@Admin`

---

## 📦 API Sản phẩm

| Method | Endpoint | Mô tả | Auth |
|--------|----------|-------|------|
| GET | `/api/products` | Danh sách tất cả | Không |
| GET | `/api/products/{id}` | Chi tiết sản phẩm | Không |
| GET | `/api/products/search?q=tên` | Tìm kiếm theo tên | Không |
| GET | `/api/products/filter?category_id=1` | Lọc theo danh mục | Không |
| GET | `/api/products/sort?order=asc\|desc` | Sắp xếp theo giá | Không |
| POST | `/api/products` | Thêm sản phẩm mới | **Admin** |
| PUT | `/api/products/{id}` | Cập nhật sản phẩm | **Admin** |
| DELETE | `/api/products/{id}` | Xóa sản phẩm | **Admin** |

#### Thêm sản phẩm (JSON)
```http
POST /api/products
Content-Type: application/json

{
  "name": "Samsung Galaxy S25",
  "description": "Mô tả sản phẩm",
  "price": 25990000,
  "category_id": 1,
  "image": ""
}
```

#### Thêm sản phẩm (Upload ảnh)
```http
POST /api/products
Content-Type: multipart/form-data

name: Samsung Galaxy S25
description: Mô tả
price: 25990000
category_id: 1
image: [file.jpg]
```

#### Validation rules
- `name`: Không được rỗng, tối đa 200 ký tự
- `price`: Phải là số > 0
- `category_id`: Phải là số nguyên dương, danh mục phải tồn tại
- `image` (file): Chỉ chấp nhận jpg, jpeg, png, gif, webp

---

## 📁 API Danh mục

| Method | Endpoint | Mô tả | Auth |
|--------|----------|-------|------|
| GET | `/api/categories` | Danh sách + số sản phẩm | Không |
| GET | `/api/categories/{id}` | Chi tiết danh mục | Không |
| POST | `/api/categories` | Thêm danh mục | **Admin** |
| PUT | `/api/categories/{id}` | Cập nhật | **Admin** |
| DELETE | `/api/categories/{id}` | Xóa (không được xóa nếu còn sản phẩm) | **Admin** |

> ⚠️ **Quy tắc xóa:** Trả về lỗi `409 Conflict` nếu danh mục vẫn còn sản phẩm.

---

## 🛒 API Giỏ hàng

Giỏ hàng lưu trong PHP Session.

| Method | Endpoint | Mô tả | Auth |
|--------|----------|-------|------|
| GET | `/api/cart` | Xem giỏ hàng | **User** |
| GET | `/api/cart/total` | Tổng tiền + phí ship | **User** |
| POST | `/api/cart` | Thêm sản phẩm | **User** |
| PUT | `/api/cart/{product_id}` | Cập nhật số lượng | **User** |
| DELETE | `/api/cart/{product_id}` | Xóa 1 sản phẩm | **User** |
| DELETE | `/api/cart` | Xóa toàn bộ giỏ | **User** |

#### Thêm vào giỏ
```http
POST /api/cart
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 2
}
```

#### Validation rules
- `product_id`: Sản phẩm phải tồn tại trong database
- `quantity`: Phải > 0

---

## 📋 API Đơn hàng

| Method | Endpoint | Mô tả | Auth |
|--------|----------|-------|------|
| GET | `/api/orders` | Danh sách (Admin: tất cả, User: của mình) | **User** |
| GET | `/api/orders/{id}` | Chi tiết đơn hàng | **User** |
| POST | `/api/orders` | Tạo đơn từ giỏ hàng | **User** |
| PUT | `/api/orders/{id}/status` | Cập nhật trạng thái | **Admin** |
| DELETE | `/api/orders/{id}` | Hủy đơn | **User** |

#### Tạo đơn hàng
```http
POST /api/orders
Content-Type: application/json

{
  "fullname": "Nguyễn Văn An",
  "phone": "0901234567",
  "address": "123 Nguyễn Huệ, Q1, TP.HCM",
  "note": "Giao giờ hành chính",
  "payment_method": "cod"
}
```

`payment_method`: `cod` | `bank_transfer` | `e_wallet`

#### Cập nhật trạng thái (Admin)
```http
PUT /api/orders/1/status
Content-Type: application/json

{
  "status": "confirmed"
}
```

Luồng trạng thái: `pending` → `confirmed` → `shipping` → `delivered`  
Hủy đơn: `pending` / `confirmed` → `cancelled`

> ⚠️ Không cho đặt hàng nếu giỏ rỗng. Sau khi đặt thành công, giỏ hàng tự động xóa.

---

## 💳 API Thanh toán

| Method | Endpoint | Mô tả | Auth |
|--------|----------|-------|------|
| POST | `/api/payments` | Tạo thanh toán | **User** |
| GET | `/api/payments/{order_id}` | Trạng thái thanh toán | **User** |

#### Tạo thanh toán
```http
POST /api/payments
Content-Type: application/json

{
  "order_id": 1,
  "payment_method": "e_wallet"
}
```

#### Các phương thức thanh toán

| Method | Mô tả | Trạng thái ngay |
|--------|-------|-----------------|
| `cod` | Thanh toán khi nhận hàng | `pending` |
| `bank_transfer` | Chuyển khoản ngân hàng | `pending` (chờ xác nhận) |
| `e_wallet` | Ví điện tử (mô phỏng) | `paid` ✅ |

> ⚠️ Không cho thanh toán lại đơn hàng đã có trạng thái `paid`.  
> ⚠️ Không cho thanh toán đơn đã bị hủy (`cancelled`).

---

## 🧪 Test với Postman

1. Import file `TechStore_API_Postman_Collection.json` vào Postman
2. Tạo Environment với biến:
   - `base_url` = `http://localhost/webbanhang`
3. Bật **Cookie Jar** trong Postman (Settings → Automatically follow redirects + Send cookies)
4. Chạy theo thứ tự:
   - Auth → Login (Admin)
   - Products → CRUD
   - Categories → CRUD
   - Cart → Thêm sản phẩm
   - Orders → Tạo đơn hàng
   - Payments → Thanh toán

---

## 🔄 Tích hợp vào giao diện (JavaScript Fetch)

```html
<!-- Ví dụ: Lấy danh sách sản phẩm -->
<script>
async function loadProducts() {
  const res = await fetch('/webbanhang/api/products');
  const json = await res.json();
  if (json.success) {
    json.data.forEach(p => {
      console.log(p.name, p.price);
    });
  }
}

// Thêm vào giỏ hàng
async function addToCart(productId, qty = 1) {
  const res = await fetch('/webbanhang/api/cart', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: productId, quantity: qty })
  });
  const json = await res.json();
  alert(json.message);
}

// Đặt hàng
async function placeOrder(orderData) {
  const res = await fetch('/webbanhang/api/orders', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(orderData)
  });
  const json = await res.json();
  if (json.success) {
    alert('Đặt hàng thành công! Mã đơn: ' + json.data.order.id);
  }
}
</script>
```

---

## 📊 Bảng tóm tắt tất cả API Endpoints

| # | Method | Endpoint | Auth | Mô tả |
|---|--------|----------|------|-------|
| 1 | POST | `/api/auth/login` | — | Đăng nhập |
| 2 | POST | `/api/auth/logout` | User | Đăng xuất |
| 3 | POST | `/api/auth/register` | — | Đăng ký |
| 4 | GET | `/api/auth/me` | User | Thông tin user hiện tại |
| 5 | GET | `/api/products` | — | Danh sách sản phẩm |
| 6 | GET | `/api/products/{id}` | — | Chi tiết sản phẩm |
| 7 | GET | `/api/products/search?q=` | — | Tìm kiếm tên |
| 8 | GET | `/api/products/filter?category_id=` | — | Lọc theo danh mục |
| 9 | GET | `/api/products/sort?order=` | — | Sắp xếp theo giá |
| 10 | POST | `/api/products` | Admin | Thêm sản phẩm |
| 11 | PUT | `/api/products/{id}` | Admin | Cập nhật sản phẩm |
| 12 | DELETE | `/api/products/{id}` | Admin | Xóa sản phẩm |
| 13 | GET | `/api/categories` | — | Danh sách danh mục |
| 14 | GET | `/api/categories/{id}` | — | Chi tiết danh mục |
| 15 | POST | `/api/categories` | Admin | Thêm danh mục |
| 16 | PUT | `/api/categories/{id}` | Admin | Cập nhật danh mục |
| 17 | DELETE | `/api/categories/{id}` | Admin | Xóa danh mục |
| 18 | GET | `/api/cart` | User | Xem giỏ hàng |
| 19 | GET | `/api/cart/total` | User | Tổng tiền giỏ hàng |
| 20 | POST | `/api/cart` | User | Thêm vào giỏ |
| 21 | PUT | `/api/cart/{id}` | User | Cập nhật số lượng |
| 22 | DELETE | `/api/cart/{id}` | User | Xóa 1 sản phẩm |
| 23 | DELETE | `/api/cart` | User | Xóa toàn bộ giỏ |
| 24 | GET | `/api/orders` | User | Danh sách đơn hàng |
| 25 | GET | `/api/orders/{id}` | User | Chi tiết đơn hàng |
| 26 | POST | `/api/orders` | User | Tạo đơn hàng |
| 27 | PUT | `/api/orders/{id}/status` | Admin | Cập nhật trạng thái |
| 28 | DELETE | `/api/orders/{id}` | User | Hủy đơn hàng |
| 29 | POST | `/api/payments` | User | Tạo thanh toán |
| 30 | GET | `/api/payments/{order_id}` | User | Trạng thái thanh toán |
