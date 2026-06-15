<?php
/**
 * API Entry Point — /api/index.php
 * Tất cả request tới /api/* đều được xử lý tại đây
 *
 * === Xác thực ===
 *   Gửi JWT token trong header:
 *   Authorization: Bearer <token>
 *
 * === Route patterns ===
 *
 * [Auth]
 *   POST   /api/auth/register
 *   POST   /api/auth/login
 *   POST   /api/auth/logout
 *   GET    /api/auth/me                        [JWT]
 *   POST   /api/auth/forgot-password
 *   POST   /api/auth/reset-password
 *
 * [Account — User]
 *   GET    /api/account/profile                [JWT]
 *   PUT    /api/account/profile                [JWT]
 *   POST   /api/account/change-password        [JWT]
 *
 * [Account — Admin]
 *   GET    /api/account/users                  [JWT + Admin]
 *   GET    /api/account/users/{id}             [JWT + Admin]
 *   POST   /api/account/users/{id}?do=toggle-active  [JWT + Admin]
 *   PUT    /api/account/users/{id}?do=role     [JWT + Admin]
 *   DELETE /api/account/users/{id}             [JWT + Admin]
 *
 * [Products]
 *   GET    /api/products
 *   GET    /api/products/{id}
 *   GET    /api/products/search?q=...
 *   GET    /api/products/filter?category_id=...
 *   GET    /api/products/sort?order=asc|desc
 *   POST   /api/products                       [JWT + Admin]
 *   PUT    /api/products/{id}                  [JWT + Admin]
 *   DELETE /api/products/{id}                  [JWT + Admin]
 *
 * [Categories]
 *   GET    /api/categories
 *   GET    /api/categories/{id}
 *   POST   /api/categories                     [JWT + Admin]
 *   PUT    /api/categories/{id}                [JWT + Admin]
 *   DELETE /api/categories/{id}                [JWT + Admin]
 *
 * [Cart]
 *   GET    /api/cart                           [JWT]
 *   GET    /api/cart/total                     [JWT]
 *   POST   /api/cart                           [JWT]
 *   PUT    /api/cart/{id}                      [JWT]
 *   DELETE /api/cart/{id}                      [JWT]
 *   DELETE /api/cart                           [JWT]
 *
 * [Orders]
 *   GET    /api/orders                         [JWT]
 *   GET    /api/orders/{id}                    [JWT]
 *   POST   /api/orders                         [JWT]
 *   PUT    /api/orders/{id}/status             [JWT + Admin]
 *   DELETE /api/orders/{id}                    [JWT]
 *
 * [Payments]
 *   POST   /api/payments                       [JWT]
 *   GET    /api/payments/{order_id}            [JWT]
 */

// ── Thiết lập header và CORS ─────────────────────────────────────────
require_once __DIR__ . '/helpers/Response.php';
Response::setHeaders();

// ── Load helpers ──────────────────────────────────────────────────────
require_once __DIR__ . '/helpers/JwtHelper.php';
require_once __DIR__ . '/helpers/Auth.php';

// ── Load config & models ─────────────────────────────────────────────
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/ProductModel.php';
require_once __DIR__ . '/../app/models/CategoryModel.php';
require_once __DIR__ . '/../app/models/CartModel.php';
require_once __DIR__ . '/../app/models/OrderModel.php';
require_once __DIR__ . '/../app/models/AccountModel.php';

// ── Load API Controllers ─────────────────────────────────────────────
require_once __DIR__ . '/controllers/AuthApiController.php';
require_once __DIR__ . '/controllers/AccountApiController.php';
require_once __DIR__ . '/controllers/ProductApiController.php';
require_once __DIR__ . '/controllers/CategoryApiController.php';
require_once __DIR__ . '/controllers/CartApiController.php';
require_once __DIR__ . '/controllers/OrderApiController.php';
require_once __DIR__ . '/controllers/PaymentApiController.php';

// ── Parse URL: /api/<resource>[/<id>][/<action>] ─────────────────────
$requestUri    = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Bóc phần /api/ ra khỏi path
$apiPos = strpos($requestUri, '/api/');
if ($apiPos !== false) {
    $path = substr($requestUri, $apiPos + 5); // bỏ '/api/'
} else {
    $path = ltrim($requestUri, '/');
    if (strpos($path, 'api/') === 0) {
        $path = substr($path, 4);
    }
}

// Tách query string
$queryPos = strpos($path, '?');
if ($queryPos !== false) {
    $path = substr($path, 0, $queryPos);
}

$path     = trim($path, '/');
$segments = explode('/', $path);

$resource = $segments[0] ?? '';
$id       = $segments[1] ?? null;   // có thể là số hoặc từ khóa
$action   = $segments[2] ?? null;   // vd: 'status'

// ── Khởi tạo kết nối DB ───────────────────────────────────────────────
$db = (new Database())->getConnection();

// ── Dispatch ─────────────────────────────────────────────────────────
switch ($resource) {
    case '':
        Response::success([
            'title'   => 'TechStore Web API',
            'version' => 'v2.0',
            'status'  => 'Running',
            'auth'    => 'JWT (Authorization: Bearer <token>)',
            'endpoints' => [
                'auth'       => '/api/auth/{login|register|logout|me|forgot-password|reset-password}',
                'account'    => '/api/account/{profile|change-password|users}',
                'products'   => '/api/products',
                'categories' => '/api/categories',
                'cart'       => '/api/cart',
                'orders'     => '/api/orders',
                'payments'   => '/api/payments',
            ],
        ], 'TechStore API đang hoạt động.');
        break;

    case 'auth':
        $ctrl = new AuthApiController($db);
        $ctrl->handle($requestMethod, $id, $action);
        break;

    case 'account':
        $ctrl = new AccountApiController($db);
        $ctrl->handle($requestMethod, $id, $action);
        break;

    case 'products':
        $ctrl = new ProductApiController($db);
        $ctrl->handle($requestMethod, $id, $action);
        break;

    case 'categories':
        $ctrl = new CategoryApiController($db);
        $ctrl->handle($requestMethod, $id, $action);
        break;

    case 'cart':
        $ctrl = new CartApiController($db);
        $ctrl->handle($requestMethod, $id, $action);
        break;

    case 'orders':
        $ctrl = new OrderApiController($db);
        $ctrl->handle($requestMethod, $id, $action);
        break;

    case 'payments':
        $ctrl = new PaymentApiController($db);
        $ctrl->handle($requestMethod, $id, $action);
        break;

    default:
        Response::error('Endpoint không tồn tại: /api/' . htmlspecialchars($resource), 404);
}
?>
