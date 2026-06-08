<?php
/**
 * Response Helper — chuẩn hóa tất cả JSON trả về từ API
 */
class Response
{
    /**
     * Gửi phản hồi JSON thành công
     */
    public static function success($data = null, string $message = 'Thành công', int $code = 200): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Gửi phản hồi JSON lỗi
     */
    public static function error(string $message = 'Có lỗi xảy ra', int $code = 400, $errors = null): void
    {
        http_response_code($code);
        $body = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Thiết lập header CORS + JSON cho mọi response
     */
    public static function setHeaders(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Lấy body JSON từ request (dùng cho PUT/POST với raw JSON)
     */
    public static function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
?>
