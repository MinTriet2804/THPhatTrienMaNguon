<?php
/**
 * PaymentApiController — Thanh toán đơn hàng
 *
 * POST /api/payments                  — Tạo thanh toán cho đơn hàng
 * GET  /api/payments/{order_id}       — Xem trạng thái thanh toán của đơn hàng
 *
 * Phương thức thanh toán hỗ trợ:
 *   cod            — Thanh toán khi nhận hàng
 *   bank_transfer  — Chuyển khoản ngân hàng (mô phỏng)
 *   e_wallet       — Ví điện tử (mô phỏng)
 */
class PaymentApiController
{
    private $orderModel;
    private $db;

    public function __construct($db)
    {
        Auth::init();
        $this->db         = $db;
        $this->orderModel = new OrderModel($db);
    }

    public function handle(string $method, $id, $action): void
    {
        switch ($method) {
            case 'POST':
                $this->createPayment();
                break;

            case 'GET':
                $id ? $this->getPaymentStatus((int)$id) : Response::error('Thiếu order_id.', 400);
                break;

            default:
                Response::error('Phương thức không được hỗ trợ.', 405);
        }
    }

    // POST /api/payments
    // Body: { "order_id": 1, "payment_method": "cod|bank_transfer|e_wallet" }
    private function createPayment(): void
    {
        Auth::requireLogin();

        $data     = Response::getJsonInput();
        $orderId  = (int)($data['order_id'] ?? 0);
        $method   = $data['payment_method'] ?? '';

        if ($orderId <= 0) {
            Response::error('Thiếu hoặc không hợp lệ order_id.', 400);
        }

        $allowedMethods = ['cod', 'bank_transfer', 'e_wallet'];
        if (!in_array($method, $allowedMethods)) {
            Response::error('Phương thức thanh toán không hợp lệ. Các giá trị hợp lệ: ' . implode(', ', $allowedMethods), 422);
        }

        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            Response::error('Không tìm thấy đơn hàng.', 404);
        }

        // Kiểm tra quyền: User chỉ thanh toán đơn của mình
        if (!Auth::isAdmin()) {
            $userId = Auth::currentUserId();
            if ($order->user_id && (int)$order->user_id !== $userId) {
                Response::error('Bạn không có quyền thanh toán đơn hàng này.', 403);
            }
        }

        // Không cho thanh toán lại đơn đã thanh toán
        if ($this->isOrderPaid($orderId)) {
            Response::error('Đơn hàng #' . $orderId . ' đã được thanh toán trước đó. Không thể thanh toán lại.', 409);
        }

        // Không cho thanh toán đơn đã hủy
        if ($order->status === 'cancelled') {
            Response::error('Đơn hàng #' . $orderId . ' đã bị hủy. Không thể thanh toán.', 409);
        }

        // Xử lý thanh toán theo phương thức
        $paymentResult = $this->processPayment($orderId, $method, (float)$order->total_amount);

        // Lưu thông tin thanh toán vào bảng payments
        $this->savePayment(
            $orderId,
            $method,
            (float)$order->total_amount,
            $paymentResult['status'],
            $paymentResult['transaction_id']
        );

        // Cập nhật payment_method của đơn hàng nếu thay đổi
        if ($order->payment_method !== $method) {
            $stmt = $this->db->prepare("UPDATE orders SET payment_method = :pm WHERE id = :id");
            $stmt->execute([':pm' => $method, ':id' => $orderId]);
        }

        // Nếu thanh toán ngay (cod/e_wallet mô phỏng), cập nhật trạng thái đơn
        if ($paymentResult['status'] === 'paid' && $order->status === 'pending') {
            $this->orderModel->updateStatus($orderId, 'confirmed');
        }

        $updatedOrder = $this->orderModel->getOrderById($orderId);

