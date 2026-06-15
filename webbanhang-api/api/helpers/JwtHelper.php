<?php
/**
 * JwtHelper — Xử lý JSON Web Token (JWT) thuần PHP, không cần thư viện ngoài.
 *
 * Thuật toán: HMAC-SHA256 (HS256)
 * Cấu trúc: header.payload.signature (Base64URL encoded)
 */
class JwtHelper
{
    /** Secret key — đổi thành chuỗi ngẫu nhiên dài trong production */
    private static string $secretKey = 'TechStore@JWT_Secret_Key_2024!#$%';

    /** Thời gian hết hạn mặc định: 24 giờ */
    private static int $expireSeconds = 86400;

    // ----------------------------------------------------------------
    //  Tạo token từ payload
    // ----------------------------------------------------------------
    public static function generate(array $payload): string
    {
        // Không được chứa mật khẩu trong token
        unset($payload['password']);

        $header = self::base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ]));

        // Bổ sung claims chuẩn
        $payload['iat'] = time();                              // issued at
        $payload['exp'] = time() + self::$expireSeconds;      // expires at
        $payload['jti'] = bin2hex(random_bytes(8));            // unique id

        $payloadEncoded = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        $signature = self::sign($header . '.' . $payloadEncoded);

        return $header . '.' . $payloadEncoded . '.' . $signature;
    }

    // ----------------------------------------------------------------
    //  Xác minh và giải mã token — trả về payload hoặc null nếu lỗi
    // ----------------------------------------------------------------
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // Kiểm tra chữ ký
        $expectedSig = self::sign($header . '.' . $payload);
        if (!hash_equals($expectedSig, $signature)) {
            return null; // Chữ ký không hợp lệ
        }

        // Giải mã payload
        $data = json_decode(self::base64UrlDecode($payload), true);
        if (!is_array($data)) {
            return null;
        }

        // Kiểm tra hết hạn
        if (isset($data['exp']) && $data['exp'] < time()) {
            return null; // Token đã hết hạn
        }

        return $data;
    }

    // ----------------------------------------------------------------
    //  Lấy token từ header Authorization: Bearer <token>
    // ----------------------------------------------------------------
    public static function getBearerToken(): ?string
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = $_SERVER['Authorization'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Normalize key
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = $requestHeaders['Authorization'];
            }
        }

        if ($headers === null) {
            return null;
        }

        if (preg_match('/Bearer\s+(.+)$/i', $headers, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    // ----------------------------------------------------------------
    //  Lấy payload từ request hiện tại (tiện ích)
    // ----------------------------------------------------------------
    public static function getPayloadFromRequest(): ?array
    {
        $token = self::getBearerToken();
        if ($token === null) {
            return null;
        }
        return self::verify($token);
    }

    // ----------------------------------------------------------------
    //  Lấy thời gian hết hạn (giây) — tiện cho response
    // ----------------------------------------------------------------
    public static function getExpireSeconds(): int
    {
        return self::$expireSeconds;
    }

    // ----------------------------------------------------------------
    //  Nội bộ: ký chuỗi
    // ----------------------------------------------------------------
    private static function sign(string $data): string
    {
        return self::base64UrlEncode(
            hash_hmac('sha256', $data, self::$secretKey, true)
        );
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>
