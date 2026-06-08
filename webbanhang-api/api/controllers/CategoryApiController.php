<?php
/**
 * CategoryApiController
 *
 * GET    /api/categories          — Danh sách danh mục
 * GET    /api/categories/{id}     — Chi tiết danh mục
 * POST   /api/categories          — Thêm danh mục (Admin)
 * PUT    /api/categories/{id}     — Cập nhật danh mục (Admin)
 * DELETE /api/categories/{id}     — Xóa danh mục (Admin) — không được xóa nếu còn sản phẩm
 */
class CategoryApiController
{
    private $categoryModel;
    private $db;

    public function __construct($db)
    {
        $this->db            = $db;
        $this->categoryModel = new CategoryModel($db);
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
                $id ? $this->update((int)$id) : Response::error('Thiếu ID danh mục.', 400);
                break;
            case 'DELETE':
                $id ? $this->delete((int)$id) : Response::error('Thiếu ID danh mục.', 400);
                break;
            default:
                Response::error('Phương thức HTTP không được hỗ trợ.', 405);
        }
    }

    // GET /api/categories
    private function index(): void
    {
        $categories = $this->categoryModel->getCategories();
        Response::success($categories, 'Lấy danh sách danh mục thành công.');
    }

    // GET /api/categories/{id}
    private function show(int $id): void
    {
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            Response::error('Không tìm thấy danh mục.', 404);
        }
        Response::success($category, 'Lấy chi tiết danh mục thành công.');
    }

    // POST /api/categories  (Admin)
    private function store(): void
    {
        Auth::requireAdmin();

        $data = $this->getInput();
        $errors = $this->validateCategory($data);
        if (!empty($errors)) {
            Response::error('Dữ liệu không hợp lệ.', 422, $errors);
        }

        $name        = trim($data['name']);
        $description = trim($data['description'] ?? '');

        $result = $this->categoryModel->addCategory($name, $description);
        if (is_array($result)) {
            Response::error('Lỗi khi lưu danh mục.', 422, $result);
        }

        $lastId   = $this->db->lastInsertId();
        $category = $this->categoryModel->getCategoryById($lastId);
        Response::success($category, 'Thêm danh mục mới thành công.', 201);
    }

    // PUT /api/categories/{id}  (Admin)
    private function update(int $id): void
    {
        Auth::requireAdmin();

        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            Response::error('Không tìm thấy danh mục.', 404);
        }

        $data   = $this->getInput();
        $errors = $this->validateCategory($data);
        if (!empty($errors)) {
            Response::error('Dữ liệu không hợp lệ.', 422, $errors);
        }

        $name        = trim($data['name']);
        $description = trim($data['description'] ?? $category->description ?? '');

        $result = $this->categoryModel->updateCategory($id, $name, $description);
        if (is_array($result)) {
            Response::error('Lỗi khi cập nhật danh mục.', 422, $result);
        }

        $updated = $this->categoryModel->getCategoryById($id);
        Response::success($updated, 'Cập nhật danh mục thành công.');
    }

    // DELETE /api/categories/{id}  (Admin)
    private function delete(int $id): void
    {
        Auth::requireAdmin();

        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            Response::error('Không tìm thấy danh mục.', 404);
        }

        // Kiểm tra còn sản phẩm thuộc danh mục này không
        $productCount = $this->countProductsInCategory($id);
        if ($productCount > 0) {
            Response::error(
                'Không thể xóa danh mục "' . $category->name . '" vì vẫn còn ' . $productCount . ' sản phẩm thuộc danh mục này. Hãy chuyển hoặc xóa sản phẩm trước.',
                409
            );
        }

        if ($this->categoryModel->deleteCategory($id)) {
            Response::success(null, 'Xóa danh mục "' . $category->name . '" thành công.');
        } else {
            Response::error('Xóa danh mục thất bại.', 500);
        }
    }

    // ---------------------------------------------------------------
    // HELPER: Đếm sản phẩm trong danh mục
    // ---------------------------------------------------------------
    private function countProductsInCategory(int $category_id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM product WHERE category_id = :id");
        $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // HELPER: Lấy input từ request
    private function getInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return Response::getJsonInput();
        }
        parse_str(file_get_contents('php://input'), $data);
        return !empty($data) ? $data : $_POST;
    }

    // HELPER: Validate
    private function validateCategory(array $data): array
    {
        $errors = [];
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            $errors['name'] = 'Tên danh mục không được để trống.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Tên danh mục không được vượt quá 100 ký tự.';
        }
        return $errors;
    }
}
?>
