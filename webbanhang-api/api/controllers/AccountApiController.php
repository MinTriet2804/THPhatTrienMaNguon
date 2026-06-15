<?php
/**
 * AccountApiController — Quản lý tài khoản người dùng
 *
 * === Dành cho User đã đăng nhập (JWT) ===
 * GET    /api/account/profile          — Xem hồ sơ cá nhân
 * PUT    /api/account/profile          — Cập nhật hồ sơ cá nhân
 * POST   /api/account/change-password  — Đổi mật khẩu
 *
 * === Dành cho Admin (JWT + role=admin) ===
 * GET    /api/account/users            — Danh sách tất cả người dùng
 * GET    /api/account/users/{id}       — Chi tiết người dùng
 * POST   /api/account/users/{id}/toggle-active  — Khóa/Mở khóa tài khoản
 * PUT    /api/account/users/{id}/role  — Cập nhật quyền
 * DELETE /api/account/users/{id}       — Xóa tài khoản
 */
class AccountApiController
{
    private $accountModel;
    private $db;

    public function __construct($db)
    {
        $this->db           = $db;
        $this->accountModel = new AccountModel($db);
    }

    /**
     * Routing nội bộ:
     *   $id    = segment thứ 2 sau /api/account/
     *   $action = segment thứ 3
     *
     * Ví dụ:
     *   GET  /api/account/profile          → $id='profile', $action=null
     *   PUT  /api/account/profile          → $id='profile', $action=null
     *   GET  /api/account/users            → $id='users',   $action=null
     *   GET  /api/account/users/5          → $id='users',   $action='5'
     *   POST /api/account/users/5/toggle-active → $id='users', $action='5', extra via query
     *
     * Vì router hiện tại chỉ parse đến $action (segment 3), với
     * /api/account/users/5/toggle-active thì:
     *   $id='users', $action='5' và thông tin 'toggle-active' mất.
     * => Dùng query string workaround: /api/account/users/5?action=toggle-active
     * HOẶC map theo method + $action pattern dưới đây.
     */
    public function handle(string $method, $id, $action): void
    {
        Auth::requireLogin(); // Mọi route đều cần JWT

        switch ($id) {
            // ------- Profile cá nhân -------
            case 'profile':
                if ($method === 'GET')  { $this->getProfile(); return; }
                if ($method === 'PUT')  { $this->updateProfile(); return; }
                break;

            // ------- Đổi mật khẩu -------
            case 'change-password':
                if ($method === 'POST') { $this->changePassword(); return; }
                break;

            // ------- Quản lý người dùng (Admin) -------
            case 'users':
                $this->handleUsers($method, $action);
                return;

            default:
                Response::error('Route Account không hợp lệ.', 404);
        }

        Response::error('Method không được hỗ trợ cho route này.', 405);
    }

    // ----------------------------------------------------------------
    //  Sub-router cho /api/account/users/*
    // ----------------------------------------------------------------
    private function handleUsers(string $method, $subId): void
    {
        Auth::requireAdmin();

        // Xử lý sub-action qua query string: ?do=toggle-active hoặc ?do=role
        $do = $_GET['do'] ?? null;

        if ($subId === null) {
            // GET /api/account/users
            if ($method === 'GET') { $this->listUsers(); return; }
        } elseif (is_numeric($subId)) {
            $userId = (int)$subId;

            if ($method === 'GET' && $do === null) {
                // GET /api/account/users/5
                $this->showUser($userId);
                return;
            }
            if ($method === 'POST' && $do === 'toggle-active') {
                // POST /api/account/users/5?do=toggle-active
                $this->toggleActive($userId);
                return;
            }
            if ($method === 'PUT' && $do === 'role') {
                // PUT /api/account/users/5?do=role
                $this->updateRole($userId);
                return;
            }
            if ($method === 'DELETE') {
                // DELETE /api/account/users/5
                $this->deleteUser($userId);
                return;
            }
        }

        Response::error('Route quản lý người dùng không hợp lệ.', 404);
    }

