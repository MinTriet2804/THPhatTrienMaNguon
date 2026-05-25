<?php
class OrderModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Tạo đơn hàng mới, trả về order_id
    public function createOrder($fullname, $phone, $address, $note, $payment, $total)
    {
        $query = "INSERT INTO orders (fullname, phone, address, note, payment_method, total_amount, status, created_at)
                  VALUES (:fullname, :phone, :address, :note, :payment, :total, 'pending', NOW())";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':phone',    $phone);
        $stmt->bindParam(':address',  $address);
        $stmt->bindParam(':note',     $note);
        $stmt->bindParam(':payment',  $payment);
        $stmt->bindParam(':total',    $total);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    // Lưu chi tiết từng sản phẩm trong đơn hàng
    public function createOrderItems($order_id, array $cart)
    {
        $query = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
                  VALUES (:order_id, :product_id, :product_name, :price, :quantity)";
        $stmt  = $this->conn->prepare($query);
        foreach ($cart as $item) {
            $stmt->bindParam(':order_id',     $order_id,           PDO::PARAM_INT);
            $stmt->bindParam(':product_id',   $item['id'],         PDO::PARAM_INT);
            $stmt->bindParam(':product_name', $item['name']);
            $stmt->bindParam(':price',        $item['price']);
            $stmt->bindParam(':quantity',     $item['quantity'],   PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    // Lấy tất cả đơn hàng (cho trang quản lý)
    public function getAllOrders()
    {
        $query = "SELECT * FROM orders ORDER BY created_at DESC";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy chi tiết một đơn hàng
    public function getOrderById($id)
    {
        $query = "SELECT * FROM orders WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Lấy danh sách sản phẩm trong đơn hàng
    public function getOrderItems($order_id)
    {
        $query = "SELECT * FROM order_items WHERE order_id = :order_id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Cập nhật trạng thái đơn hàng
    public function updateStatus($id, $status)
    {
        $allowed = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed)) return false;

        $query = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id',     $id,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Xóa đơn hàng (kèm items do ON DELETE CASCADE)
    public function deleteOrder($id)
    {
        $query = "DELETE FROM orders WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Thống kê tổng quan
    public function getStats()
    {
        $query = "SELECT
                    COUNT(*) AS total_orders,
                    SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                    SUM(CASE WHEN status = 'shipping'  THEN 1 ELSE 0 END) AS shipping,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                    SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) AS total_revenue
                  FROM orders";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}
?>
