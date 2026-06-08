<?php
class AccountModel
{
    private $conn;
    private $table = 'account';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ----------------------------------------------------------------
    //  Lấy tài khoản theo username
    // ----------------------------------------------------------------
    public function getAccountByUsername($username)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // ----------------------------------------------------------------
    //  Lấy tài khoản theo email
    // ----------------------------------------------------------------
    public function getAccountByEmail($email)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // ----------------------------------------------------------------
    //  Lấy tài khoản theo ID
    // ----------------------------------------------------------------
    public function getAccountById($id)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // ----------------------------------------------------------------
    //  Lấy tài khoản theo remember_token
    // ----------------------------------------------------------------
    public function getAccountByRememberToken($token)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE remember_token = :token LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // ----------------------------------------------------------------
    //  Lấy tài khoản theo verify_token
    // ----------------------------------------------------------------
    public function getAccountByVerifyToken($token)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE verify_token = :token LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // ----------------------------------------------------------------
    //  Đăng ký tài khoản mới
    // ----------------------------------------------------------------
    public function save($username, $fullName, $email, $password, $role = 'user', $verifyToken = null)
    {
        if ($this->getAccountByUsername($username)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table}
                    (username, fullname, email, password, role, is_active, is_verified, verify_token)
                VALUES
                    (:username, :fullname, :email, :password, :role, 1, :is_verified, :verify_token)";

        $stmt = $this->conn->prepare($sql);

        $username     = htmlspecialchars(strip_tags($username));
        $fullName     = htmlspecialchars(strip_tags($fullName));
        $email        = htmlspecialchars(strip_tags($email));
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $role         = in_array($role, ['admin', 'user']) ? $role : 'user';
        $isVerified   = $verifyToken ? 0 : 1;   // Nếu có token thì chưa xác thực

        $stmt->bindParam(':username',     $username);
        $stmt->bindParam(':fullname',     $fullName);
        $stmt->bindParam(':email',        $email);
        $stmt->bindParam(':password',     $passwordHash);
        $stmt->bindParam(':role',         $role);
        $stmt->bindParam(':is_verified',  $isVerified, PDO::PARAM_INT);
        $stmt->bindParam(':verify_token', $verifyToken);

        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Xác thực email (đặt is_verified = 1, xóa token)
    // ----------------------------------------------------------------
    public function verifyEmail($token)
    {
        $account = $this->getAccountByVerifyToken($token);
        if (!$account) return false;

        $sql  = "UPDATE {$this->table} SET is_verified = 1, verify_token = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $account->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Đổi mật khẩu
    // ----------------------------------------------------------------
    public function changePassword($id, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql  = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Cập nhật hồ sơ cá nhân
    // ----------------------------------------------------------------
    public function updateProfile($id, $fullname, $email, $phone, $address)
    {
        $fullname = htmlspecialchars(strip_tags($fullname));
        $email    = htmlspecialchars(strip_tags($email));
        $phone    = htmlspecialchars(strip_tags($phone));
        $address  = htmlspecialchars(strip_tags($address));

        $sql = "UPDATE {$this->table}
                SET fullname = :fullname, email = :email, phone = :phone, address = :address
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email',    $email);
        $stmt->bindParam(':phone',    $phone);
        $stmt->bindParam(':address',  $address);
        $stmt->bindParam(':id',       $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Cập nhật avatar
    // ----------------------------------------------------------------
    public function updateAvatar($id, $avatar)
    {
        $sql  = "UPDATE {$this->table} SET avatar = :avatar WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':id',     $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Lưu remember_token
    // ----------------------------------------------------------------
    public function saveRememberToken($id, $token)
    {
        $sql  = "UPDATE {$this->table} SET remember_token = :token WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id',    $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Xóa remember_token (khi logout)
    // ----------------------------------------------------------------
    public function clearRememberToken($id)
    {
        $sql  = "UPDATE {$this->table} SET remember_token = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Lưu token đặt lại mật khẩu
    // ----------------------------------------------------------------
    public function savePasswordResetToken($email, $token)
    {
        // Xóa token cũ nếu có
        $del = $this->conn->prepare("DELETE FROM password_resets WHERE email = :email");
        $del->bindParam(':email', $email);
        $del->execute();

        $sql  = "INSERT INTO password_resets (email, token) VALUES (:email, :token)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Lấy bản ghi reset password theo token (hết hạn sau 1 giờ)
    // ----------------------------------------------------------------
    public function getPasswordResetByToken($token)
    {
        $sql  = "SELECT * FROM password_resets
                 WHERE token = :token
                   AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // ----------------------------------------------------------------
    //  Xóa token reset sau khi dùng
    // ----------------------------------------------------------------
    public function deletePasswordResetToken($token)
    {
        $sql  = "DELETE FROM password_resets WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Lấy tất cả tài khoản (dành cho Admin)
    // ----------------------------------------------------------------
    public function getAllAccounts()
    {
        $sql  = "SELECT id, username, fullname, email, role, is_active, is_verified, avatar, created_at
                 FROM {$this->table}
                 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ----------------------------------------------------------------
    //  Khóa / Mở khóa tài khoản (Admin)
    // ----------------------------------------------------------------
    public function toggleActive($id, $status)
    {
        $status = $status ? 1 : 0;
        $sql    = "UPDATE {$this->table} SET is_active = :status WHERE id = :id";
        $stmt   = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':id',     $id,     PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Xóa tài khoản (Admin)
    // ----------------------------------------------------------------
    public function deleteAccount($id)
    {
        $sql  = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    //  Cập nhật role (Admin)
    // ----------------------------------------------------------------
    public function updateRole($id, $role)
    {
        $role = in_array($role, ['admin', 'user']) ? $role : 'user';
        $sql  = "UPDATE {$this->table} SET role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id',   $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
