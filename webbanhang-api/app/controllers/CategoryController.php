<?php
require_once('app/config/database.php');
require_once('app/models/CategoryModel.php');

class CategoryController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db            = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    // Danh sách danh mục
    public function index()
    {
        $categories = $this->categoryModel->getCategories();
        $flash      = $this->getFlash();
        include 'app/views/category/list.php';
    }

    // Form thêm mới
    public function add()
    {
        $errors = [];
        include 'app/views/category/add.php';
    }

    // Xử lý lưu danh mục mới
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/Category');
            exit;
        }
        $name        = $_POST['name']        ?? '';
        $description = $_POST['description'] ?? '';

        $result = $this->categoryModel->addCategory($name, $description);

        if (is_array($result)) {
            $errors = $result;
            include 'app/views/category/add.php';
        } else {
            $this->setFlash('success', 'Thêm danh mục thành công!');
            header('Location: /webbanhang/Category');
            exit;
        }
    }

    // Form chỉnh sửa
    public function edit($id)
    {
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            $this->setFlash('error', 'Không tìm thấy danh mục.');
            header('Location: /webbanhang/Category');
            exit;
        }
        $errors = [];
        include 'app/views/category/edit.php';
    }

    // Xử lý cập nhật
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/Category');
            exit;
        }
        $id          = $_POST['id']          ?? 0;
        $name        = $_POST['name']        ?? '';
        $description = $_POST['description'] ?? '';

        $result = $this->categoryModel->updateCategory($id, $name, $description);

        if (is_array($result)) {
            $errors   = $result;
            $category = $this->categoryModel->getCategoryById($id);
            include 'app/views/category/edit.php';
        } else {
            $this->setFlash('success', 'Cập nhật danh mục thành công!');
            header('Location: /webbanhang/Category');
            exit;
        }
    }

    // Xóa danh mục
    public function delete($id)
    {
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            $this->setFlash('error', 'Không tìm thấy danh mục.');
            header('Location: /webbanhang/Category');
            exit;
        }
        if ($this->categoryModel->deleteCategory($id)) {
            $this->setFlash('success', 'Đã xóa danh mục "' . htmlspecialchars($category->name) . '".');
        } else {
            $this->setFlash('error', 'Xóa danh mục thất bại.');
        }
        header('Location: /webbanhang/Category');
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
