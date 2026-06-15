<?php
/**
 * Auth Helper — Xác thực và phân quyền dựa trên JWT.
 *
 * Luồng:
 *   1. Client gửi: Authorization: Bearer <token>
 *   2. Auth::requireLogin()  → verify token, trả 401 nếu thiếu/sai
 *   3. Auth::requireAdmin()  → kiểm tra role = 'admin', trả 403 nếu không đủ quyền
 */
class Auth
{
    /** Payload đã giải mã, cache trong request hiện tại */
    private static ?array $currentPayload = null;

    // ----------------------------------------------------------------
    //  Lấy payload từ Bearer token (cache để không gọi lại nhiều lần)
    // ----------------------------------------------------------------
    public static function getPayload(): ?array
    {
        if (self::$currentPayload !== null) {
            return self::$currentPayload;
        }

        $token = JwtHelper::getBearerToken();
        if ($token === null) {
            return null;
        }

        self::$currentPayload = JwtHelper::verify($token);
        return self::$currentPayload;
    }

    // ----------------------------------------------------------------
    //  Kiểm tra đã đăng nhập chưa
    // ----------------------------------------------------------------
    public static function isLoggedIn(): bool
    {
        return self::getPayload() !== null;
    }

    // ----------------------------------------------------------------
    //  Kiểm tra có phải Admin không
    // ----------------------------------------------------------------
    public static function isAdmin(): bool
    {
        $payload = self::getPayload();
        return $payload !== null && isset($payload['role']) && $payload['role'] === 'admin';
    }

    // ----------------------------------------------------------------
    //  Bắt buộc đăng nhập — trả lỗi nếu chưa có token hợp lệ
    // ----------------------------------------------------------------
    public static function requireLogin(): void
    {
        $token = JwtHelper::getBearerToken();

        if ($token === null) {
            Response::error('Unauthorized. Vui lòng đăng nhập và gửi token trong header: Authorization: Bearer <token>', 401);
        }

        $payload = JwtHelper::verify($token);
        if ($payload === null) {
            Response::error('Token không hợp lệ hoặc đã hết hạn. Vui lòng đăng nhập lại.', 401);
        }

        self::$currentPayload = $payload;
    }

    // ----------------------------------------------------------------
    //  Bắt buộc quyền Admin
    // ----------------------------------------------------------------
    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (!self::isAdmin()) {
            Response::error('Forbidden. Chức năng này chỉ dành cho Admin.', 403);
        }
    }

    // ----------------------------------------------------------------
    //  Lấy ID người dùng hiện tại từ token
    // ----------------------------------------------------------------
    public static function currentUserId(): ?int
    {
        $payload = self::getPayload();
        return isset($payload['user_id']) ? (int)$payload['user_id'] : null;
    }

    // ----------------------------------------------------------------
    //  Lấy username người dùng hiện tại từ token
    // ----------------------------------------------------------------
    public static function currentUsername(): ?string
    {
        $payload = self::getPayload();
        return $payload['username'] ?? null;
    }

    // ----------------------------------------------------------------
    //  Lấy role người dùng hiện tại từ token
    // ----------------------------------------------------------------
    public static function currentRole(): ?string
    {
        $payload = self::getPayload();
        return $payload['role'] ?? null;
    }

    // ----------------------------------------------------------------
    //  Giữ lại để tương thích với code cũ (CartApiController gọi Auth::init())
    // ----------------------------------------------------------------
    public static function init(): void
    {
        // Không cần làm gì — JWT không dùng session
    }
}
?>