        Response::success([
            'order'          => $updatedOrder,
            'payment'        => [
                'order_id'       => $orderId,
                'method'         => $method,
                'amount'         => $order->total_amount,
                'status'         => $paymentResult['status'],
                'transaction_id' => $paymentResult['transaction_id'],
                'message'        => $paymentResult['message'],
            ],
        ], $paymentResult['message'], 201);
    }

    // GET /api/payments/{order_id}
    private function getPaymentStatus(int $orderId): void
    {
        Auth::requireLogin();

        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            Response::error('Không tìm thấy đơn hàng.', 404);
        }

        // Kiểm tra quyền
        if (!Auth::isAdmin()) {
            $userId = Auth::currentUserId();
            if ($order->user_id && (int)$order->user_id !== $userId) {
                Response::error('Bạn không có quyền xem thông tin thanh toán này.', 403);
            }
        }

        $payment = $this->getPaymentByOrderId($orderId);

        Response::success([
            'order_id'       => $orderId,
            'order_status'   => $order->status,
            'payment_method' => $order->payment_method,
            'total_amount'   => $order->total_amount,
            'payment'        => $payment,
            'is_paid'        => $this->isOrderPaid($orderId),
        ], 'Lấy trạng thái thanh toán thành công.');
    }

    // ---------------------------------------------------------------
    // HELPER: Xử lý logic thanh toán theo phương thức
    // ---------------------------------------------------------------
    private function processPayment(int $orderId, string $method, float $amount): array
    {
        switch ($method) {
            case 'cod':
                return [
                    'status'         => 'pending',   // Chờ giao hàng mới thu tiền
                    'transaction_id' => 'COD-' . $orderId . '-' . time(),
                    'message'        => 'Đặt hàng thành công! Bạn sẽ thanh toán ' . number_format($amount, 0, ',', '.') . '₫ khi nhận hàng.',
                ];

            case 'bank_transfer':
                // Mô phỏng: tạo mã giao dịch, chờ xác nhận thủ công
                return [
                    'status'         => 'pending',
                    'transaction_id' => 'BT-' . strtoupper(substr(md5($orderId . time()), 0, 10)),
                    'message'        => 'Vui lòng chuyển khoản ' . number_format($amount, 0, ',', '.') . '₫ đến tài khoản: MB Bank - 0123456789 - TECHSTORE. Nội dung: DH' . $orderId . '. Đơn hàng sẽ được xác nhận sau khi nhận được tiền.',
                    'bank_info'      => [
                        'bank'    => 'MB Bank',
                        'account' => '0123456789',
                        'owner'   => 'TECHSTORE',
                        'content' => 'DH' . $orderId,
                        'amount'  => $amount,
                    ],
                ];

            case 'e_wallet':
                // Mô phỏng: thanh toán thành công ngay
                return [
                    'status'         => 'paid',
                    'transaction_id' => 'EW-' . strtoupper(substr(md5($orderId . time() . rand()), 0, 12)),
                    'message'        => 'Thanh toán qua ví điện tử thành công! Số tiền ' . number_format($amount, 0, ',', '.') . '₫ đã được trừ từ ví của bạn.',
                ];

            default:
                return [
                    'status'         => 'failed',
                    'transaction_id' => null,
                    'message'        => 'Phương thức thanh toán không được hỗ trợ.',
                ];
        }
    }

    // ---------------------------------------------------------------
    // HELPER: Lưu / lấy thông tin thanh toán từ DB
    // ---------------------------------------------------------------
    private function ensurePaymentTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                order_id       INT NOT NULL,
                payment_method VARCHAR(50) NOT NULL DEFAULT 'cod',
                amount         DECIMAL(15,2) NOT NULL DEFAULT 0,
                status         ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
                transaction_id VARCHAR(100) DEFAULT NULL,
                paid_at        DATETIME DEFAULT NULL,
                created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order_id (order_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function savePayment(int $orderId, string $method, float $amount, string $status, ?string $txId): void
    {
        $this->ensurePaymentTable();

        $paidAt = ($status === 'paid') ? date('Y-m-d H:i:s') : null;
        $stmt = $this->db->prepare("
            INSERT INTO payments (order_id, payment_method, amount, status, transaction_id, paid_at)
            VALUES (:order_id, :method, :amount, :status, :tx_id, :paid_at)
        ");
        $stmt->execute([
            ':order_id' => $orderId,
            ':method'   => $method,
            ':amount'   => $amount,
            ':status'   => $status,
            ':tx_id'    => $txId,
            ':paid_at'  => $paidAt,
        ]);
    }

    private function isOrderPaid(int $orderId): bool
    {
        $this->ensurePaymentTable();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM payments WHERE order_id = :id AND status = 'paid'");
        $stmt->execute([':id' => $orderId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function getPaymentByOrderId(int $orderId): ?object
    {
        $this->ensurePaymentTable();
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE order_id = :id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':id' => $orderId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }
}
?>
