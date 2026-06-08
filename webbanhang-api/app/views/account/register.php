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
    max-width: 520px;
    padding: 40px 36px;
}
.auth-card h2 { font-weight: 800; color: #d70018; margin-bottom: 6px; }
.auth-card .subtitle { color: #888; font-size: 0.9rem; margin-bottom: 28px; }
.form-control:focus { border-color: #d70018; box-shadow: 0 0 0 3px rgba(215,0,24,0.12); }
.btn-register {
    background: linear-gradient(135deg, #d70018, #a8000f);
    color: #fff; border: none; border-radius: 8px;
    padding: 12px; font-weight: 700; font-size: 1rem;
    width: 100%; transition: opacity 0.2s;
}
.btn-register:hover { opacity: 0.9; color: #fff; }
.invalid-feedback { display: block; }
.password-strength { height: 4px; border-radius: 2px; margin-top: 6px; transition: all 0.3s; }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div style="font-size:2.5rem; color:#d70018;"><i class="fas fa-user-plus"></i></div>
            <h2>Đăng ký tài khoản</h2>
            <p class="subtitle">Tạo tài khoản để mua sắm tại TechStore</p>
        </div>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul class="mb-0 mt-1 pl-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/webbanhang/account/save" method="post" id="registerForm">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="font-weight-600 text-dark">Tên đăng nhập <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                           placeholder="username..." value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['username']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col-md-6">
                    <label class="font-weight-600 text-dark">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="fullname" class="form-control <?= isset($errors['fullname']) ? 'is-invalid' : '' ?>"
                           placeholder="Nguyễn Văn A..." value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
                    <?php if (isset($errors['fullname'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['fullname']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-600 text-dark">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       placeholder="example@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
                <small class="text-muted">Email dùng để xác thực tài khoản và khôi phục mật khẩu.</small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="font-weight-600 text-dark">Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="regPassword"
                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           placeholder="Tối thiểu 6 ký tự..." required oninput="checkStrength(this.value)">
                    <div class="password-strength bg-secondary" id="strengthBar"></div>
                    <small id="strengthText" class="text-muted"></small>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col-md-6">
                    <label class="font-weight-600 text-dark">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="confirmpassword"
                           class="form-control <?= isset($errors['confirmPass']) ? 'is-invalid' : '' ?>"
                           placeholder="Nhập lại mật khẩu..." required>
                    <?php if (isset($errors['confirmPass'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['confirmPass']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-register">
                <i class="fas fa-user-plus mr-2"></i> Tạo tài khoản
            </button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted mb-0" style="font-size:0.9rem;">
            Đã có tài khoản?
            <a href="/webbanhang/account/login" class="text-danger font-weight-bold">Đăng nhập</a>
        </p>
    </div>
</div>

<script>
function checkStrength(val) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score  = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { color: '#dc3545', label: 'Rất yếu',  width: '20%' },
        { color: '#fd7e14', label: 'Yếu',       width: '40%' },
        { color: '#ffc107', label: 'Trung bình',width: '60%' },
        { color: '#28a745', label: 'Mạnh',      width: '80%' },
        { color: '#155724', label: 'Rất mạnh',  width: '100%'},
    ];
    const lvl = levels[Math.min(score, 4)];
    bar.style.background = lvl.color;
    bar.style.width      = lvl.width;
    text.textContent     = 'Độ mạnh: ' + lvl.label;
    text.style.color     = lvl.color;
}
</script>

<?php include 'app/views/shares/footer.php'; ?>
