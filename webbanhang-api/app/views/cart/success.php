<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'app/views/shares/header.php';
$paymentLabels = [
    'cod'    => 'Thanh toán khi nhận hàng (COD)',
    'bank'   => 'Chuyển khoản ngân hàng',
    'momo'   => 'Ví MoMo',
    'vnpay'  => 'VNPay',
];
$paymentLabel = $paymentLabels[$order['payment']] ?? $order['payment'];
$shipping     = $order['total'] >= 500000 ? 0 : 30000;
$grandTotal   = $order['total'] + $shipping;
?>

<style>
.success-page { padding: 40px 0 80px; }
.success-card {
    background: #fff; border-radius: 16px; padding: 40px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.07); text-align: center; margin-bottom: 30px;
}
.success-icon {
    width: 90px; height: 90px; background: linear-gradient(135deg, #d70018, #ff424e);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(215,0,24,0.3);
    animation: popIn 0.5s ease;
}
@keyframes popIn {
    0% { transform: scale(0); opacity: 0; }
    70% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}
.success-icon i { font-size: 2.5rem; color: #fff; }
.success-title { font-size: 1.8rem; font-weight: 800; color: #222; margin-bottom: 8px; }
.success-subtitle { color: #888; font-size: 1rem; margin-bottom: 0; }
.order-code {
    display: inline-block; background: #fff5f5; color: #d70018;
    border: 1px dashed #d70018; border-radius: 8px;
    padding: 8px 20px; font-weight: 700; font-size: 1.1rem; margin-top: 16px;
}
.info-card {
    background: #fff; border-radius: 12px; padding: 24px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px;
}
.info-card h5 {
    font-weight: 700; color: #222; font-size: 1rem;
    border-bottom: 2px solid #f1f3f5; padding-bottom: 12px; margin-bottom: 16px;
}
.info-card h5 i { color: #d70018; margin-right: 8px; }
.info-row { display: flex; gap: 10px; margin-bottom: 10px; font-size: 0.9rem; }
.info-label { color: #888; min-width: 130px; }
.info-value { font-weight: 600; color: #333; }
.order-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f3f5; }
.order-item:last-child { border-bottom: none; }
.order-item-img { width: 50px; height: 50px; object-fit: contain; border-radius: 8px; background: #f9fafb; padding: 4px; }
.order-item-name { flex: 1; font-weight: 600; font-size: 0.9rem; color: #333; }
.order-item-qty { font-size: 0.8rem; color: #888; }
.order-item-price { font-weight: 700; color: #d70018; white-space: nowrap; }
.total-final { font-size: 1.2rem; font-weight: 700; color: #d70018; text-align: right; padding-top: 14px; border-top: 2px dashed #f1f3f5; margin-top: 6px; }
.btn-home {
    background: #d70018; color: #fff; border: none; border-radius: 8px;
    padding: 14px 30px; font-weight: 700; font-size: 1rem;
    cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(215,0,24,0.25);
    text-decoration: none; display: inline-block;
}
.btn-home:hover { background: #b50013; color: #fff; transform: translateY(-2px); }
.btn-continue-shop {
    background: #fff; color: #d70018; border: 2px solid #d70018;
    border-radius: 8px; padding: 12px 28px; font-weight: 700; font-size: 1rem;
    cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; margin-left: 12px;
}
.btn-continue-shop:hover { background: #fff5f5; color: #b50013; }
</style>

<div class="container success-page">
    <!-- Thông báo thành công -->
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="success-title">Đặt hàng thành công!</h1>
        <p class="success-subtitle">Cảm ơn bạn đã tin tưởng mua sắm tại TechStore. Chúng tôi sẽ liên hệ xác nhận đơn hàng sớm nhất.</p>
        <div class="order-code">
            <i class="fas fa-hashtag"></i> Mã đơn hàng: #<?php echo strtoupper(substr(md5(time()), 0, 8)); ?>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin giao hàng -->
        <div class="col-lg-6 mb-4">
            <div class="info-card">
                <h5><i class="fas fa-map-marker-alt"></i> Thông tin giao hàng</h5>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user"></i> Người nhận:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-phone"></i> Điện thoại:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-home"></i> Địa chỉ:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['address']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-wallet"></i> Thanh toán:</span>
                    <span class="info-value"><?php echo htmlspecialchars($paymentLabel); ?></span>
                </div>
                <?php if (!empty($order['note'])): ?>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-sticky-note"></i> Ghi chú:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['note']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chi tiết đơn hàng -->
        <div class="col-lg-6 mb-4">
            <div class="info-card">
                <h5><i class="fas fa-shopping-bag"></i> Chi tiết đơn hàng</h5>
                <?php foreach ($order['cart'] as $item): ?>
                <div class="order-item">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?php echo imageUrl($item['image']); ?>"
                             class="order-item-img"
                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                             onerror="this.style.display='none'">
                    <?php else: ?>
                        <div style="width:50px;height:50px;background:#f1f3f5;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ccc;">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                    <div class="order-item-name">
                        <?php echo htmlspecialchars($item['name']); ?>
                        <div class="order-item-qty">x<?php echo $item['quantity']; ?></div>
                    </div>
                    <div class="order-item-price">
                        <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="margin-top:10px;">
                    <div style="display:flex;justify-content:space-between;font-size:0.9rem;color:#888;padding:6px 0;">
                        <span>Tạm tính</span>
                        <span><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:0.9rem;color:#888;padding:6px 0;">
                        <span>Phí vận chuyển</span>
                        <?php if ($shipping == 0): ?>
                            <span class="text-success font-weight-bold">Miễn phí</span>
                        <?php else: ?>
                            <span><?php echo number_format($shipping, 0, ',', '.'); ?>₫</span>
                        <?php endif; ?>
                    </div>
                    <div class="total-final">
                        Tổng thanh toán: <?php echo number_format($grandTotal, 0, ',', '.'); ?>₫
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-2">
        <a href="/webbanhang/Product" class="btn-home">
            <i class="fas fa-home"></i> Về trang chủ
        </a>
        <a href="/webbanhang/Product" class="btn-continue-shop">
            <i class="fas fa-store"></i> Tiếp tục mua sắm
        </a>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
