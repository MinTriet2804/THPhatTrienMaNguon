<?php include 'app/views/shares/header.php'; ?>

<style>
.manage-wrapper { max-width: 1100px; margin: 0 auto; padding: 30px 16px; }
.page-title {
    font-weight: 800; color: #333;
    border-left: 5px solid #d70018;
    padding-left: 14px; margin-bottom: 24px;
}
.card-table {
    background: #fff; border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}
.card-table .card-header {
    background: linear-gradient(135deg, #d70018, #a8000f);
    color: #fff; padding: 16px 24px;
    display: flex; align-items: center; justify-content: space-between;
}
.card-table .card-header h5 { margin: 0; font-weight: 700; }
.table th { background: #f8f9fa; font-weight: 700; font-size: 0.85rem; color: #555; border-top: none; }
.table td { vertical-align: middle; font-size: 0.9rem; }
.avatar-sm {
    width: 38px; height: 38px; border-radius: 50%;
    object-fit: cover; border: 2px solid #eee;
}
.avatar-sm-placeholder {
    width: 38px; height: 38px; border-radius: 50%;
    background: #f0f0f0; display: inline-flex;
    align-items: center; justify-content: center;
    color: #aaa; font-size: 1rem;
    border: 2px solid #eee;
}
.badge-role-admin { background: #d70018; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; }
.badge-role-user  { background: #6c757d; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; }
.badge-active   { background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; }
.badge-inactive { background: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; }
.badge-verified   { background: #cce5ff; color: #004085; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; }
.badge-unverified { background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; }
.btn-action { padding: 4px 10px; font-size: 0.8rem; border-radius: 6px; }
.search-box { max-width: 280px; }
.search-box .form-control { border-radius: 20px; font-size: 0.88rem; }
</style>

<div class="manage-wrapper">
    <h4 class="page-title"><i class="fas fa-users-cog mr-2"></i>Quản lý người dùng</h4>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="card-table">
        <div class="card-header">
            <h5><i class="fas fa-users mr-2"></i>Danh sách tài khoản
                <span class="badge badge-light ml-2" style="color:#d70018;"><?= count($accounts) ?></span>
            </h5>
            <div class="search-box">
                <input type="text" id="searchInput" class="form-control form-control-sm"
                       placeholder="Tìm kiếm...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="usersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Xác thực</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $i => $acc): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($acc->avatar)): ?>
                                    <img src="/webbanhang/img.php?f=<?= urlencode($acc->avatar) ?>"
                                         alt="" class="avatar-sm mr-2">
                                <?php else: ?>
                                    <span class="avatar-sm-placeholder mr-2">
                                        <i class="fas fa-user"></i>
                                    </span>
                                <?php endif; ?>
                                <div>
                                    <div class="font-weight-600"><?= htmlspecialchars($acc->fullname) ?></div>
                                    <small class="text-muted">@<?= htmlspecialchars($acc->username) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($acc->email ?? '—') ?></td>
                        <td>
                            <?php if ($acc->id == $_SESSION['user_id']): ?>
                                <span class="badge-role-<?= $acc->role ?>"><?= ucfirst($acc->role) ?> (bạn)</span>
                            <?php else: ?>
                                <form action="/webbanhang/account/updateRole" method="post" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $acc->id ?>">
                                    <select name="role" class="form-control form-control-sm d-inline-block"
                                            style="width:auto;" onchange="this.form.submit()">
                                        <option value="user"  <?= $acc->role === 'user'  ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $acc->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($acc->is_active): ?>
                                <span class="badge-active"><i class="fas fa-check-circle mr-1"></i>Hoạt động</span>
                            <?php else: ?>
                                <span class="badge-inactive"><i class="fas fa-ban mr-1"></i>Bị khóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($acc->is_verified): ?>
                                <span class="badge-verified"><i class="fas fa-envelope-open-text mr-1"></i>Đã xác thực</span>
                            <?php else: ?>
                                <span class="badge-unverified"><i class="fas fa-clock mr-1"></i>Chưa xác thực</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted" style="font-size:0.82rem;">
                            <?= date('d/m/Y', strtotime($acc->created_at)) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($acc->id != $_SESSION['user_id']): ?>
                                <?php if ($acc->is_active): ?>
                                    <a href="/webbanhang/account/toggleActive?id=<?= $acc->id ?>&status=0"
                                       class="btn btn-warning btn-action mr-1"
                                       onclick="return confirm('Khóa tài khoản <?= htmlspecialchars($acc->username) ?>?')"
                                       title="Khóa tài khoản">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="/webbanhang/account/toggleActive?id=<?= $acc->id ?>&status=1"
                                       class="btn btn-success btn-action mr-1"
                                       onclick="return confirm('Mở khóa tài khoản <?= htmlspecialchars($acc->username) ?>?')"
                                       title="Mở khóa tài khoản">
                                        <i class="fas fa-unlock"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="/webbanhang/account/deleteUser?id=<?= $acc->id ?>"
                                   class="btn btn-danger btn-action"
                                   onclick="return confirm('Xóa vĩnh viễn tài khoản <?= htmlspecialchars($acc->username) ?>? Thao tác này không thể hoàn tác!')"
                                   title="Xóa tài khoản">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="/webbanhang/account/profile" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Quay lại hồ sơ
        </a>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const q    = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
