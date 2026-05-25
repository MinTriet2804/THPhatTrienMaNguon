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
    'cod'   => 'COD',
    'bank'  => 'Chuyển khoản',
    'momo'  => 'MoMo',
    'vnpay' => 'VNPay',
];
?>

<style>
.order-page { padding: 30px 0 60px; }
.order-title {
    font-size: 1.6rem; font-weight: 700; color: #d70018;
    text-transform: uppercase; border-bottom: 2px solid #e0e0e0;
    padding-bottom: 15px; margin-bottom: 25px;
}
/* Thống kê */
.stats-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px; margin-bottom: 28px;
}
.stat-card {
    background: #fff; border-radius: 10px; padding: 18px 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center;
    border-top: 3px solid transparent; transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-3px); }
.stat-card.red   { border-top-color: #d70018; }
.stat-card.amber { border-top-color: #f59e0b; }
.stat-card.blue  { border-top-color: #3b82f6; }
.stat-card.purple{ border-top-color: #8b5cf6; }
.stat-card.green { border-top-color: #10b981; }
.stat-card.gray  { border-top-color: #9ca3af; }
.stat-num  { font-size: 1.8rem; font-weight: 800; color: #222; line-height: 1; }
.stat-label{ font-size: 0.8rem; color: #888; margin-top: 5px; }
.revenue-card {
    background: linear-gradient(135deg, #d70018, #a8000f);
    border-radius: 10px; padding: 18px 22px; color: #fff;
    box-shadow: 0 4px 15px rgba(215,0,24,0.2); margin-bottom: 28px;
    display: flex; align-items: center; justify-content: space-between;
}
.revenue-card .rev-label { font-size: 0.9rem; opacity: 0.85; }
.revenue-card .rev-num   { font-size: 1.8rem; font-weight: 800; }
/* Bảng */
.order-table-card {
    background: #fff; border-radius: 12px; overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.order-table thead { background: #d70018; color: #fff; }
.order-table thead th { padding: 13px 16px; font-weight: 600; font-size: 0.85rem; border: none; white-space: nowrap; }
.order-table tbody td { padding: 13px 16px; vertical-align: middle; border-color: #f1f3f5; font-size: 0.88rem; }
.order-table tbody tr:hover { background: #fff8f8; }
.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 700;
    white-space: nowrap;
}
.btn-detail {
    background: #f5f5f7; color: #444; border: 1px solid #e0e0e0;
    border-radius: 6px; padding: 5px 12px; font-size: 0.8rem; font-weight: 600;
    text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px;
}
.btn-detail:hover { background: #e4e4e7; color: #222; }
.btn-del-order {
    background: #fff5f5; color: #d70018; border: 1px solid #ffe3e3;
    border-radius: 6px; padding: 5px 12px; font-size: 0.8rem; font-weight: 600;
    text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px;
}
.btn-del-order:hover { background: #d70018; color: #fff; border-color: #d70018; }
.status-select {
    border: 1px solid #e0e0e0; border-radius: 6px; padding: 5px 8px;
    font-size: 0.8rem; font-weight: 600; cursor: pointer; background: #f9fafb;
    transition: border-color 0.2s;
}
.status-select:focus { border-color: #d70018; outline: none; }
.flash-success {
    background: #f0fff4; border: 1px solid #b7ebc8; color: #1a7a3a;
    border-radius: 8px; padding: 12px 18px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 10px; font-weight: 600;
}
.flash-error {
    background: #fff5f5; border: 1px solid #ffe3e3; color: #d70018;
    border-radius: 8px; padding: 12px 18px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 10px; font-weight: 600;
}
.empty-state { text-align: center; padding: 50px 20px; color: #aaa; }
.empty-state i { font-size: 3rem; margin-bottom: 14px; display: block; }
/* Filter tabs */
.filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
.filter-tab {
    padding: 7px 16px; border-radius: 20px; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; border: 1px solid #e0e0e0; background: #fff; color: #666;
    transition: all 0.2s; text-decoration: none;
}
.filter-tab:hover, .filter-tab.active { background: #d70018; color: #fff; border-color: #d70018; }
</style>

<div class="container order-page">
    <h1 class="order-title"><i class="fas fa-clipboard-list"></i> Quản lý đơn hàng</h1>

    <?php if ($flash): ?>
        <div class="flash-<?php echo $flash['type']; ?>">
            <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Thống kê tổng quan -->
    <?php if ($stats): ?>
    <div class="revenue-card">
        <div>
            <div class="rev-label"><i class="fas fa-chart-line"></i> Tổng doanh thu (không tính đơn hủy)</div>
            <div class="rev-num"><?php echo number_format($stats->total_revenue ?? 0, 0, ',', '.'); ?>₫</div>
        </div>
        <div style="text-align:right;">
            <div class="rev-label">Tổng đơn hàng</div>
            <div class="rev-num"><?php echo $stats->total_orders; ?></div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card amber">
            <div class="stat-num"><?php echo $stats->pending; ?></div>
            <div class="stat-label">Chờ xác nhận</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-num"><?php echo $stats->confirmed; ?></div>
            <div class="stat-label">Đã xác nhận</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-num"><?php echo $stats->shipping; ?></div>
            <div class="stat-label">Đang giao</div>
        </div>
        <div class="stat-card green">
            <div class="stat-num"><?php echo $stats->delivered; ?></div>
            <div class="stat-label">Đã giao</div>
        </div>
        <div class="stat-card gray">
            <div class="stat-num"><?php echo $stats->cancelled; ?></div>
            <div class="stat-label">Đã hủy</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bảng đơn hàng -->
    <div class="order-table-card">
        <?php if (!empty($orders)): ?>
        <div style="overflow-x:auto;">
        <table class="table order-table mb-0">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Khách hàng</th>
                    <th>Điện thoại</th>
                    <th>Thanh toán</th>
                    <th class="text-right">Tổng tiền</th>
                    <th class="text-center">Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <?php $sc = $statusConfig[$order->status] ?? $statusConfig['pending']; ?>
                <tr>
                    <td><strong>#<?php echo $order->id; ?></strong></td>
                    <td><?php echo htmlspecialchars($order->fullname); ?></td>
                    <td><?php echo htmlspecialchars($order->phone); ?></td>
                    <td><?php echo $paymentLabels[$order->payment_method] ?? $order->payment_method; ?></td>
                    <td class="text-right" style="color:#d70018;font-weight:700;white-space:nowrap;">
                        <?php echo number_format($order->total_amount, 0, ',', '.'); ?>₫
                    </td>
                    <td class="text-center">
                        <!-- Dropdown đổi trạng thái nhanh -->
                        <form method="POST" action="/webbanhang/Order/updateStatus" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $order->id; ?>">
                            <select name="status" class="status-select" onchange="this.form.submit()"
                                    style="color:<?php echo $sc['color']; ?>;background:<?php echo $sc['bg']; ?>;border-color:<?php echo $sc['color']; ?>;">
                                <?php foreach ($statusConfig as $val => $cfg): ?>
                                    <option value="<?php echo $val; ?>" <?php echo $order->status === $val ? 'selected' : ''; ?>>
                                        <?php echo $cfg['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td style="white-space:nowrap;color:#888;">
                        <?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?>
                    </td>
                    <td class="text-center" style="white-space:nowrap;">
                        <a href="/webbanhang/Order/detail/<?php echo $order->id; ?>" class="btn-detail">
                            <i class="fas fa-eye"></i> Chi tiết
                        </a>
                        <a href="/webbanhang/Order/delete/<?php echo $order->id; ?>"
                           class="btn-del-order"
                           onclick="return confirm('Xóa đơn hàng #<?php echo $order->id; ?>?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>Chưa có đơn hàng nào.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
