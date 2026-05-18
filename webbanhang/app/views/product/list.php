<?php include 'app/views/shares/header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f4f6f9;
        color: #444;
    }
    .cps-title-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 30px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 15px;
    }
    .cps-title {
        font-weight: 700;
        font-size: 1.6rem;
        color: #d70018; /* Màu đỏ CellphoneS */
        text-transform: uppercase;
        margin: 0;
    }
    .btn-cps-add {
        background-color: #d70018;
        color: #fff !important;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
        text-decoration: none !important;
    }
    .btn-cps-add:hover {
        background-color: #b50013;
        transform: translateY(-2px);
    }
    /* Khung lưới sản phẩm */
    .cps-product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 50px;
    }
    /* Thẻ bọc sản phẩm */
    .cps-product-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        position: relative;
    }
    .cps-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border-color: #d70018;
    }
    
    .cps-product-img-wrapper {
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        padding: 15px;
        border-bottom: 1px solid #f1f3f5;
    }
    .cps-product-img-wrapper img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }
    .cps-product-card:hover .cps-product-img-wrapper img {
        transform: scale(1.05);
    }
    .cps-no-image {
        color: #bbb;
        font-size: 0.85rem;
        text-align: center;
    }
    .cps-no-image i {
        display: block;
        margin-bottom: 5px;
        color: #ddd;
    }

    /* Phần nội dung chữ */
    .cps-product-info {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .cps-product-name {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1.4;
        height: 48px; /* Giới hạn độ cao 2 dòng chữ */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cps-product-name a {
        color: #222;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .cps-product-name a:hover {
        color: #d70018;
    }
    .cps-category-badge {
        display: inline-block;
        background-color: #f1f3f5;
        color: #495057;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        margin-bottom: 12px;
        align-self: flex-start;
    }
    .cps-product-desc {
        font-size: 0.85rem;
        color: #777;
        margin-bottom: 15px;
        height: 57px; /* Giới hạn độ cao 3 dòng mô tả */
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.5;
    }
    .cps-price-box {
        margin-top: auto; /* Đẩy giá xuống sát đáy */
        padding-top: 10px;
        border-top: 1px dashed #f1f3f5;
    }
    .cps-price-label {
        font-size: 0.8rem;
        color: #888;
    }
    .cps-product-price {
        color: #d70018;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    /* Hệ thống nút hành động */
    .cps-action-box {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .btn-cps-edit {
        background-color: #f5f5f7;
        color: #444 !important;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px;
        border-radius: 6px;
        text-align: center;
        text-decoration: none !important;
        border: 1px solid #e0e0e0;
        transition: all 0.2s ease;
    }
    .btn-cps-edit:hover {
        background-color: #e4e4e7;
        color: #000 !important;
    }
    .btn-cps-delete {
        background-color: #fff5f5;
        color: #d70018 !important;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px;
        border-radius: 6px;
        text-align: center;
        text-decoration: none !important;
        border: 1px solid #ffe3e3;
        transition: all 0.2s ease;
    }
    .btn-cps-delete:hover {
        background-color: #d70018;
        color: #fff !important;
        border-color: #d70018;
    }
</style>

<div class="container">
    <div class="cps-title-section">
        <h1 class="cps-title"><i class="fas fa-stream"></i> Danh sách sản phẩm</h1>
        <a href="/webbanhang/Product/add" class="btn-cps-add">
            <i class="fas fa-plus"></i> Thêm sản phẩm mới
        </a>
    </div>

    <div class="cps-product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="cps-product-card">
                    
                    <div class="cps-product-img-wrapper">
                        <!-- Đồng bộ chính xác với thư mục public/images/ đã sửa đổi -->
                        <?php if (!empty($product->image) && file_exists("public/images/" . $product->image)): ?>
                            <img src="/webbanhang/public/images/<?php echo $product->image; ?>" alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <div class="cps-no-image">
                                <i class="fas fa-image fa-2x"></i>
                                Chưa có hình ảnh
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="cps-product-info">
                        
                        <span class="cps-category-badge">
                            <i class="fas fa-tags"></i> <?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?>
                        </span>

                        <h2 class="cps-product-name">
                            <a href="/webbanhang/Product/show/<?php echo $product->id; ?>">
                                <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </h2>

                        <p class="cps-product-desc">
                            <?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?>
                        </p>

                        <div class="cps-price-box">
                            <div class="cps-price-label">Giá bán độc quyền:</div>
                            <div class="cps-product-price">
                                <?php 
                                    $price = floatval($product->price);
                                    echo number_format($price, 0, ',', '.') . ' ₫'; 
                                ?>
                            </div>
                            
                            <div class="cps-action-box">
                                <a href="/webbanhang/Product/edit/<?php echo $product->id; ?>" class="btn-cps-edit">
                                    <i class="fas fa-pen"></i> Sửa
                                </a>
                                <a href="/webbanhang/Product/delete/<?php echo $product->id; ?>" 
                                   class="btn-cps-delete" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hệ thống sẽ gỡ bỏ vĩnh viễn hình ảnh đính kèm trên máy chủ.');">
                                    <i class="fas fa-trash-alt"></i> Xóa
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center p-5 col-span-full w-100" style="grid-column: 1 / -1; background: #fff; border-radius: 12px; border: 1px dashed #ccc;">
                <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                <p class="text-muted mb-0">Hiện chưa có sản phẩm nào trong danh mục này.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>