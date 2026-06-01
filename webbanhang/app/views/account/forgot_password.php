<?php include 'app/views/shares/header.php'; ?>

<style>
.auth-wrapper { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px; }
.auth-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    width: 100%; max-width: 440px; padding: 40px 36px;
}
.auth-card h2 { font-weight: 800; color: #d70018; margin-bottom: 6px; }
.form-control:focus { border-color: #d70018; box-shadow: 0 0 0 3px rgba(215,0,24,0.12); }
.btn-send {
    background: linear-gradient(135deg, #d70018, #a8000f);
    color: #fff; border: none; border-radius: 8px;
    padding: 12px; font-weight: 700; font-size: 1rem;
    width: 100%; transition: opacity 0.2s;
}
.btn-send:hover { opacity: 0.9; color: #fff; }
.input-group-text { background: #f8f8f8; border-right: none; }
.form-control { border-left: none; }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div style="font-size:2.5rem; color:#d70018;"><i class="fas fa-envelope-open-text"></i></div>
            <h2>Quên mật khẩu</h2>
            <p class="text-muted" style="font-size:0.9rem;">Nhập email để nhận link đặt lại mật khẩu</p>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert <?= isset($success) && $success ? 'alert-success' : 'alert-danger' ?>">
                <i class="fas <?= isset($success) && $success ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($success) || !$success): ?>
        <form action="/webbanhang/account/sendResetLink" method="post">
            <div class="form-group">
                <label class="font-weight-600 text-dark">Địa chỉ Email <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                    </div>
                    <input type="email" name="email" class="form-control"
                           placeholder="example@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-send">
                <i class="fas fa-paper-plane mr-2"></i>Gửi link đặt lại mật khẩu
            </button>
        </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="/webbanhang/account/login" class="text-muted" style="font-size:0.9rem;">
                <i class="fas fa-arrow-left mr-1"></i>Quay lại đăng nhập
            </a>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
