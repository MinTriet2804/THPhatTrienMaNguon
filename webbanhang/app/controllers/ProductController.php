<?php
require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CategoryModel.php');

class ProductController
{
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    // Action hiển thị tất cả sản phẩm
    public function index()
    {
        $products = $this->productModel->getProducts();
        include 'app/views/product/list.php';
    }

    // Action lọc sản phẩm theo danh mục (Dành cho thanh điều hướng)
    public function category($id)
    {
        $products = $this->productModel->getProductsByCategory($id);
        include 'app/views/product/list.php';
    }

    // Action xem chi tiết sản phẩm
    public function show($id)
    {
        $product = $this->productModel->getProductById($id);
        if ($product) {
            include 'app/views/product/show.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    // Action hiển thị form thêm mới
    public function add()
    {
        $categories = (new CategoryModel($this->db))->getCategories();
        include_once 'app/views/product/add.php';
    }

    // Hàm xử lý upload file ảnh dùng chung
    private function uploadImage()
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Dùng đường dẫn tuyệt đối từ vị trí file controller này
            // __DIR__ = .../webbanhang/app/controllers
            $base_dir = dirname(dirname(__DIR__)); // = .../webbanhang
            $target_dir = $base_dir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;

            // Tự động tạo thư mục nếu chưa có
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Đổi tên file bằng time() để tránh trùng
            $file_name = time() . '_' . basename($_FILES["image"]["name"]);
            // Thay khoảng trắng trong tên file bằng dấu gạch dưới
            $file_name = str_replace(' ', '_', $file_name);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Kiểm tra định dạng đuôi file
            $valid_extensions = array("jpg", "jpeg", "png", "gif", "webp");
            if (!in_array($imageFileType, $valid_extensions)) {
                return '';
            }

            // Kiểm tra nội dung file có phải ảnh thật không
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                return '';
            }

            // Di chuyển file từ thư mục tạm lên server
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                return $file_name;
            }
        }
        return '';
    }

    // Action xử lý lưu sản phẩm mới thêm
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? '';
            $category_id = $_POST['category_id'] ?? null;

            // Tiến hành upload file ảnh
            $image = $this->uploadImage();

            $result = $this->productModel->addProduct($name, $description, $price, $category_id, $image);

            if (is_array($result)) {
                $errors = $result;
                $categories = (new CategoryModel($this->db))->getCategories();
                include 'app/views/product/add.php';
            } else {
                header('Location: /webbanhang/Product');
            }
        }
    }

    // Action hiển thị form chỉnh sửa
    public function edit($id)
    {
        $product = $this->productModel->getProductById($id);
        $categories = (new CategoryModel($this->db))->getCategories();
        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    // Action xử lý cập nhật sản phẩm
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];
            
            $currentProduct = $this->productModel->getProductById($id);
            $old_image = $currentProduct ? $currentProduct->image : '';

            $new_image = $this->uploadImage();
            $image_to_save = !empty($new_image) ? $new_image : $old_image;
            
            $edit = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image_to_save);
            
            if ($edit) {
                // Xóa ảnh cũ nếu có ảnh mới
                if (!empty($new_image) && !empty($old_image)) {
                    $base_dir = dirname(dirname(__DIR__));
                    $old_path = $base_dir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $old_image;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                header('Location: /webbanhang/Product');
            } else {
                echo "Đã xảy ra lỗi khi lưu sản phẩm.";
            }
        }
    }

    // Action xóa sản phẩm
    public function delete($id)
    {
        $product = $this->productModel->getProductById($id);
        
        if ($product) {
            $image_name = $product->image;
            
            if ($this->productModel->deleteProduct($id)) {
                // Xóa file ảnh vật lý nếu tồn tại
                if (!empty($image_name)) {
                    $base_dir = dirname(dirname(__DIR__));
                    $img_path = $base_dir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $image_name;
                    if (file_exists($img_path)) {
                        unlink($img_path);
                    }
                }
                header('Location: /webbanhang/Product');
            } else {
                echo "Đã xảy ra lỗi khi xóa sản phẩm.";
            }
        } else {
            echo "Sản phẩm không tồn tại.";
        }
    }
}
?>