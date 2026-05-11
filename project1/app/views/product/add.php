<!DOCTYPE html>
<html>
<head>
    <title>Thêm sản phẩm mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0 fw-bold">THÊM SẢN PHẨM MỚI</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/project1/Product/add" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" placeholder="Ví dụ: iPhone 15 Pro Max" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Hình ảnh sản phẩm</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">Chọn ảnh định dạng .jpg, .png, .webp</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger py-2 fw-bold">LƯU SẢN PHẨM</button>
                            <a href="/project1/Product/list" class="btn btn-light py-2">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>