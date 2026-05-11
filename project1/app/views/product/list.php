<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>TechStore - CellphoneS Style</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar { background-color: #d70018; }
        .product-card { border-radius: 15px; border: none; transition: 0.3s; height: 100%; }
        .product-card:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.1); transform: translateY(-5px); }
        .price { color: #d70018; font-weight: bold; }
        .banner { background: #d70018; color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/project1/Product/list">TECHSTORE</a>
        <a href="/project1/Product/add" class="btn btn-light btn-sm rounded-pill px-3">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </a>
    </div>
</nav>

<div class="container">
    <div class="banner text-center shadow">
        <h2 class="fw-bold">ƯU ĐÃI THÁNG 5 - GIẢM ĐẾN 50%</h2>
        <p>Mua sắm linh kiện, điện thoại chính hãng tại TechStore</p>
    </div>

    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card product-card shadow-sm">
                    <img src="/project1/public/images/<?php echo $product->image; ?>" 
                         class="card-img-top p-3" style="height: 180px; object-fit: contain;">
                    <div class="card-body d-flex flex-column">
                        <h6 class="fw-bold text-dark text-truncate"><?php echo $product->getName(); ?></h6>
                        <p class="price mb-2"><?php echo number_format($product->getPrice(), 0, ',', '.'); ?>₫</p>
                        <div class="mt-auto d-flex gap-2">
                            <a href="/project1/Product/edit/<?php echo $product->getID(); ?>" class="btn btn-outline-primary btn-sm w-50">Sửa</a>
                            <a href="/project1/Product/delete/<?php echo $product->getID(); ?>" class="btn btn-outline-danger btn-sm w-50" onclick="return confirm('Xóa?')">Xóa</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có sản phẩm nào. Hãy thêm mới!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>