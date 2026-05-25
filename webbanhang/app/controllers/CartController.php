<?php
require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CartModel.php');
require_once('app/models/OrderModel.php');

class CartController
{
    private $productModel;
    private $cartModel;
    private $orderModel;
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db           = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->cartModel    = new CartModel();
        $this->orderModel   = new OrderModel($this->db);
    }

    // Hiển thị trang giỏ hàng
    public function index()
    {
        $cart  = $this->cartModel->getCart();
        $total = $this->cartModel->getTotal();
        include 'app/views/cart/index.php';
    }

    // Thêm sản phẩm vào giỏ
    public function add($id)
    {
        $product = $this->productModel->getProductById($id);
        if ($product) {
            $this->cartModel->addToCart($product);
        }
        // Redirect về trang trước hoặc danh sách
        $referer = $_SERVER['HTTP_REFERER'] ?? '/webbanhang/Product';
        header('Location: ' . $referer);
        exit;
    }

    // Cập nhật số lượng
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id       = $_POST['id'] ?? null;
            $quantity = (int)($_POST['quantity'] ?? 1);
            if ($id !== null) {
                $this->cartModel->updateQuantity($id, $quantity);
            }
        }
        header('Location: /webbanhang/Cart');
        exit;
    }

    // Xóa một sản phẩm
    public function remove($id)
    {
        $this->cartModel->removeItem($id);
        header('Location: /webbanhang/Cart');
        exit;
    }

    // Xóa toàn bộ giỏ hàng
    public function clear()
    {
        $this->cartModel->clearCart();
        header('Location: /webbanhang/Cart');
        exit;
    }

    // Hiển thị trang thanh toán
    public function checkout()
    {
        $cart  = $this->cartModel->getCart();
        $total = $this->cartModel->getTotal();
        if (empty($cart)) {
            header('Location: /webbanhang/Cart');
            exit;
        }
        include 'app/views/cart/checkout.php';
    }

    // Xử lý đặt hàng — lưu vào DB
    public function placeOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = htmlspecialchars(strip_tags($_POST['fullname'] ?? ''));
            $phone    = htmlspecialchars(strip_tags($_POST['phone']    ?? ''));
            $address  = htmlspecialchars(strip_tags($_POST['address']  ?? ''));
            $note     = htmlspecialchars(strip_tags($_POST['note']     ?? ''));
            $payment  = htmlspecialchars(strip_tags($_POST['payment']  ?? 'cod'));

            $cart     = $this->cartModel->getCart();
            $total    = $this->cartModel->getTotal();
            $shipping = $total >= 500000 ? 0 : 30000;
            $grand    = $total + $shipping;

            // Lưu đơn hàng vào database
            $order_id = $this->orderModel->createOrder($fullname, $phone, $address, $note, $payment, $grand);
            $this->orderModel->createOrderItems($order_id, $cart);

            // Lưu thông tin vào session để hiển thị trang thành công
            $_SESSION['order_success'] = [
                'order_id' => $order_id,
                'name'     => $fullname,
                'phone'    => $phone,
                'address'  => $address,
                'note'     => $note,
                'payment'  => $payment,
                'cart'     => $cart,
                'total'    => $total,
            ];

            $this->cartModel->clearCart();
            header('Location: /webbanhang/Cart/success');
            exit;
        }
        header('Location: /webbanhang/Cart/checkout');
        exit;
    }

    // Trang đặt hàng thành công
    public function success()
    {
        if (empty($_SESSION['order_success'])) {
            header('Location: /webbanhang/Product');
            exit;
        }
        $order = $_SESSION['order_success'];
        unset($_SESSION['order_success']);
        include 'app/views/cart/success.php';
    }
}
?>
