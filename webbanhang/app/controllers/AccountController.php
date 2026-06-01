<?php
require_once 'app/config/database.php';
require_once 'app/models/AccountModel.php';

class AccountController
{
    private $accountModel;
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db           = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);

        // Tự động đăng nhập qua Remember Me cookie
        $this->autoLoginFromCookie();
    }

    // ================================================================
    //  HELPER: Tự đăng nhập từ cookie remember_me
    // ================================================================
    private function autoLoginFromCookie()
    {
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
            $token   = $_COOKIE['remember_me'];
            $account = $this->accountModel->getAccountByRememberToken($token);
            if ($account && $account->is_active) {
                $this->setSession($account);
            }
        }
    }

    // ================================================================
    //  HELPER: Ghi session sau khi đăng nhập
    // ================================================================
    private function setSession($account)
    {
        $_SESSION['user_id']  = $account->id;
        $_SESSION['username'] = $account->username;
        $_SESSION['fullname'] = $account->fullname;
        $_SESSION['role']     = $account->role;
        $_SESSION['avatar']   = $account->avatar ?? '';
        $_SESSION['email']    = $account->email ?? '';
    }

    // ================================================================
    //  HELPER: Gửi email (dùng PHP mail() — cần cấu hình SMTP)
    //  Trong môi trường dev (Laragon) có thể dùng MailHog/Mailtrap
    // ================================================================
    private function sendMail($to, $subject, $body)
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: TechStore <noreply@techstore.vn>\r\n";
        return mail($to, $subject, $body, $headers);
    }

    // ================================================================
    //  ĐĂNG KÝ
    // ================================================================
    public function register()
    {
        include 'app/views/account/register.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/account/register');
            exit;
        }

        $username        = trim($_POST['username']        ?? '');
        $fullName        = trim($_POST['fullname']        ?? '');
        $email           = trim($_POST['email']           ?? '');
        $password        = $_POST['password']             ?? '';
        $confirmPassword = $_POST['confirmpassword']      ?? '';
        $role            = $_POST['role']                 ?? 'user';
        $errors          = [];

        if (empty($username))                          $errors['username']    = 'Vui lòng nhập username!';
        if (empty($fullName))                          $errors['fullname']    = 'Vui lòng nhập họ tên!';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                                                       $errors['email']      = 'Email không hợp lệ!';
        if (empty($password))                          $errors['password']   = 'Vui lòng nhập mật khẩu!';
        if (strlen($password) < 6)                     $errors['password']   = 'Mật khẩu tối thiểu 6 ký tự!';
        if ($password !== $confirmPassword)            $errors['confirmPass']= 'Mật khẩu xác nhận chưa khớp!';
        if (!in_array($role, ['admin', 'user']))       $role = 'user';

        if ($this->accountModel->getAccountByUsername($username)) {
            $errors['account'] = 'Username này đã được đăng ký!';
        }
        if (!empty($email) && $this->accountModel->getAccountByEmail($email)) {
            $errors['email'] = 'Email này đã được sử dụng!';
        }

        if (!empty($errors)) {
            include 'app/views/account/register.php';
            return;
        }

        // Tạo token xác thực email
        $verifyToken = bin2hex(random_bytes(32));
        $result      = $this->accountModel->save($username, $fullName, $email, $password, $role, $verifyToken);

        if ($result) {
            // Gửi email xác thực
            $verifyLink = "http://{$_SERVER['HTTP_HOST']}/webbanhang/account/verifyEmail?token={$verifyToken}";
            $body = "
                <h2>Xác thực tài khoản TechStore</h2>
                <p>Xin chào <strong>{$fullName}</strong>,</p>
                <p>Nhấn vào link bên dưới để xác thực tài khoản của bạn:</p>
                <p><a href='{$verifyLink}' style='background:#d70018;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;'>Xác thực ngay</a></p>
                <p>Link có hiệu lực trong 24 giờ.</p>
                <p>Nếu bạn không đăng ký tài khoản này, hãy bỏ qua email này.</p>
            ";
            $this->sendMail($email, 'Xác thực tài khoản TechStore', $body);

            $_SESSION['flash_success'] = 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.';
            header('Location: /webbanhang/account/login');
            exit;
        }

        $errors['general'] = 'Đăng ký thất bại, vui lòng thử lại!';
        include 'app/views/account/register.php';
    }

    // ================================================================
    //  XÁC THỰC EMAIL
    // ================================================================
    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $_SESSION['flash_error'] = 'Token không hợp lệ!';
            header('Location: /webbanhang/account/login');
            exit;
        }

        $result = $this->accountModel->verifyEmail($token);
        if ($result) {
            $_SESSION['flash_success'] = 'Xác thực email thành công! Bạn có thể đăng nhập.';
        } else {
            $_SESSION['flash_error'] = 'Token không hợp lệ hoặc đã hết hạn!';
        }
        header('Location: /webbanhang/account/login');
        exit;
    }

    // ================================================================
    //  ĐĂNG NHẬP
    // ================================================================
    public function login()
    {
        include 'app/views/account/login.php';
    }

    public function checkLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/account/login');
            exit;
        }

        $username   = trim($_POST['username']   ?? '');
        $password   = $_POST['password']        ?? '';
        $rememberMe = isset($_POST['remember_me']);
        $error      = '';

        $account = $this->accountModel->getAccountByUsername($username);

        if (!$account) {
            $error = 'Không tìm thấy tài khoản!';
        } elseif (!password_verify($password, $account->password)) {
            $error = 'Mật khẩu không đúng!';
        } elseif (!$account->is_active) {
            $error = 'Tài khoản đã bị khóa! Vui lòng liên hệ quản trị viên.';
        } elseif (!$account->is_verified) {
            $error = 'Tài khoản chưa được xác thực email! Vui lòng kiểm tra hộp thư.';
        }

        if (!empty($error)) {
            include 'app/views/account/login.php';
            return;
        }

        // Ghi session
        $this->setSession($account);

        // Remember Me
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            $this->accountModel->saveRememberToken($account->id, $token);
            setcookie('remember_me', $token, time() + (30 * 24 * 3600), '/', '', false, true);
        }

        // Redirect về trang đích nếu có (ví dụ: từ giỏ hàng)
        $redirect = $_SESSION['redirect_after_login'] ?? '/webbanhang/product';
        unset($_SESSION['redirect_after_login']);

        header('Location: ' . $redirect);
        exit;
    }

    // ================================================================
    //  ĐĂNG XUẤT
    // ================================================================
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $this->accountModel->clearRememberToken($_SESSION['user_id']);
        }

        // Xóa cookie remember me
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }

        session_destroy();
        header('Location: /webbanhang/product');
        exit;
    }

    // ================================================================
    //  HỒ SƠ CÁ NHÂN
    // ================================================================
    public function profile()
    {
        $this->requireLogin();
        $account = $this->accountModel->getAccountById($_SESSION['user_id']);
        include 'app/views/account/profile.php';
    }

    public function updateProfile()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/account/profile');
            exit;
        }

        $id      = $_SESSION['user_id'];
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $address  = trim($_POST['address']  ?? '');
        $errors   = [];

        if (empty($fullname))                                          $errors[] = 'Họ tên không được để trống!';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ!';

        // Kiểm tra email trùng với người khác
        if (!empty($email)) {
            $existing = $this->accountModel->getAccountByEmail($email);
            if ($existing && $existing->id != $id) {
                $errors[] = 'Email này đã được sử dụng bởi tài khoản khác!';
            }
        }

        if (!empty($errors)) {
            $account = $this->accountModel->getAccountById($id);
            include 'app/views/account/profile.php';
            return;
        }

        $this->accountModel->updateProfile($id, $fullname, $email, $phone, $address);

        // Cập nhật session
        $_SESSION['fullname'] = $fullname;
        $_SESSION['email']    = $email;

        $_SESSION['flash_success'] = 'Cập nhật hồ sơ thành công!';
        header('Location: /webbanhang/account/profile');
        exit;
    }

    // ================================================================
    //  UPLOAD AVATAR
    // ================================================================
    public function uploadAvatar()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['avatar'])) {
            header('Location: /webbanhang/account/profile');
            exit;
        }

        $file      = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize   = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Lỗi khi tải file lên!';
            header('Location: /webbanhang/account/profile');
            exit;
        }

        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['flash_error'] = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)!';
            header('Location: /webbanhang/account/profile');
            exit;
        }

        if ($file['size'] > $maxSize) {
            $_SESSION['flash_error'] = 'Kích thước ảnh không được vượt quá 2MB!';
            header('Location: /webbanhang/account/profile');
            exit;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $dest     = 'public/images/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // Xóa avatar cũ nếu có
            $account = $this->accountModel->getAccountById($_SESSION['user_id']);
            if (!empty($account->avatar) && file_exists('public/images/' . $account->avatar)) {
                unlink('public/images/' . $account->avatar);
            }

            $this->accountModel->updateAvatar($_SESSION['user_id'], $filename);
            $_SESSION['avatar'] = $filename;
            $_SESSION['flash_success'] = 'Cập nhật ảnh đại diện thành công!';
        } else {
            $_SESSION['flash_error'] = 'Không thể lưu file ảnh!';
        }

        header('Location: /webbanhang/account/profile');
        exit;
    }

    // ================================================================
    //  ĐỔI MẬT KHẨU
    // ================================================================
    public function changePassword()
    {
        $this->requireLogin();
        include 'app/views/account/change_password.php';
    }

    public function updatePassword()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/account/changePassword');
            exit;
        }

        $id          = $_SESSION['user_id'];
        $oldPassword = $_POST['old_password']     ?? '';
        $newPassword = $_POST['new_password']     ?? '';
        $confirmNew  = $_POST['confirm_password'] ?? '';
        $errors      = [];

        $account = $this->accountModel->getAccountById($id);

        if (!password_verify($oldPassword, $account->password)) {
            $errors[] = 'Mật khẩu hiện tại không đúng!';
        }
        if (strlen($newPassword) < 6) {
            $errors[] = 'Mật khẩu mới tối thiểu 6 ký tự!';
        }
        if ($newPassword !== $confirmNew) {
            $errors[] = 'Xác nhận mật khẩu mới chưa khớp!';
        }

        if (!empty($errors)) {
            include 'app/views/account/change_password.php';
            return;
        }

        $this->accountModel->changePassword($id, $newPassword);
        $_SESSION['flash_success'] = 'Đổi mật khẩu thành công!';
        header('Location: /webbanhang/account/profile');
        exit;
    }

    // ================================================================
    //  QUÊN MẬT KHẨU
    // ================================================================
    public function forgotPassword()
    {
        include 'app/views/account/forgot_password.php';
    }

    public function sendResetLink()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/account/forgotPassword');
            exit;
        }

        $email   = trim($_POST['email'] ?? '');
        $message = '';
        $success = false;

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Vui lòng nhập email hợp lệ!';
        } else {
            $account = $this->accountModel->getAccountByEmail($email);
            // Luôn hiển thị thông báo thành công để tránh lộ thông tin
            if ($account) {
                $token = bin2hex(random_bytes(32));
                $this->accountModel->savePasswordResetToken($email, $token);

                $resetLink = "http://{$_SERVER['HTTP_HOST']}/webbanhang/account/resetPassword?token={$token}";
                $body = "
                    <h2>Đặt lại mật khẩu TechStore</h2>
                    <p>Xin chào <strong>{$account->fullname}</strong>,</p>
                    <p>Nhấn vào link bên dưới để đặt lại mật khẩu:</p>
                    <p><a href='{$resetLink}' style='background:#d70018;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;'>Đặt lại mật khẩu</a></p>
                    <p>Link có hiệu lực trong <strong>1 giờ</strong>.</p>
                    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.</p>
                ";
                $this->sendMail($email, 'Đặt lại mật khẩu TechStore', $body);
            }
            $success = true;
            $message = 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi link đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.';
        }

        include 'app/views/account/forgot_password.php';
    }

    public function resetPassword()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $_SESSION['flash_error'] = 'Token không hợp lệ!';
            header('Location: /webbanhang/account/login');
            exit;
        }

        $reset = $this->accountModel->getPasswordResetByToken($token);
        if (!$reset) {
            $_SESSION['flash_error'] = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!';
            header('Location: /webbanhang/account/forgotPassword');
            exit;
        }

        include 'app/views/account/reset_password.php';
    }

    public function doResetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/account/login');
            exit;
        }

        $token       = $_POST['token']            ?? '';
        $newPassword = $_POST['new_password']     ?? '';
        $confirmNew  = $_POST['confirm_password'] ?? '';
        $errors      = [];

        $reset = $this->accountModel->getPasswordResetByToken($token);
        if (!$reset) {
            $_SESSION['flash_error'] = 'Token không hợp lệ hoặc đã hết hạn!';
            header('Location: /webbanhang/account/forgotPassword');
            exit;
        }

        if (strlen($newPassword) < 6) $errors[] = 'Mật khẩu tối thiểu 6 ký tự!';
        if ($newPassword !== $confirmNew) $errors[] = 'Xác nhận mật khẩu chưa khớp!';

        if (!empty($errors)) {
            include 'app/views/account/reset_password.php';
            return;
        }

        $account = $this->accountModel->getAccountByEmail($reset->email);
        if ($account) {
            $this->accountModel->changePassword($account->id, $newPassword);
            $this->accountModel->deletePasswordResetToken($token);
            $_SESSION['flash_success'] = 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập.';
        }

        header('Location: /webbanhang/account/login');
        exit;
    }

    // ================================================================
    //  QUẢN LÝ NGƯỜI DÙNG (ADMIN)
    // ================================================================
    public function manageUsers()
    {
        $this->requireAdmin();
        $accounts = $this->accountModel->getAllAccounts();
        include 'app/views/account/manage_users.php';
    }

    public function toggleActive()
    {
        $this->requireAdmin();

        $id     = (int)($_GET['id']     ?? 0);
        $status = (int)($_GET['status'] ?? 0);

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID không hợp lệ!';
            header('Location: /webbanhang/account/manageUsers');
            exit;
        }

        // Không cho khóa chính mình
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Bạn không thể khóa tài khoản của chính mình!';
            header('Location: /webbanhang/account/manageUsers');
            exit;
        }

        $this->accountModel->toggleActive($id, $status);
        $_SESSION['flash_success'] = $status ? 'Đã mở khóa tài khoản!' : 'Đã khóa tài khoản!';
        header('Location: /webbanhang/account/manageUsers');
        exit;
    }

    public function updateRole()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/account/manageUsers');
            exit;
        }

        $id   = (int)($_POST['id']   ?? 0);
        $role = $_POST['role'] ?? 'user';

        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Bạn không thể thay đổi role của chính mình!';
            header('Location: /webbanhang/account/manageUsers');
            exit;
        }

        $this->accountModel->updateRole($id, $role);
        $_SESSION['flash_success'] = 'Cập nhật quyền thành công!';
        header('Location: /webbanhang/account/manageUsers');
        exit;
    }

    public function deleteUser()
    {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);

        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Bạn không thể xóa tài khoản của chính mình!';
            header('Location: /webbanhang/account/manageUsers');
            exit;
        }

        $this->accountModel->deleteAccount($id);
        $_SESSION['flash_success'] = 'Đã xóa tài khoản!';
        header('Location: /webbanhang/account/manageUsers');
        exit;
    }

    // ================================================================
    //  HELPER: Yêu cầu đăng nhập
    // ================================================================
    private function requireLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /webbanhang/account/login');
            exit;
        }
    }

    // ================================================================
    //  HELPER: Yêu cầu quyền Admin
    // ================================================================
    private function requireAdmin()
    {
        $this->requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die('<div style="text-align:center;padding:60px;font-family:sans-serif;">
                    <h2 style="color:#d70018;">403 - Không có quyền truy cập</h2>
                    <p>Bạn không có quyền thực hiện thao tác này.</p>
                    <a href="/webbanhang/product" style="color:#d70018;">← Về trang chủ</a>
                 </div>');
        }
    }
}
