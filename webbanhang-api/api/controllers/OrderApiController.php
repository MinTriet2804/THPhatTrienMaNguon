<?php
/**
 * OrderApiController
 *
 * GET    /api/orders              — Danh sách đơn hàng (Admin: tất cả, User: của mình)
 * GET    /api/orders/{id}         — Chi tiết đơn hàng
 * POST   /api/orders              — Tạo đơn hàng từ giỏ hàng
 * PUT    /api/orders/{id}/status  — Cập nhật trạng thái (Admin)
 * DELETE /api/orders/{id}         — Hủy đơn hàng
 */
class OrderApiController
{
    private $orderModel;
    private $cartModel;
    private $productModel;

    public function __construct($db)
    {
        Auth::init();
        $this->orderModel   = new OrderModel($db);
        $this->cartModel    = new CartModel();
        $this->productModel = new ProductModel($db);
    }

    public function handle(string $method, $id, $action): void
    {
        switch ($method) {
            case 'GET':
                $id ? $this->show((int)$id) : $this->index();
                break;

            case 'POST':
                $this->store();
                break;

            case 'PUT':
                if ($id && $action === 'status') {
                    $this->updateStatus((int)$id);
                } else {
                    Response::error('Route không hợp lệ. Dùng PUT /api/orders/{id}/status', 400);
                }
                break;

            case 'DELETE':
                $id ? $this->cancel((int)$id) : Response::error('Thiếu ID đơn hàng.', 400);
                break;

            default:
                Response::error('Phương thức không được hỗ trợ.', 405);
        }
    }

    // GET /api/orders
    private function index(): void
    {
        Auth::requireLogin();

        if (Auth::isAdmin()) {
            $orders = $this->orderModel->getAllOrders();
            $stats  = $this->orderModel->getStats();
            Response::success([
                'orders' => $orders,
                'stats'  => $stats,
                'count'  => count($orders),
            ], 'Lấy danh sách đơn hàng thành công.');
        } else {
            // User chỉ xem đơn của mình — lưu user_id khi tạo đơn
            $userId = Auth::currentUserId();
            $orders = $this->orderModel->getOrdersByUser($userId);
            Response::success([
                'orders' => $orders,
                'count'  => count($orders),
            ], 'Lấy danh sách đơn hàng của bạn thành công.');
        }
    }

    // GET /api/orders/{id}
    private function show(int $id): void
    {
        Auth::requireLogin();

        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            Response::error('Không tìm thấy đơn hàng.', 404);
        }

        // User chỉ được xem đơn của mình
        if (!Auth::isAdmin()) {
            $userId = Auth::currentUserId();
            if ($order->user_id && (int)$order->user_id !== $userId) {
                Response::error('Bạn không có quyền xem đơn hàng này.', 403);
            }
        }

        $items = $this->orderModel->getOrderItems($id);
        Response::success([
            'order' => $order,
            'items' => $items,
        ], 'Lấy chi tiết đơn hàng thành công.');
    }

    // POST /api/orders — Tạo đơn hàng từ giỏ hàng
    private function store(): void
    {
        Auth::requireLogin();

        $cart = $this->cartModel->getCart();
        if (empty($cart)) {
            Response::error('Giỏ hàng đang trống. Không thể đặt hàng.', 400);
        }

        $data = Response::getJsonInput();
        $errors = $this->validateOrder($data);
        if (!empty($errors)) {
            Response::error('Dữ liệu không hợp lệ.', 422, $errors);
        }

        $fullname = htmlspecialchars(strip_tags(trim($data['fullname'])));
        $phone    = htmlspecialchars(strip_tags(trim($data['phone'])));
        $address  = htmlspecialchars(strip_tags(trim($data['address'])));
        $note     = htmlspecialchars(strip_tags(trim($data['note'] ?? '')));
        $payment  = in_array($data['payment_method'] ?? 'cod', ['cod', 'bank_transfer', 'e_wallet'])
                    ? $data['payment_method']
                    : 'cod';

        $subtotal = $this->cartModel->getTotal();
        $shipping = $subtotal >= 500000 ? 0 : 30000;
        $total    = $subtotal + $shipping;
        $userId   = Auth::currentUserId();

        $orderId = $this->orderModel->createOrder($fullname, $phone, $address, $note, $payment, $total, $userId);
        $this->orderModel->createOrderItems($orderId, $cart);

        // Xóa giỏ hàng sau khi đặt hàng thành công
        $this->cartModel->clearCart();

        $order = $this->orderModel->getOrderById($orderId);
        $items = $this->orderModel->getOrderItems($orderId);

        Response::success([
            'order'    => $order,
            'items'    => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $total,
        ], 'Đặt hàng thành công! Mã đơn hàng #' . $orderId, 201);
    }

    // PUT /api/orders/{id}/status — Cập nhật trạng thái (Admin)
    private function updateStatus(int $id): void
    {
        Auth::requireAdmin();

        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            Response::error('Không tìm thấy đơn hàng.', 404);
        }

        $data   = Response::getJsonInput();
        $status = $data['status'] ?? '';

        $allowed = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed)) {
            Response::error('Trạng thái không hợp lệ. Các giá trị hợp lệ: ' . implode(', ', $allowed), 422);
        }

        // Không cho phép thay đổi trạng thái từ cancelled
        if ($order->status === 'cancelled' && $status !== 'cancelled') {
            Response::error('Không thể thay đổi trạng thái đơn hàng đã bị hủy.', 409);
        }

        if ($this->orderModel->updateStatus($id, $status)) {
            $updated = $this->orderModel->getOrderById($id);
            Response::success($updated, 'Cập nhật trạng thái đơn hàng #' . $id . ' thành "' . $status . '" thành công.');
        } else {
            Response::error('Cập nhật trạng thái thất bại.', 500);
        }
    }

    // DELETE /api/orders/{id} — Hủy đơn hàng
    private function cancel(int $id): void
    {
        Auth::requireLogin();

        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            Response::error('Không tìm thấy đơn hàng.', 404);
        }

        // User chỉ được hủy đơn của mình
        if (!Auth::isAdmin()) {
            $userId = Auth::currentUserId();
            if ($order->user_id && (int)$order->user_id !== $userId) {
                Response::error('Bạn không có quyền hủy đơn hàng này.', 403);
            }
        }

        // Chỉ cho phép hủy khi đang pending
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            Response::error('Chỉ có thể hủy đơn hàng ở trạng thái "pending" hoặc "confirmed". Đơn hàng hiện tại: ' . $order->status, 409);
        }

        if ($this->orderModel->updateStatus($id, 'cancelled')) {
            $updated = $this->orderModel->getOrderById($id);
            Response::success($updated, 'Hủy đơn hàng #' . $id . ' thành công.');
        } else {
            Response::error('Hủy đơn hàng thất bại.', 500);
        }
    }

    // HELPER: Validate dữ liệu đơn hàng
    private function validateOrder(array $data): array
    {
        $errors = [];
        if (empty(trim($data['fullname'] ?? ''))) {
            $errors['fullname'] = 'Họ tên người nhận không được để trống.';
        }
        $phone = trim($data['phone'] ?? '');
        if (empty($phone)) {
            $errors['phone'] = 'Số điện thoại không được để trống.';
        } elseif (!preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }
        if (empty(trim($data['address'] ?? ''))) {
            $errors['address'] = 'Địa chỉ giao hàng không được để trống.';
        }
        return $errors;
    }
}
?>
