<?php
/**
 * AuthApiController — Xác thực người dùng
 *
 * POST /api/auth/login     — Đăng nhập
 * POST /api/auth/logout    — Đăng xuất
 * POST /api/auth/register  — Đăng ký
 * GET  /api/auth/me        — Thông tin user hiện tại
 */
class AuthApiController
{
    private $db;

    public function __construct($db)
    {
        Auth::init();
        $this->db = $db;
    }

    public function handle(string $method, $id, $action): void
    {
        $route = $id ?? '';  // 'login', 'logout', 'register', 'me'

        switch ($route) {
            case 'login':
                if ($method === 'POST') { $this->login(); return; }
                break;
            case 'logout':
                if ($method === 'POST') { $this->logout(); return; }
                break;
            case 'register':
                if ($method === 'POST') { $this->register(); return; }
                break;
            case 'me':
                if ($method === 'GET') { $this->me(); return; }
                break;
        }

        Response::error('Auth route không hợp lệ.', 404);
    }

    // POST /api/auth/login
    private function login(): void
    {
        $data     = Response::getJsonInput();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            Response::error('Vui lòng nhập tên đăng nhập và mật khẩu.', 400);
        }

        $stmt = $this->db->prepare("SELECT * FROM account WHERE username = :u AND is_active = 1 LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$user || !password_verify($password, $user->password)) {
            Response::error('Tên đăng nhập hoặc mật khẩu không đúng.', 401);
        }

        // Lưu session
        $_SESSION['user_id']  = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role']     = $user->role;
        $_SESSION['fullname'] = $user->fullname;

        Response::success([
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
                'fullname' => $user->fullname,
                'email'    => $user->email,
                'role'     => $user->role,
                'avatar'   => $user->avatar,
            ],
        ], 'Đăng nhập thành công. Xin chào, ' . $user->fullname . '!');
    }

    // POST /api/auth/logout
    private function logout(): void
    {
        Auth::requireLogin();
        $username = $_SESSION['username'] ?? 'Người dùng';
        session_destroy();
        Response::success(null, 'Đăng xuất thành công. Tạm biệt, ' . $username . '!');
    }

    // POST /api/auth/register
    private function register(): void
    {
        $data = Response::getJsonInput();

        $errors = [];
        $username = trim($data['username'] ?? '');
        $fullname = trim($data['fullname'] ?? '');
        $email    = trim($data['email']    ?? '');
        $password = $data['password']      ?? '';
        $confirm  = $data['confirm_password'] ?? '';

        if (empty($username)) $errors['username'] = 'Tên đăng nhập không được để trống.';
        elseif (strlen($username) < 3) $errors['username'] = 'Tên đăng nhập phải có ít nhất 3 ký tự.';

        if (empty($fullname)) $errors['fullname'] = 'Họ tên không được để trống.';

        if (empty($email)) $errors['email'] = 'Email không được để trống.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ.';

        if (empty($password)) $errors['password'] = 'Mật khẩu không được để trống.';
        elseif (strlen($password) < 6) $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';

        if ($password !== $confirm) $errors['confirm_password'] = 'Xác nhận mật khẩu không khớp.';

        if (!empty($errors)) {
            Response::error('Dữ liệu không hợp lệ.', 422, $errors);
        }

        // Kiểm tra username đã tồn tại
        $stmt = $this->db->prepare("SELECT id FROM account WHERE username = :u");
        $stmt->execute([':u' => $username]);
        if ($stmt->fetch()) {
            Response::error('Tên đăng nhập đã được sử dụng.', 409);
        }

        // Kiểm tra email đã tồn tại
        $stmt = $this->db->prepare("SELECT id FROM account WHERE email = :e");
        $stmt->execute([':e' => $email]);
        if ($stmt->fetch()) {
            Response::error('Email đã được đăng ký.', 409);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            INSERT INTO account (username, fullname, email, password, role, is_active, is_verified)
            VALUES (:u, :fn, :em, :pw, 'user', 1, 1)
        ");
        $stmt->execute([
            ':u'  => $username,
            ':fn' => $fullname,
            ':em' => $email,
            ':pw' => $hashedPassword,
        ]);

        $newId = $this->db->lastInsertId();
        Response::success([
            'id'       => (int)$newId,
            'username' => $username,
            'fullname' => $fullname,
            'email'    => $email,
            'role'     => 'user',
        ], 'Đăng ký thành công! Chào mừng, ' . $fullname . '!', 201);
    }

    // GET /api/auth/me
    private function me(): void
    {
        Auth::requireLogin();
        $userId = Auth::currentUserId();
        $stmt = $this->db->prepare("SELECT id, username, fullname, email, role, avatar, phone, address, created_at FROM account WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$user) {
            Response::error('Không tìm thấy người dùng.', 404);
        }
        Response::success($user, 'Lấy thông tin người dùng thành công.');
    }
}
?>
