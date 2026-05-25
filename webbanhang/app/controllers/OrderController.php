<?php
require_once('app/config/database.php');
require_once('app/models/OrderModel.php');

class OrderController
{
    private $orderModel;
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db         = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
    }

    // Danh sách tất cả đơn hàng
    public function index()
    {
        $orders = $this->orderModel->getAllOrders();
        $stats  = $this->orderModel->getStats();
        $flash  = $this->getFlash();
        include 'app/views/order/list.php';
    }

    // Chi tiết một đơn hàng
    public function detail($id)
    {
        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            $this->setFlash('error', 'Không tìm thấy đơn hàng.');
            header('Location: /webbanhang/Order');
            exit;
        }
        $items = $this->orderModel->getOrderItems($id);
        include 'app/views/order/detail.php';
    }

    // Cập nhật trạng thái đơn hàng (POST)
    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = (int)($_POST['id']     ?? 0);
            $status = $_POST['status'] ?? '';
            if ($this->orderModel->updateStatus($id, $status)) {
                $this->setFlash('success', 'Cập nhật trạng thái đơn hàng thành công!');
            } else {
                $this->setFlash('error', 'Cập nhật trạng thái thất bại.');
            }
        }
        header('Location: /webbanhang/Order');
        exit;
    }

    // Xóa đơn hàng
    public function delete($id)
    {
        if ($this->orderModel->deleteOrder($id)) {
            $this->setFlash('success', 'Đã xóa đơn hàng #' . $id . '.');
        } else {
            $this->setFlash('error', 'Xóa đơn hàng thất bại.');
        }
        header('Location: /webbanhang/Order');
        exit;
    }

    // --- Flash message helpers ---
    private function setFlash($type, $message)
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    private function getFlash()
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
?>
