<?php
/**
 * ProductApiController
 *
 * GET    /api/products                  — Danh sách sản phẩm
 * GET    /api/products/{id}             — Chi tiết sản phẩm
 * GET    /api/products/search?q=...     — Tìm kiếm theo tên
 * GET    /api/products/filter?category_id=...  — Lọc theo danh mục
 * GET    /api/products/sort?order=asc|desc     — Sắp xếp theo giá
 * POST   /api/products                  — Thêm sản phẩm (Admin)
 * PUT    /api/products/{id}             — Cập nhật sản phẩm (Admin)
 * DELETE /api/products/{id}             — Xóa sản phẩm (Admin)
 */
class ProductApiController
{
    private $productModel;
    private $categoryModel;
    private $db;

    public function __construct($db)
    {
        $this->db             = $db;
        $this->productModel   = new ProductModel($db);
        $this->categoryModel  = new CategoryModel($db);
    }

    public function handle(string $method, $id, $action): void
    {
        // Các route đặc biệt khi $id là từ khóa (không phải số)
        if ($id !== null && !is_numeric($id)) {
            switch ($id) {
                case 'search':
                    if ($method === 'GET') { $this->search(); return; }
                    break;
                case 'filter':
                    if ($method === 'GET') { $this->filterByCategory(); return; }
                    break;
                case 'sort':
                    if ($method === 'GET') { $this->sortByPrice(); return; }
                    break;
            }
            Response::error('Route không hợp lệ.', 404);
        }

        switch ($method) {
            case 'GET':
                $id ? $this->show((int)$id) : $this->index();
                break;
            case 'POST':
                $this->store();
                break;
            case 'PUT':
                $id ? $this->update((int)$id) : Response::error('Thiếu ID sản phẩm.', 400);
                break;
            case 'DELETE':
                $id ? $this->delete((int)$id) : Response::error('Thiếu ID sản phẩm.', 400);
                break;
            default:
                Response::error('Phương thức HTTP không được hỗ trợ.', 405);
        }
    }

    // GET /api/products
    private function index(): void
    {
        $products = $this->productModel->getProducts();
        Response::success($products, 'Lấy danh sách sản phẩm thành công.');
    }

