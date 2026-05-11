<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa sản phẩm - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f4f4; }
        .card { border: none; border-radius: 15px; }
        .card-header { background-color: #d70018 !important; color: white; border-radius: 15px 15px 0 0 !important; }
        .btn-danger { background-color: #d70018; border: none; }
        .img-preview { max-width: 150px; border-radius: 10px; border: 1px solid #ddd; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3 text-center">
                    <h4 class="mb-0 fw-bold">CHỈNH SỬA SẢN PHẨM</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/project1/Product/edit/<?php echo $product->getID(); ?>" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product->getName()); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($product->getDescription()); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                            <input type="number" name="price" class="form-control" value="<?php echo $product->getPrice(); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Hình ảnh sản phẩm</label>
                            <?php if(!empty($product->image)): ?>
                                <img src="/project1/public/images/<?php echo $product->image; ?>" class="img-preview" alt="Current Image">
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger fw-bold py-2">CẬP NHẬT NGAY</button>
                            <a href="/project1/Product/list" class="btn btn-outline-secondary">Hủy bỏ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>