<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'app/views/shares/header.php';
$shipping   = $total >= 500000 ? 0 : 30000;
$grandTotal = $total + $shipping;
?>

<style>
.checkout-page { padding: 30px 0 60px; }
.checkout-title {
    font-size: 1.6rem; font-weight: 700; color: #d70018;
    text-transform: uppercase; border-bottom: 2px solid #e0e0e0;
    padding-bottom: 15px; margin-bottom: 25px;
}
.checkout-card {
    background: #fff; border-radius: 12px; padding: 28px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px;
}
.checkout-card h5 {
    font-weight: 700; color: #222; font-size: 1.05rem;
    border-bottom: 2px solid #f1f3f5; padding-bottom: 12px; margin-bottom: 20px;
}
.checkout-card h5 i { color: #d70018; margin-right: 8px; }
.form-label { font-weight: 600; font-size: 0.9rem; color: #333; margin-bottom: 6px; }
.form-control {
    border-radius: 8px; border: 1px solid #e0e0e0;
    padding: 11px 14px; font-size: 0.95rem;
    transition: all 0.2s; background: #f9fafb;
}
.form-control:focus {
    border-color: #d70018;
    box-shadow: 0 0 0 3px rgba(215,0,24,0.12);
    background: #fff;
}
.payment-option {
    border: 2px solid #e0e0e0; border-radius: 10px; padding: 14px 18px;
    cursor: pointer; transition: all 0.2s; margin-bottom: 10px;
    display: flex; align-items: center; gap: 12px;
}
.payment-option:hover { border-color: #d70018; background: #fff5f5; }
.payment-option input[type="radio"] { accent-color: #d70018; width: 18px; height: 18px; }
.payment-option.selected { border-color: #d70018; background: #fff5f5; }
.payment-icon { font-size: 1.4rem; width: 36px; text-align: center; }
.payment-label { font-weight: 600; font-size: 0.95rem; color: #333; }
.payment-desc { font-size: 0.8rem; color: #888; }
.order-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f3f5; }
.order-item:last-child { border-bottom: none; }
.order-item-img { width: 55px; height: 55px; object-fit: contain; border-radius: 8px; background: #f9fafb; padding: 4px; }
.order-item-name { font-weight: 600; font-size: 0.9rem; color: #333; flex: 1; }
.order-item-qty { font-size: 0.85rem; color: #888; }
.order-item-price { font-weight: 700; color: #d70018; white-space: nowrap; }
.summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 0.95rem; }
.summary-total { font-size: 1.2rem; font-weight: 700; color: #d70018; border-top: 2px dashed #f1f3f5; padding-top: 14px; margin-top: 6px; }
.btn-place-order {
    background: #d70018; color: #fff; border: none; border-radius: 8px;
    padding: 15px; font-weight: 700; font-size: 1.05rem; width: 100%;
    cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(215,0,24,0.25);
    margin-top: 16px;
}
.btn-place-order:hover { background: #b50013; transform: translateY(-2px); }
.btn-back-cart {
    display: block; text-align: center; margin-top: 10px;
    color: #d70018; font-weight: 600; font-size: 0.9rem;
    text-decoration: none; padding: 10px;
    border: 1px solid #d70018; border-radius: 8px; transition: all 0.2s;
}
.btn-back-cart:hover { background: #fff5f5; color: #b50013; }
.step-bar { display: flex; align-items: center; margin-bottom: 30px; }
.step { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; }
.step-num {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.85rem;
}
.step.done .step-num { background: #d70018; color: #fff; }
.step.active .step-num { background: #d70018; color: #fff; }
.step.pending .step-num { background: #e0e0e0; color: #888; }
.step.done .step-label { color: #d70018; }
.step.active .step-label { color: #d70018; }
.step.pending .step-label { color: #aaa; }
.step-line { flex: 1; height: 2px; background: #e0e0e0; margin: 0 10px; }
.step-line.done { background: #d70018; }
</style>

<div class="container checkout-page">
    <h1 class="checkout-title"><i class="fas fa-credit-card"></i> Thanh toán</h1>

    <!-- Thanh bước -->
    <div class="step-bar">
        <div class="step done">
            <div class="step-num"><i class="fas fa-check"></i></div>
            <span class="step-label">Giỏ hàng</span>
        </div>
        <div class="step-line done"></div>
        <div class="step active">
            <div class="step-num">2</div>
            <span class="step-label">Thanh toán</span>
        </div>
        <div class="step-line"></div>
        <div class="step pending">
            <div class="step-num">3</div>
            <span class="step-label">Hoàn tất</span>
        </div>
    </div>

    <form method="POST" action="/webbanhang/Cart/placeOrder" onsubmit="return validateCheckout();">
        <div class="row">
            <!-- Cột trái: Form thông tin -->
            <div class="col-lg-7 mb-4">
                <!-- Thông tin giao hàng -->
                <div class="checkout-card">
                    <h5><i class="fas fa-map-marker-alt"></i> Thông tin giao hàng</h5>
                    <div class="mb-3">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control" placeholder="Nhập họ và tên người nhận..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control" placeholder="Ví dụ: 0901234567" required
                               pattern="^(0|\+84)[0-9]{9}$">
                        <small class="text-muted">Định dạng: 0xxxxxxxxx hoặc +84xxxxxxxxx</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="3"
                                  placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..." required></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Ghi chú đơn hàng</label>
                        <textarea name="note" class="form-control" rows="2"
                                  placeholder="Ghi chú thêm (thời gian giao hàng, yêu cầu đặc biệt...)"></textarea>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="checkout-card">
                    <h5><i class="fas fa-wallet"></i> Phương thức thanh toán</h5>

                    <label class="payment-option selected" id="opt-cod">
                        <input type="radio" name="payment" value="cod" checked onchange="selectPayment('opt-cod')">
                        <span class="payment-icon">💵</span>
                        <div>
                            <div class="payment-label">Thanh toán khi nhận hàng (COD)</div>
                            <div class="payment-desc">Trả tiền mặt khi nhận được hàng</div>
                        </div>
                    </label>

                    <label class="payment-option" id="opt-bank">
                        <input type="radio" name="payment" value="bank" onchange="selectPayment('opt-bank')">
                        <span class="payment-icon">🏦</span>
                        <div>
                            <div class="payment-label">Chuyển khoản ngân hàng</div>
                            <div class="payment-desc">Chuyển khoản qua tài khoản ngân hàng</div>
                        </div>
                    </label>

                    <label class="payment-option" id="opt-momo">
                        <input type="radio" name="payment" value="momo" onchange="selectPayment('opt-momo')">
                        <span class="payment-icon">💜</span>
                        <div>
                            <div class="payment-label">Ví MoMo</div>
                            <div class="payment-desc">Thanh toán qua ví điện tử MoMo</div>
                        </div>
                    </label>

                    <label class="payment-option" id="opt-vnpay">
                        <input type="radio" name="payment" value="vnpay" onchange="selectPayment('opt-vnpay')">
                        <span class="payment-icon">🔵</span>
                        <div>
                            <div class="payment-label">VNPay</div>
                            <div class="payment-desc">Thanh toán qua cổng VNPay (ATM, Visa, Master)</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Cột phải: Tóm tắt đơn hàng -->
            <div class="col-lg-5">
                <div class="checkout-card" style="position:sticky;top:20px;">
                    <h5><i class="fas fa-shopping-bag"></i> Đơn hàng của bạn</h5>

                    <div style="max-height:300px;overflow-y:auto;">
                        <?php foreach ($cart as $item): ?>
                        <div class="order-item">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo imageUrl($item['image']); ?>"
                                     class="order-item-img"
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     onerror="this.style.display='none'">
                            <?php else: ?>
                                <div style="width:55px;height:55px;background:#f1f3f5;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ccc;">
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
                    </div>

                    <div style="margin-top:16px;">
                        <div class="summary-row">
                            <span class="text-muted">Tạm tính</span>
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
                        <div class="summary-row summary-total">
                            <span>Tổng thanh toán</span>
                            <span><?php echo number_format($grandTotal, 0, ',', '.'); ?>₫</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-place-order">
                        <i class="fas fa-check-circle"></i> ĐẶT HÀNG NGAY
                    </button>
                    <a href="/webbanhang/Cart" class="btn-back-cart">
                        <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function selectPayment(selectedId) {
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    document.getElementById(selectedId).classList.add('selected');
}
function validateCheckout() {
    const phone = document.querySelector('input[name="phone"]').value;
    const phoneRegex = /^(0|\+84)[0-9]{9}$/;
    if (!phoneRegex.test(phone)) {
        alert('Số điện thoại không hợp lệ! Vui lòng nhập đúng định dạng.');
        return false;
    }
    return true;
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
