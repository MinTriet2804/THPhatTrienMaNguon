<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nhúng các helper và models hệ thống
require_once 'app/helpers.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/AccountModel.php';

// 2. Xử lý phân tích URL (Routing)
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Loại bỏ thư mục gốc "webbanhang" nếu có trong URL
if (isset($url[0]) && strtolower($url[0]) === 'webbanhang') {
    array_shift($url);
}

// 3. Xác định tên Controller và Action
$controllerName = isset($url[0]) && $url[0] != '' ? ucfirst($url[0]) . 'Controller' : 'ProductController';
$action         = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';

// =========================================================================
//  PHÂN QUYỀN (Authorization Middleware)
// =========================================================================

// Các controller chỉ dành cho người đã đăng nhập (bất kỳ role)
$loginRequired = [
    'CategoryController',
    'OrderController',
];

// Các action cụ thể trong controller cần đăng nhập
$loginRequiredActions = [
    'ProductController'  => ['add', 'edit', 'delete', 'store', 'update'],
    'AccountController'  => ['profile', 'updateProfile', 'uploadAvatar',
                             'changePassword', 'updatePassword'],
];

// Các controller/action chỉ dành cho Admin
$adminRequired = [
    'CategoryController' => '*',   // Toàn bộ
    'OrderController'    => '*',   // Toàn bộ
];

$adminRequiredActions = [
    'ProductController'  => ['add', 'edit', 'delete', 'store', 'update'],
    'AccountController'  => ['manageUsers', 'toggleActive', 'updateRole', 'deleteUser'],
];

// Kiểm tra đăng nhập
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['username']);
$isAdmin    = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Kiểm tra cần đăng nhập
$needLogin = false;
if (in_array($controllerName, $loginRequired)) {
    $needLogin = true;
} elseif (isset($loginRequiredActions[$controllerName])
       && in_array($action, $loginRequiredActions[$controllerName])) {
    $needLogin = true;
}

if ($needLogin && !$isLoggedIn) {
    $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục!';
    header('Location: /webbanhang/account/login');
    exit;
}

// Kiểm tra cần quyền Admin
$needAdmin = false;
if (isset($adminRequired[$controllerName])) {
    $needAdmin = true;
} elseif (isset($adminRequiredActions[$controllerName])
       && in_array($action, $adminRequiredActions[$controllerName])) {
    $needAdmin = true;
}

if ($needAdmin && !$isAdmin) {
    if (!$isLoggedIn) {
        $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục!';
        header('Location: /webbanhang/account/login');
    } else {
        http_response_code(403);
        die('<div style="text-align:center;padding:80px;font-family:sans-serif;">
                <h2 style="color:#d70018;">403 — Không có quyền truy cập</h2>
                <p>Chức năng này chỉ dành cho Admin.</p>
                <a href="/webbanhang/product" style="color:#d70018;">← Về trang chủ</a>
             </div>');
    }
    exit;
}

// =========================================================================

// 4. Kiểm tra file controller tồn tại
if (!file_exists('app/controllers/' . $controllerName . '.php')) {
    die('Controller not found: ' . htmlspecialchars($controllerName));
}

require_once 'app/controllers/' . $controllerName . '.php';
$controller = new $controllerName();

// 5. Kiểm tra action tồn tại
if (!method_exists($controller, $action)) {
    die('Action not found: ' . htmlspecialchars($action));
}

// 6. Khởi chạy ứng dụng
call_user_func_array([$controller, $action], array_slice($url, 2));
