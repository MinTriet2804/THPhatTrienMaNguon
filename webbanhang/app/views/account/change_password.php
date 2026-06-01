<?php include 'app/views/shares/header.php'; ?>

<style>
.auth-wrapper { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px; }
.auth-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    width: 100%; max-width: 460px; padding: 40px 36px;
}
.auth-card h2 { font-weight: 800; color: #d70018; margin-bottom: 6px; }
.form-control:focus { border-color: #d70018; box-shadow: 0 0 0 3px rgba(215,0,24,0.12); }
.btn-save {
    background: linear-gradient(135deg, #d70018, #a8000f);
    color: #fff; border: none; border-radius: 8px;
    padding: 12px; font-weight: 700; font-size: 1rem;
    width: 100%; transition: opacity 0.2s;
}
.btn-save:hover { opacity: 0.9; color: #fff; }
.input-group-text { background: #f8f8f8; border-right: none; }
.form-control { border-left: none; }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div style="font-size:2.5rem; color:#d70018;"><i class="fas fa-key"></i></div>
            <h2>Đổi mật khẩu</h2>
            <p class="text-muted" style="font-size:0.9rem;">Bảo vệ tài khoản của bạn</p>
        </div>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/webbanhang/account/updatePassword" method="post">
            <div class="form-group">
                <label class="font-weight-600 text-dark">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                    </div>
                    <input type="password" name="old_password" class="form-control"
                           placeholder="Nhập mật khẩu hiện tại..." required>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-600 text-dark">Mật khẩu mới <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock-open text-muted"></i></span>
                    </div>
                    <input type="password" name="new_password" id="newPass" class="form-control"
                           placeholder="Tối thiểu 6 ký tự..." required minlength="6">
                </div>
                <small class="text-muted">Mật khẩu tối thiểu 6 ký tự, nên kết hợp chữ hoa, số và ký tự đặc biệt.</small>
            </div>

            <div class="form-group">
                <label class="font-weight-600 text-dark">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-check-double text-muted"></i></span>
                    </div>
                    <input type="password" name="confirm_password" id="confirmPass" class="form-control"
                           placeholder="Nhập lại mật khẩu mới..." required>
                </div>
                <small id="matchMsg" class="text-muted"></small>
            </div>

            <button type="submit" class="btn btn-save">
                <i class="fas fa-save mr-2"></i>Cập nhật mật khẩu
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="/webbanhang/account/profile" class="text-muted" style="font-size:0.9rem;">
                <i class="fas fa-arrow-left mr-1"></i>Quay lại hồ sơ
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('confirmPass').addEventListener('input', function() {
    const msg = document.getElementById('matchMsg');
    if (this.value === document.getElementById('newPass').value) {
        msg.textContent = '✓ Mật khẩu khớp';
        msg.className = 'text-success';
    } else {
        msg.textContent = '✗ Mật khẩu chưa khớp';
        msg.className = 'text-danger';
    }
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