    // GET /api/products/{id}
    private function show(int $id): void
    {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            Response::error('Không tìm thấy sản phẩm.', 404);
        }
        Response::success($product, 'Lấy chi tiết sản phẩm thành công.');
    }

    // GET /api/products/search?q=tên
    private function search(): void
    {
        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            Response::error('Vui lòng cung cấp từ khóa tìm kiếm (?q=...).', 400);
        }
        $products = $this->productModel->searchByName($q);
        Response::success($products, 'Tìm kiếm sản phẩm thành công. Kết quả: ' . count($products));
    }

    // GET /api/products/filter?category_id=1
    private function filterByCategory(): void
    {
        $category_id = (int)($_GET['category_id'] ?? 0);
        if ($category_id <= 0) {
            Response::error('Vui lòng cung cấp category_id hợp lệ.', 400);
        }
        $category = $this->categoryModel->getCategoryById($category_id);
        if (!$category) {
            Response::error('Danh mục không tồn tại.', 404);
        }
        $products = $this->productModel->getProductsByCategory($category_id);
        Response::success([
            'category' => $category,
            'products' => $products,
            'count'    => count($products),
        ], 'Lọc sản phẩm theo danh mục thành công.');
    }

    // GET /api/products/sort?order=asc|desc
    private function sortByPrice(): void
    {
        $order = strtolower($_GET['order'] ?? 'asc');
        if (!in_array($order, ['asc', 'desc'])) {
            Response::error('Tham số order phải là "asc" hoặc "desc".', 400);
        }
        $products = $this->productModel->getProductsSortedByPrice($order);
        Response::success($products, 'Sắp xếp sản phẩm theo giá ' . ($order === 'asc' ? 'tăng dần' : 'giảm dần') . ' thành công.');
    }

    // POST /api/products  (Admin)
    private function store(): void
    {
        Auth::requireAdmin();

        // Hỗ trợ cả multipart/form-data (khi upload ảnh) và application/json
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $data = Response::getJsonInput();
        } else {
            $data = $_POST;
        }

        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            Response::error('Dữ liệu không hợp lệ.', 422, $errors);
        }

        $name        = trim($data['name']);
        $description = trim($data['description'] ?? '');
        $price       = (float)$data['price'];
        $category_id = (int)$data['category_id'];
        $image       = $data['image'] ?? '';

        // Xử lý upload file ảnh nếu có
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->uploadImage();
            if ($uploadResult === false) {
                Response::error('Không thể upload hình ảnh. Vui lòng kiểm tra định dạng (jpg, jpeg, png, gif, webp).', 422);
            }
            $image = $uploadResult;
        }

        // Kiểm tra danh mục tồn tại
        $category = $this->categoryModel->getCategoryById($category_id);
        if (!$category) {
            Response::error('Danh mục sản phẩm không tồn tại.', 422);
        }

        $result = $this->productModel->addProduct($name, $description, $price, $category_id, $image);

        if (is_array($result)) {
            Response::error('Lỗi khi lưu sản phẩm.', 422, $result);
        }

        // Lấy sản phẩm vừa thêm để trả về
        $lastId  = $this->db->lastInsertId();
        $product = $this->productModel->getProductById($lastId);
        Response::success($product, 'Thêm sản phẩm mới thành công.', 201);
    }

    // PUT /api/products/{id}  (Admin)
    private function update(int $id): void
    {
        Auth::requireAdmin();

        $product = $this->productModel->getProductById($id);
        if (!$product) {
            Response::error('Không tìm thấy sản phẩm.', 404);
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $data = Response::getJsonInput();
        } else {
            // PHP không tự parse PUT multipart, dùng parse_str cho x-www-form-urlencoded
            parse_str(file_get_contents('php://input'), $data);
            // Nếu $_POST có dữ liệu (Postman form-data), dùng $_POST
            if (empty($data) && !empty($_POST)) {
                $data = $_POST;
            }
        }

        $errors = $this->validateProduct($data, true);
        if (!empty($errors)) {
            Response::error('Dữ liệu không hợp lệ.', 422, $errors);
        }

        $name        = trim($data['name']);
        $description = trim($data['description'] ?? $product->description ?? '');
        $price       = (float)$data['price'];
        $category_id = (int)$data['category_id'];

        // Kiểm tra danh mục tồn tại
        $category = $this->categoryModel->getCategoryById($category_id);
        if (!$category) {
            Response::error('Danh mục sản phẩm không tồn tại.', 422);
        }

        // Xử lý ảnh
        $image = $product->image ?? '';
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->uploadImage();
            if ($uploadResult === false) {
                Response::error('Không thể upload hình ảnh. Kiểm tra định dạng file.', 422);
            }
            // Xóa ảnh cũ nếu có
            if (!empty($image)) {
                $oldPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $image);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $image = $uploadResult;
        } elseif (isset($data['image']) && $data['image'] !== '') {
            $image = $data['image'];
        }

        $result = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image);
        if (!$result) {
            Response::error('Cập nhật sản phẩm thất bại.', 500);
        }

        $updated = $this->productModel->getProductById($id);
        Response::success($updated, 'Cập nhật sản phẩm thành công.');
    }

    // DELETE /api/products/{id}  (Admin)
    private function delete(int $id): void
    {
        Auth::requireAdmin();

        $product = $this->productModel->getProductById($id);
        if (!$product) {
            Response::error('Không tìm thấy sản phẩm.', 404);
        }

        if ($this->productModel->deleteProduct($id)) {
            // Xóa file ảnh trên server
            if (!empty($product->image)) {
                $imgPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $product->image);
                if (file_exists($imgPath)) unlink($imgPath);
            }
            Response::success(null, 'Xóa sản phẩm "' . $product->name . '" thành công.');
        } else {
            Response::error('Xóa sản phẩm thất bại.', 500);
        }
    }

    // ---------------------------------------------------------------
    // HELPER: Validate dữ liệu sản phẩm
    // ---------------------------------------------------------------
    private function validateProduct(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        $name = trim($data['name'] ?? '');
        if ($name === '') {
            $errors['name'] = 'Tên sản phẩm không được để trống.';
        } elseif (mb_strlen($name) > 200) {
            $errors['name'] = 'Tên sản phẩm không được vượt quá 200 ký tự.';
        }

        $price = $data['price'] ?? '';
        if ($price === '' || $price === null) {
            $errors['price'] = 'Giá sản phẩm không được để trống.';
        } elseif (!is_numeric($price)) {
            $errors['price'] = 'Giá sản phẩm phải là số.';
        } elseif ((float)$price <= 0) {
            $errors['price'] = 'Giá sản phẩm phải lớn hơn 0.';
        }

        $category_id = $data['category_id'] ?? '';
        if ($category_id === '' || $category_id === null) {
            $errors['category_id'] = 'Danh mục sản phẩm không được để trống.';
        } elseif (!is_numeric($category_id) || (int)$category_id <= 0) {
            $errors['category_id'] = 'Danh mục sản phẩm không hợp lệ.';
        }

        // Kiểm tra định dạng ảnh nếu truyền qua JSON (tên file)
        if (isset($data['image']) && $data['image'] !== '') {
            $ext = strtolower(pathinfo($data['image'], PATHINFO_EXTENSION));
            $validExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!empty($ext) && !in_array($ext, $validExts)) {
                $errors['image'] = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận: jpg, jpeg, png, gif, webp.';
            }
        }

        return $errors;
    }

    // ---------------------------------------------------------------
    // HELPER: Upload ảnh sản phẩm
    // ---------------------------------------------------------------
    private function uploadImage()
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $targetDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $validExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $validExts)) {
            return false;
        }

        // Kiểm tra file thực sự là ảnh
        if (getimagesize($_FILES['image']['tmp_name']) === false) {
            return false;
        }

        $fileName   = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES['image']['name']));
        $targetFile = $targetDir . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            return false;
        }

        return 'public/images/' . $fileName;
    }
}
?>
