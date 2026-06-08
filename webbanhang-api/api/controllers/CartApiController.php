<?php
/**
 * CartApiController — Giỏ hàng lưu trong Session
 *
 * GET    /api/cart            — Xem giỏ hàng
 * GET    /api/cart/total      — Lấy tổng tiền
 * POST   /api/cart            — Thêm sản phẩm vào giỏ
 * PUT    /api/cart/{id}       — Cập nhật số lượng
 * DELETE /api/cart/{id}       — Xóa 1 sản phẩm
 * DELETE /api/cart            — Xóa toàn bộ giỏ hàng
 */
class CartApiController
{
    private $productModel;
    private $cartModel;

    public function __construct($db)
    {
        Auth::init();
        $this->productModel = new ProductModel($db);
        $this->cartModel    = new CartModel();
    }

    public function handle(string $method, $id, $action): void
    {
        // GET /api/cart/total
        if ($method === 'GET' && $id === 'total') {
            $this->getTotal();
            return;
        }

        switch ($method) {
            case 'GET':
                $this->index();
                break;

            case 'POST':
                $this->addItem();
                break;

            case 'PUT':
                $id !== null ? $this->updateItem($id) : Response::error('Thiếu ID sản phẩm.', 400);
                break;

            case 'DELETE':
                $id !== null ? $this->removeItem($id) : $this->clearCart();
                break;

            default:
                Response::error('Phương thức không được hỗ trợ.', 405);
        }
    }

    // GET /api/cart
    private function index(): void
    {
        Auth::requireLogin();
        $cart  = $this->cartModel->getCart();
        $total = $this->cartModel->getTotal();
        $count = $this->cartModel->getCount();

        Response::success([
            'items'      => array_values($cart),
            'item_count' => $count,
            'total'      => $total,
        ], 'Lấy giỏ hàng thành công.');
    }

    // GET /api/cart/total
    private function getTotal(): void
    {
        Auth::requireLogin();
        $total    = $this->cartModel->getTotal();
        $count    = $this->cartModel->getCount();
        $shipping = $total >= 500000 ? 0 : 30000;

        Response::success([
            'subtotal'      => $total,
            'shipping_fee'  => $shipping,
            'grand_total'   => $total + $shipping,
            'item_count'    => $count,
        ], 'Lấy tổng tiền giỏ hàng thành công.');
    }

    // POST /api/cart  — body: { "product_id": 1, "quantity": 2 }
    private function addItem(): void
    {
        Auth::requireLogin();

        $data       = Response::getJsonInput();
        $product_id = (int)($data['product_id'] ?? 0);
        $quantity   = (int)($data['quantity']   ?? 1);

        if ($product_id <= 0) {
            Response::error('Thiếu hoặc không hợp lệ product_id.', 400);
        }
        if ($quantity <= 0) {
            Response::error('Số lượng sản phẩm phải lớn hơn 0.', 400);
        }

        // Kiểm tra sản phẩm tồn tại
        $product = $this->productModel->getProductById($product_id);
        if (!$product) {
            Response::error('Sản phẩm không tồn tại.', 404);
        }

        // Thêm vào giỏ (với số lượng tùy chỉnh)
        $this->cartModel->addToCartWithQuantity($product, $quantity);

        $cart  = $this->cartModel->getCart();
        $total = $this->cartModel->getTotal();

        Response::success([
            'items'      => array_values($cart),
            'item_count' => $this->cartModel->getCount(),
            'total'      => $total,
        ], 'Thêm sản phẩm "' . $product->name . '" vào giỏ hàng thành công.', 201);
    }

    // PUT /api/cart/{product_id}  — body: { "quantity": 3 }
    private function updateItem($product_id): void
    {
        Auth::requireLogin();

        $product_id = (int)$product_id;
        $data       = Response::getJsonInput();
        $quantity   = isset($data['quantity']) ? (int)$data['quantity'] : -1;

        if ($quantity < 0) {
            Response::error('Thiếu hoặc không hợp lệ quantity.', 400);
        }
        if ($quantity === 0) {
            // Nếu quantity = 0 thì xóa khỏi giỏ
            $this->cartModel->removeItem($product_id);
            Response::success(null, 'Đã xóa sản phẩm khỏi giỏ hàng.');
        }
        if ($quantity < 1) {
            Response::error('Số lượng sản phẩm phải lớn hơn 0.', 400);
        }

        $cart = $this->cartModel->getCart();
        if (!isset($cart[$product_id])) {
            Response::error('Sản phẩm không có trong giỏ hàng.', 404);
        }

        $this->cartModel->updateQuantity($product_id, $quantity);

        $updatedCart = $this->cartModel->getCart();
        Response::success([
            'items'      => array_values($updatedCart),
            'item_count' => $this->cartModel->getCount(),
            'total'      => $this->cartModel->getTotal(),
        ], 'Cập nhật số lượng thành công.');
    }

    // DELETE /api/cart/{product_id}
    private function removeItem($product_id): void
    {
        Auth::requireLogin();

        $product_id = (int)$product_id;
        $cart = $this->cartModel->getCart();
        if (!isset($cart[$product_id])) {
            Response::error('Sản phẩm không có trong giỏ hàng.', 404);
        }

        $productName = $cart[$product_id]['name'];
        $this->cartModel->removeItem($product_id);

        Response::success([
            'items'      => array_values($this->cartModel->getCart()),
            'item_count' => $this->cartModel->getCount(),
            'total'      => $this->cartModel->getTotal(),
        ], 'Đã xóa sản phẩm "' . $productName . '" khỏi giỏ hàng.');
    }

    // DELETE /api/cart  (không có id)
    private function clearCart(): void
    {
        Auth::requireLogin();
        $this->cartModel->clearCart();
        Response::success(null, 'Đã xóa toàn bộ giỏ hàng.');
    }
}
?>
