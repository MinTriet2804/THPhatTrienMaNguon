<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'app/models/CartModel.php';
$cartModel = new CartModel();
$cartCount = $cartModel->getCount();
include 'app/views/shares/header.php';
?>

<style>
.cart-page { padding: 30px 0 60px; }
.cart-title {
    font-size: 1.6rem; font-weight: 700; color: #d70018;
    text-transform: uppercase; border-bottom: 2px solid #e0e0e0;
    padding-bottom: 15px; margin-bottom: 25px;
}
.cart-table { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.cart-table thead { background: #d70018; color: #fff; }
.cart-table thead th { padding: 14px 16px; font-weight: 600; font-size: 0.9rem; border: none; }
.cart-table tbody td { padding: 16px; vertical-align: middle; border-color: #f1f3f5; }
.cart-img { width: 70px; height: 70px; object-fit: contain; border-radius: 8px; background: #f9fafb; padding: 4px; }
.cart-product-name { font-weight: 600; color: #222; font-size: 0.95rem; }
.cart-price { color: #d70018; font-weight: 700; font-size: 1rem; white-space: nowrap; }
.qty-input {
    width: 65px; text-align: center; border: 1px solid #e0e0e0;
    border-radius: 6px; padding: 6px; font-weight: 600;
}
.btn-remove {
    background: #fff5f5; color: #d70018; border: 1px solid #ffe3e3;
    border-radius: 6px; padding: 6px 12px; font-size: 0.85rem;
    font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-remove:hover { background: #d70018; color: #fff; border-color: #d70018; }
.cart-summary {
    background: #fff; border-radius: 12px; padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: sticky; top: 20px;
}
.cart-summary h5 { font-weight: 700; color: #222; border-bottom: 2px solid #f1f3f5; padding-bottom: 12px; margin-bottom: 18px; }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; }
.summary-total { font-size: 1.2rem; font-weight: 700; color: #d70018; border-top: 2px dashed #f1f3f5; padding-top: 14px; margin-top: 6px; }
.btn-checkout {
    background: #d70018; color: #fff; border: none; border-radius: 8px;
    padding: 14px; font-weight: 700; font-size: 1rem; width: 100%;
    cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(215,0,24,0.25);
    text-decoration: none; display: block; text-align: center; margin-top: 16px;
}
.btn-checkout:hover { background: #b50013; color: #fff; transform: translateY(-2px); }
.btn-continue {
    display: block; text-align: center; margin-top: 10px;
    color: #d70018; font-weight: 600; font-size: 0.9rem;
    text-decoration: none; padding: 10px;
    border: 1px solid #d70018; border-radius: 8px; transition: all 0.2s;
}
.btn-continue:hover { background: #fff5f5; color: #b50013; }
.btn-clear {
    background: #f5f5f7; color: #666; border: 1px solid #e0e0e0;
    border-radius: 8px; padding: 10px 18px; font-weight: 600;
    font-size: 0.85rem; cursor: pointer; transition: all 0.2s;
    text-decoration: none;
}
.btn-clear:hover { background: #e4e4e7; color: #333; }
.empty-cart {
    text-align: center; padding: 60px 20px;
    background: #fff; border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.empty-cart i { font-size: 4rem; color: #ddd; margin-bottom: 20px; display: block; }
.empty-cart p { color: #888; font-size: 1rem; margin-bottom: 20px; }
.badge-cart {
    background: #d70018; color: #fff; border-radius: 50%;
    width: 20px; height: 20px; font-size: 0.7rem; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
    margin-left: 4px;
}
</style>

<div class="container cart-page">
    <h1 class="cart-title"><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn
        <?php if (!empty($cart)): ?>
            <span class="badge-cart"><?php echo array_sum(array_column($cart, 'quantity')); ?></span>
        <?php endif; ?>
    </h1>

    <?php if (empty($cart)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Giỏ hàng của bạn đang trống.</p>
            <a href="/webbanhang/Product" class="btn-checkout" style="display:inline-block;width:auto;padding:12px 30px;">
                <i class="fas fa-store"></i> Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" style="font-size:0.9rem;">
                        <i class="fas fa-box"></i> <?php echo count($cart); ?> sản phẩm trong giỏ
                    </span>
                    <a href="/webbanhang/Cart/clear" class="btn-clear"
                       onclick="return confirm('Xóa toàn bộ giỏ hàng?')">
                        <i class="fas fa-trash"></i> Xóa tất cả
                    </a>
                </div>

                <div class="cart-table">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:80px">Ảnh</th>
                                <th>Sản phẩm</th>
                                <th class="text-center">Đơn giá</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-center">Thành tiền</th>
                                <th class="text-center">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $item): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?php echo imageUrl($item['image']); ?>"
                                             class="cart-img"
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             onerror="this.parentElement.innerHTML='<div style=\'width:70px;height:70px;background:#f1f3f5;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:1.5rem;\'><i class=\'fas fa-image\'></i></div>'">
                                    <?php else: ?>
                                        <div style="width:70px;height:70px;background:#f1f3f5;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:1.5rem;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="cart-product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="cart-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="/webbanhang/Cart/update" style="display:inline-flex;align-items:center;gap:6px;">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <button type="button" class="qty-btn" onclick="changeQty(this, -1)">−</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                               min="1" max="99" class="qty-input"
                                               onchange="this.form.submit()">
                                        <button type="button" class="qty-btn" onclick="changeQty(this, 1)">+</button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <span class="cart-price">
                                        <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="/webbanhang/Cart/remove/<?php echo $item['id']; ?>"
                                       class="btn-remove"
                                       onclick="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="cart-summary">
                    <h5><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h5>

                    <?php
                    $itemCount = array_sum(array_column($cart, 'quantity'));
                    $shipping  = $total >= 500000 ? 0 : 30000;
                    $grandTotal = $total + $shipping;
                    ?>

                    <div class="summary-row">
                        <span class="text-muted">Tạm tính (<?php echo $itemCount; ?> sản phẩm)</span>
                        <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">Phí vận chuyển</span>
                        <?php if ($shipping == 0): ?>
                            <span class="text-success font-weight-bold">Miễn phí</span>
                        <?php else: ?>
                            <span><?php echo number_format($shipping, 0, ',', '.'); ?>₫</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($shipping > 0): ?>
                    <div style="font-size:0.8rem;color:#888;margin-bottom:10px;">
                        <i class="fas fa-info-circle"></i> Miễn phí ship cho đơn từ 500.000₫
                    </div>
                    <?php endif; ?>

                    <div class="summary-row summary-total">
                        <span>Tổng cộng</span>
                        <span><?php echo number_format($grandTotal, 0, ',', '.'); ?>₫</span>
                    </div>

                    <a href="/webbanhang/Cart/checkout" class="btn-checkout">
                        <i class="fas fa-credit-card"></i> Tiến hành thanh toán
                    </a>
                    <a href="/webbanhang/Product" class="btn-continue">
                        <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.qty-btn {
    width: 28px; height: 28px; border: 1px solid #e0e0e0;
    background: #f5f5f7; border-radius: 6px; font-size: 1rem;
    font-weight: 700; cursor: pointer; line-height: 1;
    transition: all 0.15s; color: #444;
}
.qty-btn:hover { background: #d70018; color: #fff; border-color: #d70018; }
</style>

<script>
function changeQty(btn, delta) {
    const form  = btn.closest('form');
    const input = form.querySelector('input[name="quantity"]');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 99) val = 99;
    input.value = val;
    form.submit();
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
