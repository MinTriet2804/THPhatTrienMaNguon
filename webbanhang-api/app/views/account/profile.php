<?php include 'app/views/shares/header.php'; ?>

<style>
.profile-wrapper { max-width: 900px; margin: 0 auto; padding: 30px 16px; }
.profile-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 24px;
}
.profile-header {
    background: linear-gradient(135deg, #d70018, #a8000f);
    padding: 32px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 24px;
}
.avatar-wrapper {
    position: relative;
    width: 100px;
    height: 100px;
    flex-shrink: 0;
}
.avatar-img {
    width: 100px; height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,0.5);
    background: #fff;
}
.avatar-placeholder {
    width: 100px; height: 100px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: rgba(255,255,255,0.8);
    border: 4px solid rgba(255,255,255,0.5);
}
.avatar-upload-btn {
    position: absolute; bottom: 0; right: 0;
    background: #fff; color: #d70018;
    border-radius: 50%; width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.8rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    transition: transform 0.2s;
}
.avatar-upload-btn:hover { transform: scale(1.1); }
.profile-info h3 { font-weight: 800; margin-bottom: 4px; }
.profile-info .role-badge {
    display: inline-block;
    padding: 3px 12px; border-radius: 20px;
    font-size: 0.8rem; font-weight: 700;
    background: rgba(255,255,255,0.25);
}
.profile-body { padding: 28px 32px; }
.section-title {
    font-weight: 700; color: #333;
    border-left: 4px solid #d70018;
    padding-left: 12px; margin-bottom: 20px;
}
.form-control:focus { border-color: #d70018; box-shadow: 0 0 0 3px rgba(215,0,24,0.12); }
.btn-save {
    background: linear-gradient(135deg, #d70018, #a8000f);
    color: #fff; border: none; border-radius: 8px;
    padding: 10px 28px; font-weight: 700;
    transition: opacity 0.2s;
}
.btn-save:hover { opacity: 0.9; color: #fff; }
.info-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; color: rgba(255,255,255,0.85); font-size: 0.9rem; }
.info-row i { width: 18px; }
.stat-box {
    background: #f8f9fa; border-radius: 10px;
    padding: 16px; text-align: center;
}
.stat-box .stat-num { font-size: 1.6rem; font-weight: 800; color: #d70018; }
.stat-box .stat-label { font-size: 0.8rem; color: #888; }
</style>

<div class="profile-wrapper">

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

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Profile Header Card -->
    <div class="profile-card">
        <div class="profile-header">
            <div class="avatar-wrapper">
                <?php if (!empty($account->avatar)): ?>
                    <img src="/public/images/<?= urlencode(basename($account->avatar)) ?>"
                         alt="Avatar" class="avatar-img" id="avatarPreview">
                <?php else: ?>
                    <div class="avatar-placeholder" id="avatarPreview">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <label class="avatar-upload-btn" for="avatarInput" title="Thay đổi ảnh đại diện">
                    <i class="fas fa-camera"></i>
                </label>
            </div>

            <div class="profile-info">
                <h3><?= htmlspecialchars($account->fullname) ?></h3>
                <div class="mb-2">
                    <span class="role-badge">
                        <?= $account->role === 'admin' ? '<i class="fas fa-crown"></i> Admin' : '<i class="fas fa-user"></i> User' ?>
                    </span>
                    <?php if ($account->is_verified): ?>
                        <span class="role-badge ml-2"><i class="fas fa-check-circle"></i> Đã xác thực</span>
                    <?php else: ?>
                        <span class="role-badge ml-2" style="background:rgba(255,193,7,0.3);">
                            <i class="fas fa-exclamation-circle"></i> Chưa xác thực
                        </span>
                    <?php endif; ?>
                </div>
                <div class="info-row"><i class="fas fa-at"></i> <?= htmlspecialchars($account->username) ?></div>
                <?php if (!empty($account->email)): ?>
                    <div class="info-row"><i class="fas fa-envelope"></i> <?= htmlspecialchars($account->email) ?></div>
                <?php endif; ?>
                <?php if (!empty($account->phone)): ?>
                    <div class="info-row"><i class="fas fa-phone"></i> <?= htmlspecialchars($account->phone) ?></div>
                <?php endif; ?>
                <div class="info-row">
                    <i class="fas fa-calendar-alt"></i>
                    Tham gia: <?= date('d/m/Y', strtotime($account->created_at)) ?>
                </div>
            </div>
        </div>

        <!-- Avatar upload form (hidden) -->
        <form action="/webbanhang/account/uploadAvatar" method="post" enctype="multipart/form-data" id="avatarForm">
            <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none"
                   onchange="previewAndUpload(this)">
        </form>
    </div>

    <div class="row">
        <!-- Cập nhật hồ sơ -->
        <div class="col-md-8">
            <div class="profile-card">
                <div class="profile-body">
                    <h5 class="section-title"><i class="fas fa-edit mr-2"></i>Cập nhật hồ sơ</h5>
                    <form action="/webbanhang/account/updateProfile" method="post">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-600">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="fullname" class="form-control"
                                       value="<?= htmlspecialchars($account->fullname) ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-600">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($account->email ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-600">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($account->phone ?? '') ?>"
                                       placeholder="0901234567">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-600">Tên đăng nhập</label>
                                <input type="text" class="form-control"
                                       value="<?= htmlspecialchars($account->username) ?>" disabled>
                                <small class="text-muted">Không thể thay đổi tên đăng nhập.</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-600">Địa chỉ</label>
                            <textarea name="address" class="form-control" rows="2"
                                      placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành..."><?= htmlspecialchars($account->address ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save mr-2"></i>Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Đổi mật khẩu -->
            <div class="profile-card mb-3">
                <div class="profile-body">
                    <h5 class="section-title"><i class="fas fa-key mr-2"></i>Bảo mật</h5>
                    <a href="/webbanhang/account/changePassword" class="btn btn-outline-danger btn-block">
                        <i class="fas fa-lock mr-2"></i>Đổi mật khẩu
                    </a>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="profile-card">
                <div class="profile-body">
                    <h5 class="section-title"><i class="fas fa-chart-bar mr-2"></i>Thông tin</h5>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="stat-box">
                                <div class="stat-num"><?= $account->role === 'admin' ? '<i class="fas fa-crown" style="font-size:1.4rem"></i>' : '<i class="fas fa-user" style="font-size:1.4rem"></i>' ?></div>
                                <div class="stat-label"><?= ucfirst($account->role) ?></div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-box">
                                <div class="stat-num">
                                    <?php if ($account->is_active): ?>
                                        <i class="fas fa-check-circle text-success" style="font-size:1.4rem"></i>
                                    <?php else: ?>
                                        <i class="fas fa-ban text-danger" style="font-size:1.4rem"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="stat-label"><?= $account->is_active ? 'Hoạt động' : 'Bị khóa' ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="/webbanhang/account/manageUsers" class="btn btn-outline-secondary btn-block btn-sm">
                            <i class="fas fa-users-cog mr-2"></i>Quản lý người dùng
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewAndUpload(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            // Thay thế placeholder bằng img nếu cần
            if (preview.tagName === 'DIV') {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'avatar-img';
                img.id = 'avatarPreview';
                preview.parentNode.replaceChild(img, preview);
            } else {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
        // Tự động submit form upload
        document.getElementById('avatarForm').submit();
    }
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
