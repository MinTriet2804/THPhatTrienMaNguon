<?php include 'app/views/shares/header.php'; ?>

<style>
.auth-wrapper {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
}
.auth-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    width: 100%;
    max-width: 440px;
    padding: 40px 36px;
}
.auth-card h2 {
    font-weight: 800;
    color: #d70018;
    margin-bottom: 6px;
}
.auth-card .subtitle {
    color: #888;
    font-size: 0.9rem;
    margin-bottom: 28px;
}
.form-control:focus {
    border-color: #d70018;
    box-shadow: 0 0 0 3px rgba(215,0,24,0.12);
}
.btn-login {
    background: linear-gradient(135deg, #d70018, #a8000f);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-weight: 700;
    font-size: 1rem;
    width: 100%;
    transition: opacity 0.2s;
}
.btn-login:hover { opacity: 0.9; color: #fff; }
.input-group-text { background: #f8f8f8; border-right: none; }
.form-control { border-left: none; }
.form-control:first-child { border-left: 1px solid #ced4da; }
.alert { border-radius: 8px; font-size: 0.9rem; }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div style="font-size:2.5rem; color:#d70018;"><i class="fas fa-user-circle"></i></div>
            <h2>Đăng nhập</h2>
            <p class="subtitle">Chào mừng trở lại TechStore!</p>
        </div>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/webbanhang/account/checkLogin" method="post">
            <div class="form-group">
                <label class="font-weight-600 text-dark">Tên đăng nhập</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                    </div>
                    <input type="text" name="username" class="form-control"
                           placeholder="Nhập username..."
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-600 text-dark">Mật khẩu</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                    </div>
                    <input type="password" name="password" id="passwordInput"
                           class="form-control" placeholder="Nhập mật khẩu..." required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePassword()" title="Hiện/ẩn mật khẩu">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group d-flex justify-content-between align-items-center">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="rememberMe" name="remember_me">
                    <label class="custom-control-label" for="rememberMe">Ghi nhớ đăng nhập</label>
                </div>
                <a href="/webbanhang/account/forgotPassword" class="text-danger" style="font-size:0.88rem;">
                    Quên mật khẩu?
                </a>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i> Đăng nhập
            </button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted mb-0" style="font-size:0.9rem;">
            Chưa có tài khoản?
            <a href="/webbanhang/account/register" class="text-danger font-weight-bold">Đăng ký ngay</a>
        </p>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
