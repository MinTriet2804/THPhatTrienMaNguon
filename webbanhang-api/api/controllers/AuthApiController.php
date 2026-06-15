<?php
/**
 * AuthApiController — Xác thực người dùng bằng JWT
 *
 * POST   /api/auth/register        — Đăng ký tài khoản mới
 * POST   /api/auth/login           — Đăng nhập, trả về JWT token
 * POST   /api/auth/logout          — Đăng xuất (thông báo client xóa token)
 * GET    /api/auth/me              — Xem thông tin người dùng đang đăng nhập  [JWT]
 * POST   /api/auth/forgot-password — Quên mật khẩu (mô phỏng)
 * POST   /api/auth/reset-password  — Đặt lại mật khẩu bằng token reset
 */
class AuthApiController
{
    private $accountModel;
    private $db;

    public function __construct($db)
    {
        $this->db           = $db;
        $this->accountModel = new AccountModel($db);
    }

    public function handle(string $method, $id, $action): void
    {
        // $id đóng vai trò action phụ: /api/auth/{action}
        switch ($id) {
            case 'login':
                if ($method === 'POST') { $this->login(); return; }
                break;

            case 'register':
                if ($method === 'POST') { $this->register(); return; }
                break;

            case 'logout':
                if ($method === 'POST') { $this->logout(); return; }
                break;

            case 'me':
                if ($method === 'GET') { $this->me(); return; }
                break;

            case 'forgot-password':
                if ($method === 'POST') { $this->forgotPassword(); return; }
                break;

            case 'reset-password':
                if ($method === 'POST') { $this->resetPassword(); return; }
                break;
        }

        Response::error('Route Auth không hợp lệ hoặc method không được hỗ trợ.', 404);
    }

    // ----------------------------------------------------------------
    //  POST /api/auth/register
    // ----------------------------------------------------------------
    private function register(): void
    {
        $data = Response::getJsonInput();

        $errors = $this->validateRegister($data);
        if (!empty($errors)) {
            Response::error('Dữ liệu đăng ký không hợp lệ.', 422, $errors);
        }

        $username = trim($data['username']);
        $fullname = trim($data['fullname']);
        $email    = trim($data['email']);
        $password = $data['password'];

        // Kiểm tra username/email đã tồn tại
        if ($this->accountModel->getAccountByUsername($username)) {
            Response::error('Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.', 409);
        }
        if ($this->accountModel->getAccountByEmail($email)) {
            Response::error('Email đã được sử dụng. Vui lòng dùng email khác.', 409);
        }

        // Mật khẩu được mã hóa bằng password_hash trong AccountModel::save()
        $result = $this->accountModel->save($username, $fullname, $email, $password, 'user', null);

        if (!$result) {
            Response::error('Đăng ký thất bại. Vui lòng thử lại.', 500);
        }

        $user = $this->accountModel->getAccountByUsername($username);

        Response::success([
            'id'       => $user->id,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'email'    => $user->email,
            'role'     => $user->role,
        ], 'Đăng ký tài khoản thành công.', 201);
    }

    // ----------------------------------------------------------------
    //  POST /api/auth/login
    // ----------------------------------------------------------------
    private function login(): void
    {
        $data     = Response::getJsonInput();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $password === '') {
            Response::error('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.', 400);
        }

        $user = $this->accountModel->getAccountByUsername($username);

        // Dùng password_verify để kiểm tra mật khẩu đã hash
        if (!$user || !password_verify($password, $user->password)) {
            Response::error('Tên đăng nhập hoặc mật khẩu không chính xác.', 401);
        }

        if (!$user->is_active) {
            Response::error('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.', 403);
        }

        // Tạo JWT token — KHÔNG chứa mật khẩu
        $token = JwtHelper::generate([
            'user_id'  => (int)$user->id,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'email'    => $user->email,
            'role'     => $user->role,
        ]);