    // ================================================================
    //  PROFILE CÁ NHÂN
    // ================================================================

    // GET /api/account/profile
    private function getProfile(): void
    {
        $userId = Auth::currentUserId();
        $user   = $this->accountModel->getAccountById($userId);

        if (!$user) {
            Response::error('Không tìm thấy thông tin người dùng.', 404);
        }

        Response::success($this->sanitizeUser($user), 'Lấy hồ sơ cá nhân thành công.');
    }

    // PUT /api/account/profile
    // Body: { "fullname": "...", "email": "...", "phone": "...", "address": "..." }
    private function updateProfile(): void
    {
        $userId = Auth::currentUserId();
        $user   = $this->accountModel->getAccountById($userId);

        if (!$user) {
            Response::error('Không tìm thấy thông tin người dùng.', 404);
        }

        $data   = Response::getJsonInput();
        $errors = $this->validateProfile($data, $userId);
        if (!empty($errors)) {
            Response::error('Dữ liệu không hợp lệ.', 422, $errors);
        }

        $fullname = trim($data['fullname'] ?? $user->fullname);
        $email    = trim($data['email']    ?? $user->email);
        $phone    = trim($data['phone']    ?? $user->phone ?? '');
        $address  = trim($data['address']  ?? $user->address ?? '');

        $result = $this->accountModel->updateProfile($userId, $fullname, $email, $phone, $address);

        if (!$result) {
            Response::error('Cập nhật hồ sơ thất bại. Vui lòng thử lại.', 500);
        }

        $updated = $this->accountModel->getAccountById($userId);
        Response::success($this->sanitizeUser($updated), 'Cập nhật hồ sơ cá nhân thành công.');
    }

