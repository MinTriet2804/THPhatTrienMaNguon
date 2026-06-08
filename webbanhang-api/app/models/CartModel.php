<?php
class CartModel
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    // Lấy toàn bộ giỏ hàng
    public function getCart()
    {
        return $_SESSION['cart'];
    }

    // Thêm sản phẩm vào giỏ hàng (số lượng mặc định = 1)
    public function addToCart($product)
    {
        $id = $product->id;
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'id'       => $product->id,
                'name'     => $product->name,
                'price'    => $product->price,
                'image'    => $product->image,
                'quantity' => 1,
            ];
        }
    }

    // Thêm sản phẩm với số lượng tùy chỉnh (API mới)
    public function addToCartWithQuantity($product, int $quantity = 1)
    {
        $id = $product->id;
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$id] = [
                'id'       => $product->id,
                'name'     => $product->name,
                'price'    => $product->price,
                'image'    => $product->image,
                'quantity' => $quantity,
            ];
        }
    }

    // Cập nhật số lượng
    public function updateQuantity($id, $quantity)
    {
        if (isset($_SESSION['cart'][$id])) {
            if ($quantity <= 0) {
                $this->removeItem($id);
            } else {
                $_SESSION['cart'][$id]['quantity'] = (int)$quantity;
            }
        }
    }

    // Xóa một sản phẩm
    public function removeItem($id)
    {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
    }

    // Xóa toàn bộ giỏ
    public function clearCart()
    {
        $_SESSION['cart'] = [];
    }

    // Tính tổng tiền
    public function getTotal()
    {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    // Đếm tổng số lượng sản phẩm
    public function getCount()
    {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
}
?>