        Response::success([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => JwtHelper::getExpireSeconds(),
            'user' => [
                'id'       => (int)$user->id,
                'username' => $user->username,
                'fullname' => $user->fullname,
                'email'    => $user->email,
                'role'     => $user->role,
                'avatar'   => $user->avatar ?? null,
            ],
        ], 'Đăng nhập thành công.');
    }

    // ----------------------------------------------------------------
    //  POST /api/auth/logout
    // ----------------------------------------------------------------
    private function logout(): void
    {
        // JWT là stateless — server không lưu trạng thái session
        // Client phải tự xóa token khỏi storage
        // (Trong production nên có token blacklist, ở đây mô phỏng đơn giản)
        Response::success(null, 'Đăng xuất thành công. Vui lòng xóa token ở phía client.');
    }

    // ----------------------------------------------------------------
    //  GET /api/auth/me  [Yêu cầu JWT]
    // ----------------------------------------------------------------
    private function me(): void
    {
        Auth::requireLogin();

        $userId = Auth::currentUserId();
        $user   = $this->accountModel->getAccountById($userId);

        if (!$user) {
            Response::error('Không tìm thấy thông tin người dùng.', 404);
        }

        Response::success([
            'id'          => (int)$user->id,
            'username'    => $user->username,
            'fullname'    => $user->fullname,
            'email'       => $user->email,
            'phone'       => $user->phone ?? null,
            'address'     => $user->address ?? null,
            'role'        => $user->role,
            'is_active'   => (bool)$user->is_active,
            'is_verified' => (bool)$user->is_verified,
            'avatar'      => $user->avatar ?? null,
            'created_at'  => $user->created_at ?? null,
        ], 'Lấy thông tin người dùng thành công.');
    }

    // ----------------------------------------------------------------
    //  POST /api/auth/forgot-password
    //  Body: { "email": "user@example.com" }
    // ----------------------------------------------------------------
    private function forgotPassword(): void
    {
        $data  = Response::getJsonInput();
        $email = trim($data['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Vui lòng nhập địa chỉ email hợp lệ.', 400);
        }

        $user = $this->accountModel->getAccountByEmail($email);

        // Luôn trả về 200 để tránh lộ thông tin email tồn tại hay không
        if (!$user) {
            Response::success(null, 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.');
        }

        // Tạo reset token ngẫu nhiên
        $resetToken = bin2hex(random_bytes(32));

        // Lưu vào bảng password_resets (hết hạn sau 1 giờ)
        $this->accountModel->savePasswordResetToken($email, $resetToken);

        // === MÔ PHỎNG GỬI EMAIL ===
        // Trong thực tế: gửi email chứa link reset
        // http://localhost:8080/webbanhang-api/api/auth/reset-password
        // Body: { "token": "<resetToken>", "new_password": "..." }

        Response::success([
            'reset_token'  => $resetToken,   // Trả về cho mục đích demo/test
            'expires_in'   => '1 giờ',
            'note'         => '[MÔ PHỎNG] Trong thực tế, token sẽ được gửi qua email thay vì trả về API.',
            'how_to_reset' => 'POST /api/auth/reset-password với body: { "token": "<token>", "new_password": "<mật khẩu mới>" }',
        ], 'Yêu cầu đặt lại mật khẩu đã được ghi nhận.');
    }

    // ----------------------------------------------------------------
    //  POST /api/auth/reset-password
    //  Body: { "token": "...", "new_password": "...", "confirm_password": "..." }
    // ----------------------------------------------------------------
    private function resetPassword(): void
    {
        $data        = Response::getJsonInput();
        $token       = trim($data['token'] ?? '');
        $newPassword = $data['new_password'] ?? '';
        $confirm     = $data['confirm_password'] ?? '';

        if ($token === '') {
            Response::error('Token đặt lại mật khẩu không được để trống.', 400);
        }
        if (strlen($newPassword) < 6) {
            Response::error('Mật khẩu mới phải có ít nhất 6 ký tự.', 422);
        }
        if ($newPassword !== $confirm) {
            Response::error('Mật khẩu xác nhận không khớp.', 422);
        }

        // Tìm bản ghi reset (chưa hết hạn 1 giờ)
        $record = $this->accountModel->getPasswordResetByToken($token);
        if (!$record) {
            Response::error('Token không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu đặt lại mật khẩu mới.', 400);
        }

        $user = $this->accountModel->getAccountByEmail($record->email);
        if (!$user) {
            Response::error('Tài khoản không tồn tại.', 404);
        }

        // Cập nhật mật khẩu mới (password_hash trong changePassword)
        $this->accountModel->changePassword($user->id, $newPassword);

        // Xóa token sau khi dùng
        $this->accountModel->deletePasswordResetToken($token);

        Response::success(null, 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.');
    }

    // ----------------------------------------------------------------
    //  Validate dữ liệu đăng ký
    // ----------------------------------------------------------------
    private function validateRegister(array $data): array
    {
        $errors = [];

        $username = trim($data['username'] ?? '');
        if ($username === '') {
            $errors['username'] = 'Tên đăng nhập không được để trống.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $errors['username'] = 'Tên đăng nhập chỉ được chứa chữ, số, dấu gạch dưới (3-50 ký tự).';
        }

        $fullname = trim($data['fullname'] ?? '');
        if ($fullname === '') {
            $errors['fullname'] = 'Họ tên không được để trống.';
        } elseif (mb_strlen($fullname) > 100) {
            $errors['fullname'] = 'Họ tên không được vượt quá 100 ký tự.';
        }

        $email = trim($data['email'] ?? '');
        if ($email === '') {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Địa chỉ email không hợp lệ.';
        }

        $password = $data['password'] ?? '';
        if ($password === '') {
            $errors['password'] = 'Mật khẩu không được để trống.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        $confirm = $data['confirm_password'] ?? '';
        if ($confirm !== '' && $password !== $confirm) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
        }

        return $errors;
    }
}
?>