    // POST /api/account/change-password
    // Body: { "current_password": "...", "new_password": "...", "confirm_password": "..." }
    private function changePassword(): void
    {
        $userId = Auth::currentUserId();
        $user   = $this->accountModel->getAccountById($userId);

        if (!$user) {
            Response::error('Không tìm thấy thông tin người dùng.', 404);
        }

        $data           = Response::getJsonInput();
        $currentPwd     = $data['current_password']  ?? '';
        $newPwd         = $data['new_password']       ?? '';
        $confirmPwd     = $data['confirm_password']   ?? '';

        if ($currentPwd === '' || $newPwd === '' || $confirmPwd === '') {
            Response::error('Vui lòng điền đầy đủ mật khẩu hiện tại, mật khẩu mới và xác nhận.', 400);
        }

        // Kiểm tra mật khẩu hiện tại bằng password_verify
        if (!password_verify($currentPwd, $user->password)) {
            Response::error('Mật khẩu hiện tại không chính xác.', 401);
        }

        if (strlen($newPwd) < 6) {
            Response::error('Mật khẩu mới phải có ít nhất 6 ký tự.', 422);
        }

        if ($newPwd !== $confirmPwd) {
            Response::error('Mật khẩu xác nhận không khớp.', 422);
        }

        if ($currentPwd === $newPwd) {
            Response::error('Mật khẩu mới phải khác mật khẩu hiện tại.', 422);
        }

        // Mã hóa mật khẩu mới bằng password_hash trong changePassword()
        $result = $this->accountModel->changePassword($userId, $newPwd);

        if (!$result) {
            Response::error('Đổi mật khẩu thất bại. Vui lòng thử lại.', 500);
        }

        Response::success(null, 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại với mật khẩu mới.');
    }

    // ================================================================
    //  QUẢN LÝ NGƯỜI DÙNG (ADMIN)
    // ================================================================

    // GET /api/account/users
    private function listUsers(): void
    {
        $users = $this->accountModel->getAllAccounts();
        $list  = array_map([$this, 'sanitizeUser'], $users);

        Response::success([
            'users' => $list,
            'count' => count($list),
        ], 'Lấy danh sách người dùng thành công.');
    }

    // GET /api/account/users/{id}
    private function showUser(int $id): void
    {
        $user = $this->accountModel->getAccountById($id);
        if (!$user) {
            Response::error('Không tìm thấy người dùng.', 404);
        }
        Response::success($this->sanitizeUser($user), 'Lấy thông tin người dùng thành công.');
    }

    // POST /api/account/users/{id}?do=toggle-active
    // Body: { "is_active": true/false }
    private function toggleActive(int $id): void
    {
        $user = $this->accountModel->getAccountById($id);
        if (!$user) {
            Response::error('Không tìm thấy người dùng.', 404);
        }

        // Không cho khóa chính mình
        if ($id === Auth::currentUserId()) {
            Response::error('Bạn không thể khóa chính tài khoản của mình.', 400);
        }

        $data     = Response::getJsonInput();
        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : !(bool)$user->is_active;

        $this->accountModel->toggleActive($id, $isActive);

        $status = $isActive ? 'kích hoạt' : 'khóa';
        Response::success([
            'id'        => $id,
            'username'  => $user->username,
            'is_active' => $isActive,
        ], "Đã {$status} tài khoản '{$user->username}' thành công.");
    }

    // PUT /api/account/users/{id}?do=role
    // Body: { "role": "admin|user" }
    private function updateRole(int $id): void
    {
        $user = $this->accountModel->getAccountById($id);
        if (!$user) {
            Response::error('Không tìm thấy người dùng.', 404);
        }

        // Không cho đổi role của chính mình
        if ($id === Auth::currentUserId()) {
            Response::error('Bạn không thể thay đổi quyền của chính mình.', 400);
        }

        $data = Response::getJsonInput();
        $role = $data['role'] ?? '';

        if (!in_array($role, ['admin', 'user'])) {
            Response::error('Quyền không hợp lệ. Chỉ chấp nhận: admin, user.', 422);
        }

        $this->accountModel->updateRole($id, $role);

        Response::success([
            'id'       => $id,
            'username' => $user->username,
            'role'     => $role,
        ], "Đã cập nhật quyền của '{$user->username}' thành '{$role}'.");
    }

    // DELETE /api/account/users/{id}
    private function deleteUser(int $id): void
    {
        $user = $this->accountModel->getAccountById($id);
        if (!$user) {
            Response::error('Không tìm thấy người dùng.', 404);
        }

        // Không cho xóa chính mình
        if ($id === Auth::currentUserId()) {
            Response::error('Bạn không thể xóa chính tài khoản của mình.', 400);
        }

        $this->accountModel->deleteAccount($id);

        Response::success(null, "Đã xóa tài khoản '{$user->username}' thành công.");
    }

    // ================================================================
    //  HELPER: Loại bỏ password và sensitive fields trước khi trả về
    // ================================================================
    private function sanitizeUser($user): array
    {
        return [
            'id'          => (int)$user->id,
            'username'    => $user->username,
            'fullname'    => $user->fullname,
            'email'       => $user->email,
            'phone'       => $user->phone ?? null,
            'address'     => $user->address ?? null,
            'role'        => $user->role,
            'is_active'   => (bool)$user->is_active,
            'is_verified' => (bool)($user->is_verified ?? false),
            'avatar'      => $user->avatar ?? null,
            'created_at'  => $user->created_at ?? null,
        ];
    }

    // HELPER: Validate profile
    private function validateProfile(array $data, int $currentUserId): array
    {
        $errors = [];

        $fullname = trim($data['fullname'] ?? '');
        if ($fullname !== '' && mb_strlen($fullname) > 100) {
            $errors['fullname'] = 'Họ tên không được vượt quá 100 ký tự.';
        }

        $email = trim($data['email'] ?? '');
        if ($email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Địa chỉ email không hợp lệ.';
            } else {
                // Kiểm tra email chưa bị dùng bởi user khác
                $existing = $this->accountModel->getAccountByEmail($email);
                if ($existing && (int)$existing->id !== $currentUserId) {
                    $errors['email'] = 'Email đã được sử dụng bởi tài khoản khác.';
                }
            }
        }

        $phone = trim($data['phone'] ?? '');
        if ($phone !== '' && !preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }

        return $errors;
    }
}
?>
