<?php
/**
 * API Entry Point — /api/index.php
 * Tất cả request tới /api/* đều được xử lý tại đây
 *
 * Route patterns:
 *   GET    /api/products
 *   GET    /api/products/{id}
 *   POST   /api/products
 *   PUT    /api/products/{id}
 *   DELETE /api/products/{id}
 *   GET    /api/products/search?q=...
 *   GET    /api/products/filter?category_id=...
 *   GET    /api/products/sort?order=asc|desc
 *
 *   GET    /api/categories
 *   GET    /api/categories/{id}
 *   POST   /api/categories
 *   PUT    /api/categories/{id}
 *   DELETE /api/categories/{id}
 *
 *   GET    /api/cart
 *   POST   /api/cart
 *   PUT    /api/cart/{id}
 *   DELETE /api/cart/{id}
 *   DELETE /api/cart
 *   GET    /api/cart/total
 *
 *   GET    /api/orders
 *   GET    /api/orders/{id}
 *   POST   /api/orders
 *   PUT    /api/orders/{id}/status
 *   DELETE /api/orders/{id}
 *
 *   POST   /api/payments
 *   GET    /api/payments/{order_id}
 *
 *   POST   /api/auth/login
 *   POST   /api/auth/logout
 *   POST   /api/auth/register
 */

// Thiết lập header và CORS
require_once __DIR__ . '/helpers/Response.php';
Response::setHeaders();

// Load helpers & config
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/ProductModel.php';
require_once __DIR__ . '/../app/models/CategoryModel.php';
require_once __DIR__ . '/../app/models/CartModel.php';
require_once __DIR__ . '/../app/models/OrderModel.php';

// Load API Controllers
require_once __DIR__ . '/controllers/ProductApiController.php';
require_once __DIR__ . '/controllers/CategoryApiController.php';
require_once __DIR__ . '/controllers/CartApiController.php';
require_once __DIR__ . '/controllers/OrderApiController.php';
require_once __DIR__ . '/controllers/PaymentApiController.php';
require_once __DIR__ . '/controllers/AuthApiController.php';

// Parse URL: /api/<resource>[/<id>][/<action>]
$requestUri    = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Bóc phần /api/ ra khỏi path
$basePath = '';
// Tìm vị trí /api/ trong URI
$apiPos = strpos($requestUri, '/api/');
if ($apiPos !== false) {
    $path = substr($requestUri, $apiPos + 5); // bỏ '/api/'
} else {
    $path = ltrim($requestUri, '/');
    // Nếu bắt đầu bằng 'api/', cắt bỏ
    if (strpos($path, 'api/') === 0) {
        $path = substr($path, 4);
    }
}

// Tách query string
$queryPos = strpos($path, '?');
if ($queryPos !== false) {
    $path = substr($path, 0, $queryPos);
}

$path = trim($path, '/');
$segments = explode('/', $path);

$resource = $segments[0] ?? '';
$id       = $segments[1] ?? null;   // có thể là số hoặc từ khóa (vd: 'total', 'search')
$action   = $segments[2] ?? null;   // vd: 'status'

// Khởi tạo kết nối DB
$db = (new Database())->getConnection();

// Dispatch
switch ($resource) {
    case '':
        Response::success([
            'title' => 'TechStore Web API',
            'status' => 'Running',
            'available_endpoints' => [
                'products'   => 'http://localhost:8080/api/products',
                'categories' => 'http://localhost:8080/api/categories',
                'cart'       => 'http://localhost:8080/api/cart',
                'orders'     => 'http://localhost:8080/api/orders',
                'auth'       => 'http://localhost:8080/api/auth'
            ]
        ], 'Kết nối cổng API tổng quát thành công.');
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

    case 'auth':
        $ctrl = new AuthApiController($db);
        $ctrl->handle($requestMethod, $id, $action);
        break;

    default:
        Response::error('Endpoint không tồn tại: /api/' . htmlspecialchars($resource), 404);
}
?>
