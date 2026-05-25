<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'app/views/shares/header.php';
?>

<style>
.mgr-page { padding: 30px 0 60px; }
.mgr-title {
    font-size: 1.6rem; font-weight: 700; color: #d70018;
    text-transform: uppercase; border-bottom: 2px solid #e0e0e0;
    padding-bottom: 15px; margin-bottom: 25px;
    display: flex; justify-content: space-between; align-items: center;
}
.btn-add-new {
    background: #d70018; color: #fff; border: none; border-radius: 8px;
    padding: 10px 20px; font-weight: 600; font-size: 0.9rem;
    text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
    transition: all 0.2s; box-shadow: 0 4px 12px rgba(215,0,24,0.2);
}
.btn-add-new:hover { background: #b50013; color: #fff; transform: translateY(-2px); }
.mgr-table-card {
    background: #fff; border-radius: 12px; overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.mgr-table thead { background: #d70018; color: #fff; }
.mgr-table thead th { padding: 14px 18px; font-weight: 600; font-size: 0.88rem; border: none; }
.mgr-table tbody td { padding: 14px 18px; vertical-align: middle; border-color: #f1f3f5; font-size: 0.9rem; }
.mgr-table tbody tr:hover { background: #fff8f8; }
.badge-count {
    background: #fff5f5; color: #d70018; border: 1px solid #ffe3e3;
    border-radius: 20px; padding: 3px 10px; font-size: 0.8rem; font-weight: 700;
}
.btn-edit {
    background: #f5f5f7; color: #444; border: 1px solid #e0e0e0;
    border-radius: 6px; padding: 6px 14px; font-size: 0.82rem; font-weight: 600;
    text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px;
}
.btn-edit:hover { background: #e4e4e7; color: #222; }
.btn-del {
    background: #fff5f5; color: #d70018; border: 1px solid #ffe3e3;
    border-radius: 6px; padding: 6px 14px; font-size: 0.82rem; font-weight: 600;
    text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px;
}
.btn-del:hover { background: #d70018; color: #fff; border-color: #d70018; }
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
.empty-state {
    text-align: center; padding: 50px 20px; color: #aaa;
}
.empty-state i { font-size: 3rem; margin-bottom: 14px; display: block; }
</style>

<div class="container mgr-page">
    <div class="mgr-title">
        <span><i class="fas fa-folder-open"></i> Quản lý danh mục</span>
        <a href="/webbanhang/Category/add" class="btn-add-new">
            <i class="fas fa-plus"></i> Thêm danh mục
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="flash-<?php echo $flash['type']; ?>">
            <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="mgr-table-card">
        <?php if (!empty($categories)): ?>
        <table class="table mgr-table mb-0">
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Tên danh mục</th>
                    <th>Mô tả</th>
                    <th class="text-center" style="width:130px">Số sản phẩm</th>
                    <th class="text-center" style="width:160px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td class="text-muted"><?php echo $cat->id; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($cat->name); ?></strong>
                    </td>
                    <td class="text-muted">
                        <?php echo $cat->description ? htmlspecialchars($cat->description) : '<em>Chưa có mô tả</em>'; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge-count">
                            <i class="fas fa-box"></i> <?php echo $cat->product_count; ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="/webbanhang/Category/edit/<?php echo $cat->id; ?>" class="btn-edit">
                            <i class="fas fa-pen"></i> Sửa
                        </a>
                        <a href="/webbanhang/Category/delete/<?php echo $cat->id; ?>"
                           class="btn-del"
                           onclick="return confirm('Xóa danh mục \'<?php echo htmlspecialchars($cat->name, ENT_QUOTES); ?>\'?\nCác sản phẩm thuộc danh mục này sẽ không còn danh mục.')">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>Chưa có danh mục nào. Hãy thêm danh mục đầu tiên!</p>
                <a href="/webbanhang/Category/add" class="btn-add-new" style="display:inline-flex;">
                    <i class="fas fa-plus"></i> Thêm ngay
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
