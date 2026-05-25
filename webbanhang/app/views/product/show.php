<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'app/models/CartModel.php';
$cartModel = new CartModel();
$cartCount = $cartModel->getCount();
include 'app/views/shares/header.php';
?>

<style>
.show-page { padding: 30px 0 60px; }
.breadcrumb-cps { background: none; padding: 0; margin-bottom: 20px; font-size: 0.9rem; }
.breadcrumb-cps a { color: #d70018; text-decoration: none; }
.breadcrumb-cps a:hover { text-decoration: underline; }
.breadcrumb-cps .separator { margin: 0 8px; color: #aaa; }
.product-detail-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden;
}
.product-img-panel {
    background: #f9fafb; display: flex; align-items: center;
    justify-content: center; padding: 40px; min-height: 380px;
    border-right: 1px solid #f1f3f5;
}
.product-img-panel img {
    max-width: 100%; max-height: 320px; object-fit: contain;
    transition: transform 0.3s ease;
}
.product-img-panel img:hover { transform: scale(1.04); }
.product-info-panel { padding: 36px; }
.product-category-badge {
    display: inline-block; background: #fff5f5; color: #d70018;
    border: 1px solid #ffe3e3; font-size: 0.8rem; font-weight: 600;
    padding: 4px 12px; border-radius: 20px; margin-bottom: 14px;
}
.product-detail-name {
    font-size: 1.7rem; font-weight: 800; color: #1a1a1a;
    line-height: 1.3; margin-bottom: 16px;
}
.product-detail-price {
    font-size: 2rem; font-weight: 800; color: #d70018; margin-bottom: 6px;
}
.price-note { font-size: 0.85rem; color: #888; margin-bottom: 24px; }
.product-detail-desc {
    font-size: 0.95rem; color: #555; line-height: 1.7;
    border-top: 1px solid #f1f3f5; padding-top: 20px; margin-bottom: 28px;
}
.qty-selector { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.qty-selector label { font-weight: 600; font-size: 0.9rem; color: #333; }
.qty-control { display: flex; align-items: center; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
.qty-control button {
    width: 36px; height: 40px; border: none; background: #f5f5f7;
    font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: all 0.15s; color: #444;
}
.qty-control button:hover { background: #d70018; color: #fff; }
.qty-control input {
    width: 55px; height: 40px; border: none; border-left: 1px solid #e0e0e0;
    border-right: 1px solid #e0e0e0; text-align: center; font-weight: 700; font-size: 1rem;
}
.btn-add-cart {
    background: #d70018; color: #fff; border: none; border-radius: 10px;
    padding: 14px 28px; font-weight: 700; font-size: 1rem;
    cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(215,0,24,0.25);
    text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
}
.btn-add-cart:hover { background: #b50013; color: #fff; transform: translateY(-2px); }
.btn-buy-now {
    background: #fff; color: #d70018; border: 2px solid #d70018;
    border-radius: 10px; padding: 13px 28px; font-weight: 700; font-size: 1rem;
    cursor: pointer; transition: all 0.2s; text-decoration: none;
    display: inline-flex; align-items: center; gap: 8px; margin-left: 10px;
}
.btn-buy-now:hover { background: #fff5f5; color: #b50013; }
.product-meta { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f3f5; }
.meta-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #666; }
.meta-item i { color: #d70018; }
.btn-back-list {
    display: inline-flex; align-items: center; gap: 6px;
    color: #d70018; font-weight: 600; font-size: 0.9rem;
    text-decoration: none; margin-bottom: 20px;
    padding: 8px 16px; border: 1px solid #d70018; border-radius: 8px;
    transition: all 0.2s;
}
.btn-back-list:hover { background: #fff5f5; color: #b50013; }
.no-image-placeholder {
    width: 100%; height: 280px; background: #f1f3f5; border-radius: 12px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: #ccc;
}
.no-image-placeholder i { font-size: 4rem; margin-bottom: 10px; }
</style>

<div class="container show-page">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-cps">
        <a href="/webbanhang/Product"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="/webbanhang/Product">Sản phẩm</a>
        <span class="separator">/</span>
        <span style="color:#555;"><?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?></span>
    </nav>

    <a href="/webbanhang/Product" class="btn-back-list">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách
    </a>

    <div class="product-detail-card">
        <div class="row no-gutters">
            <!-- Ảnh sản phẩm -->
            <div class="col-md-5">
                <div class="product-img-panel">
                    <?php if (!empty($product->image)): ?>
                        <img src="<?php echo imageUrl($product->image); ?>"
                             alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>"
                             id="main-product-img"
                             onerror="this.parentElement.innerHTML='<div class=\'no-image-placeholder\'><i class=\'fas fa-image\'></i><span>Không tìm thấy ảnh</span></div>'">
                    <?php else: ?>
                        <div class="no-image-placeholder">
                            <i class="fas fa-image"></i>
                            <span>Chưa có hình ảnh</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thông tin sản phẩm -->
            <div class="col-md-7">
                <div class="product-info-panel">
                    <span class="product-category-badge">
                        <i class="fas fa-tag"></i>
                        <?php echo htmlspecialchars($product->category_id ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?>
                    </span>

                    <h1 class="product-detail-name">
                        <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                    </h1>

                    <div class="product-detail-price">
                        <?php echo number_format(floatval($product->price), 0, ',', '.'); ?>₫
                    </div>
                    <div class="price-note">
                        <i class="fas fa-check-circle text-success"></i> Giá đã bao gồm VAT
                        &nbsp;|&nbsp;
                        <i class="fas fa-truck text-success"></i> Miễn phí ship cho đơn từ 500.000₫
                    </div>

                    <div class="product-detail-desc">
                        <?php echo nl2br(htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8')); ?>
                    </div>

                    <!-- Chọn số lượng -->
                    <div class="qty-selector">
                        <label>Số lượng:</label>
                        <div class="qty-control">
                            <button type="button" onclick="changeDetailQty(-1)">−</button>
                            <input type="number" id="detail-qty" value="1" min="1" max="99" readonly>
                            <button type="button" onclick="changeDetailQty(1)">+</button>
                        </div>
                    </div>

                    <!-- Nút hành động -->
                    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
                        <a href="/webbanhang/Cart/add/<?php echo $product->id; ?>" class="btn-add-cart">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                        </a>
                        <a href="/webbanhang/Cart/add/<?php echo $product->id; ?>" class="btn-buy-now"
                           onclick="sessionStorage.setItem('buyNow','1')">
                            <i class="fas fa-bolt"></i> Mua ngay
                        </a>
                    </div>

                    <!-- Thông tin thêm -->
                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Bảo hành chính hãng 12 tháng</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-undo"></i>
                            <span>Đổi trả trong 30 ngày</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-headset"></i>
                            <span>Hỗ trợ 24/7</span>
                        </div>
                    </div>

                    <!-- Nút quản trị -->
                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #f1f3f5;display:flex;gap:10px;">
                        <a href="/webbanhang/Product/edit/<?php echo $product->id; ?>"
                           style="background:#f5f5f7;color:#444;border:1px solid #e0e0e0;border-radius:8px;padding:8px 16px;font-size:0.85rem;font-weight:600;text-decoration:none;transition:all 0.2s;">
                            <i class="fas fa-pen"></i> Chỉnh sửa
                        </a>
                        <a href="/webbanhang/Product/delete/<?php echo $product->id; ?>"
                           onclick="return confirm('Xóa sản phẩm này?')"
                           style="background:#fff5f5;color:#d70018;border:1px solid #ffe3e3;border-radius:8px;padding:8px 16px;font-size:0.85rem;font-weight:600;text-decoration:none;transition:all 0.2s;">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeDetailQty(delta) {
    const input = document.getElementById('detail-qty');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 99) val = 99;
    input.value = val;
}
// Nếu người dùng bấm "Mua ngay", redirect thẳng đến checkout sau khi thêm vào giỏ
if (sessionStorage.getItem('buyNow') === '1') {
    sessionStorage.removeItem('buyNow');
    setTimeout(() => { window.location.href = '/webbanhang/Cart/checkout'; }, 300);
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
