<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'app/views/shares/header.php';

$statusConfig = [
    'pending'   => ['label' => 'Chờ xác nhận', 'color' => '#f59e0b', 'bg' => '#fffbeb', 'icon' => 'clock'],
    'confirmed' => ['label' => 'Đã xác nhận',  'color' => '#3b82f6', 'bg' => '#eff6ff', 'icon' => 'check'],
    'shipping'  => ['label' => 'Đang giao',     'color' => '#8b5cf6', 'bg' => '#f5f3ff', 'icon' => 'truck'],
    'delivered' => ['label' => 'Đã giao',       'color' => '#10b981', 'bg' => '#ecfdf5', 'icon' => 'check-circle'],
    'cancelled' => ['label' => 'Đã hủy',        'color' => '#ef4444', 'bg' => '#fef2f2', 'icon' => 'times-circle'],
];
$paymentLabels = [
    'cod'   => '💵 Thanh toán khi nhận hàng (COD)',
    'bank'  => '🏦 Chuyển khoản ngân hàng',
    'momo'  => '💜 Ví MoMo',
    'vnpay' => '🔵 VNPay',
];
$sc = $statusConfig[$order->status] ?? $statusConfig['pending'];
?>

<style>
.detail-page { padding: 30px 0 60px; }
.detail-title {
    font-size: 1.5rem; font-weight: 700; color: #d70018;
    border-bottom: 2px solid #e0e0e0; padding-bottom: 14px; margin-bottom: 24px;
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;
}
.info-card {
    background: #fff; border-radius: 12px; padding: 24px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px;
}
.info-card h5 {
    font-weight: 700; color: #222; font-size: 1rem;
    border-bottom: 2px solid #f1f3f5; padding-bottom: 12px; margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
}
.info-card h5 i { color: #d70018; }
.info-row { display: flex; gap: 12px; margin-bottom: 12px; font-size: 0.9rem; }
.info-label { color: #888; min-width: 140px; flex-shrink: 0; }
.info-value { font-weight: 600; color: #333; }
.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 20px; padding: 5px 14px; font-size: 0.85rem; font-weight: 700;
}
.items-table thead { background: #f5f5f7; }
.items-table thead th { padding: 11px 14px; font-weight: 600; font-size: 0.85rem; border: none; color: #555; }
.items-table tbody td { padding: 12px 14px; vertical-align: middle; border-color: #f1f3f5; font-size: 0.88rem; }
.item-img { width: 50px; height: 50px; object-fit: contain; border-radius: 6px; background: #f9fafb; padding: 3px; }
.total-row { font-size: 1.1rem; font-weight: 700; color: #d70018; }
.btn-back {
    display: inline-flex; align-items: center; gap: 7px;
    color: #d70018; border: 1px solid #d70018; border-radius: 8px;
    padding: 9px 18px; font-weight: 600; font-size: 0.88rem;
    text-decoration: none; transition: all 0.2s;
}
.btn-back:hover { background: #fff5f5; color: #b50013; }
.status-form-card {
    background: #fff; border-radius: 12px; padding: 22px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px;
}
.status-form-card h5 {
    font-weight: 700; color: #222; font-size: 1rem;
    border-bottom: 2px solid #f1f3f5; padding-bottom: 12px; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
}
.status-form-card h5 i { color: #d70018; }
.status-select-lg {
    width: 100%; border: 1px solid #e0e0e0; border-radius: 8px;
    padding: 11px 14px; font-size: 0.95rem; font-weight: 600;
    background: #f9fafb; cursor: pointer; margin-bottom: 12px;
    transition: border-color 0.2s;
}
.status-select-lg:focus { border-color: #d70018; outline: none; }
.btn-update-status {
    background: #d70018; color: #fff; border: none; border-radius: 8px;
    padding: 11px; font-weight: 700; font-size: 0.9rem; width: 100%;
    cursor: pointer; transition: all 0.2s;
}
.btn-update-status:hover { background: #b50013; }
/* Timeline trạng thái */
.timeline { display: flex; align-items: center; gap: 0; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px; }
.tl-step { display: flex; flex-direction: column; align-items: center; flex: 1; min-width: 80px; }
.tl-dot {
    width: 32px; height: 32px; border-radius: 50%; border: 2px solid #e0e0e0;
    background: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; color: #ccc; position: relative; z-index: 1;
}
.tl-dot.done { background: #d70018; border-color: #d70018; color: #fff; }
.tl-dot.active { background: #fff; border-color: #d70018; color: #d70018; box-shadow: 0 0 0 3px rgba(215,0,24,0.15); }
.tl-label { font-size: 0.72rem; color: #aaa; margin-top: 6px; text-align: center; white-space: nowrap; }
.tl-label.done, .tl-label.active { color: #d70018; font-weight: 600; }
.tl-line { flex: 1; height: 2px; background: #e0e0e0; margin-top: -16px; }
.tl-line.done { background: #d70018; }
</style>

<div class="container detail-page">
    <div class="detail-title">
        <span><i class="fas fa-receipt"></i> Chi tiết đơn hàng #<?php echo $order->id; ?></span>
        <a href="/webbanhang/Order" class="btn-back">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php
    // Timeline
    $steps = ['pending','confirmed','shipping','delivered'];
    $currentIdx = array_search($order->status, $steps);
    if ($order->status === 'cancelled') $currentIdx = -1;
    ?>
    <?php if ($order->status !== 'cancelled'): ?>
    <div class="timeline">
        <?php foreach ($steps as $i => $step): ?>
            <?php
            $cls = 'pending-tl';
            if ($i < $currentIdx) $cls = 'done';
            elseif ($i === $currentIdx) $cls = 'active';
            $sc2 = $statusConfig[$step];
            ?>
            <?php if ($i > 0): ?>
                <div class="tl-line <?php echo $i <= $currentIdx ? 'done' : ''; ?>"></div>
            <?php endif; ?>
            <div class="tl-step">
                <div class="tl-dot <?php echo $cls; ?>">
                    <i class="fas fa-<?php echo $sc2['icon']; ?>"></i>
                </div>
                <div class="tl-label <?php echo $cls; ?>"><?php echo $sc2['label']; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 18px;margin-bottom:20px;color:#ef4444;font-weight:600;">
        <i class="fas fa-times-circle"></i> Đơn hàng này đã bị hủy.
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7">
            <!-- Thông tin khách hàng -->
            <div class="info-card">
                <h5><i class="fas fa-user"></i> Thông tin khách hàng</h5>
                <div class="info-row">
                    <span class="info-label">Họ tên:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order->fullname); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Điện thoại:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order->phone); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Địa chỉ:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order->address); ?></span>
                </div>
                <?php if (!empty($order->note)): ?>
                <div class="info-row">
                    <span class="info-label">Ghi chú:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order->note); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Thanh toán:</span>
                    <span class="info-value"><?php echo $paymentLabels[$order->payment_method] ?? $order->payment_method; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày đặt:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($order->created_at)); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="status-badge" style="color:<?php echo $sc['color']; ?>;background:<?php echo $sc['bg']; ?>;">
                        <i class="fas fa-<?php echo $sc['icon']; ?>"></i> <?php echo $sc['label']; ?>
                    </span>
                </div>
            </div>

            <!-- Sản phẩm trong đơn -->
            <div class="info-card">
                <h5><i class="fas fa-shopping-bag"></i> Sản phẩm đã đặt</h5>
                <table class="table items-table mb-0">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th class="text-center">SL</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div style="width:50px;height:50px;background:#f1f3f5;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#ccc;">
                                    <i class="fas fa-box"></i>
                                </div>
                            </td>
                            <td><strong><?php echo htmlspecialchars($item->product_name); ?></strong></td>
                            <td class="text-center"><?php echo $item->quantity; ?></td>
                            <td class="text-right"><?php echo number_format($item->price, 0, ',', '.'); ?>₫</td>
                            <td class="text-right" style="color:#d70018;font-weight:700;">
                                <?php echo number_format($item->price * $item->quantity, 0, ',', '.'); ?>₫
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="4" class="text-right">Tổng thanh toán:</td>
                            <td class="text-right"><?php echo number_format($order->total_amount, 0, ',', '.'); ?>₫</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-5">
            <!-- Cập nhật trạng thái -->
            <div class="status-form-card">
                <h5><i class="fas fa-edit"></i> Cập nhật trạng thái</h5>
                <form method="POST" action="/webbanhang/Order/updateStatus">
                    <input type="hidden" name="id" value="<?php echo $order->id; ?>">
                    <select name="status" class="status-select-lg">
                        <?php foreach ($statusConfig as $val => $cfg): ?>
                            <option value="<?php echo $val; ?>" <?php echo $order->status === $val ? 'selected' : ''; ?>>
                                <?php echo $cfg['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-update-status">
                        <i class="fas fa-save"></i> Lưu trạng thái
                    </button>
                </form>
            </div>

            <!-- Tóm tắt đơn hàng -->
            <div class="info-card">
                <h5><i class="fas fa-calculator"></i> Tóm tắt thanh toán</h5>
                <?php
                $itemTotal = 0;
                foreach ($items as $it) $itemTotal += $it->price * $it->quantity;
                $shipping = $order->total_amount - $itemTotal;
                ?>
                <div class="info-row">
                    <span class="info-label">Tạm tính:</span>
                    <span class="info-value"><?php echo number_format($itemTotal, 0, ',', '.'); ?>₫</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phí vận chuyển:</span>
                    <span class="info-value">
                        <?php echo $shipping <= 0 ? '<span style="color:#10b981;font-weight:700;">Miễn phí</span>' : number_format($shipping, 0, ',', '.') . '₫'; ?>
                    </span>
                </div>
                <div class="info-row" style="border-top:2px dashed #f1f3f5;padding-top:12px;margin-top:4px;">
                    <span class="info-label" style="font-weight:700;color:#222;">Tổng cộng:</span>
                    <span style="font-size:1.2rem;font-weight:800;color:#d70018;">
                        <?php echo number_format($order->total_amount, 0, ',', '.'); ?>₫
                    </span>
                </div>
            </div>

            <!-- Xóa đơn hàng -->
            <div style="text-align:center;margin-top:10px;">
                <a href="/webbanhang/Order/delete/<?php echo $order->id; ?>"
                   onclick="return confirm('Xóa vĩnh viễn đơn hàng #<?php echo $order->id; ?>?')"
                   style="color:#d70018;font-size:0.85rem;font-weight:600;text-decoration:none;">
                    <i class="fas fa-trash"></i> Xóa đơn hàng này
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
