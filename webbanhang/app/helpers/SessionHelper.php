<?php
class SessionHelper
{
    // Khởi động session nếu chưa bắt đầu
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Kiểm tra người dùng đã đăng nhập chưa
    public static function isLoggedIn(): bool
    {
        self::start();
        return isset($_SESSION['user_id']) || isset($_SESSION['username']);
    }

    // Kiểm tra người dùng có phải admin không
    public static function isAdmin(): bool
    {
        self::start();
        return self::isLoggedIn()
            && isset($_SESSION['role'])
            && $_SESSION['role'] === 'admin';
    }

    // Lấy vai trò của người dùng, mặc định là 'guest'
    public static function getRole(): string
    {
        self::start();
        return $_SESSION['role'] ?? 'guest';
    }

    // Lấy ID người dùng hiện tại
    public static function getUserId(): ?int
    {
        self::start();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    // Lấy username hiện tại
    public static function getUsername(): ?string
    {
        self::start();
        return $_SESSION['username'] ?? null;
    }

    // Lấy fullname hiện tại
    public static function getFullname(): ?string
    {
        self::start();
        return $_SESSION['fullname'] ?? $_SESSION['username'] ?? null;
    }

    // Yêu cầu đăng nhập — redirect nếu chưa
    public static function requireLogin(string $redirect = '/webbanhang/account/login'): void
    {
        if (!self::isLoggedIn()) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục!';
            header('Location: ' . $redirect);
            exit;
        }
    }

    // Yêu cầu quyền Admin
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            die('<div style="text-align:center;padding:80px;font-family:sans-serif;">
                    <h2 style="color:#d70018;">403 — Không có quyền truy cập</h2>
                    <p>Chức năng này chỉ dành cho Admin.</p>
                    <a href="/webbanhang/product" style="color:#d70018;">← Về trang chủ</a>
                 </div>');
        }
    }
}
