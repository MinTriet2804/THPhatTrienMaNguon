<?php
/**
 * Auth Helper — kiểm tra session, phân quyền cho API
 */
class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn(): bool
    {
        self::init();
        return isset($_SESSION['user_id']) || isset($_SESSION['username']);
    }

    public static function isAdmin(): bool
    {
        self::init();
        return self::isLoggedIn()
            && isset($_SESSION['role'])
            && $_SESSION['role'] === 'admin';
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            Response::error('Bạn cần đăng nhập để thực hiện thao tác này.', 401);
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            Response::error('Bạn không có quyền thực hiện thao tác này.', 403);
        }
    }

    public static function currentUserId(): ?int
    {
        self::init();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }
}
?>
