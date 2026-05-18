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

    // Hàm xử lý upload file ảnh dùng chung (Đã nâng cấp bảo mật)
    private function uploadImage()
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "public/images/";
            
            // Tự động tạo thư mục chứa ảnh nếu hệ thống chưa có
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Đổi tên file bằng hàm time() để tránh trùng tên ảnh
            $file_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // 1. Kiểm tra định dạng đuôi file mở rộng
            $valid_extensions = array("jpg", "jpeg", "png", "gif", "webp");
            if (!in_array($imageFileType, $valid_extensions)) {
                return '';
            }

            // 2. BẢO MẬT: Kiểm tra nội dung file có phải là ảnh thật không (chống mã độc shell script)
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

    // Action xử lý cập nhật sản phẩm (Đã sửa lỗi giữ ảnh cũ và dọn dẹp ảnh rác)
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];
            
            // 1. Lấy thông tin sản phẩm hiện tại để tìm tên file ảnh cũ
            $currentProduct = $this->productModel->getProductById($id);
            $old_image = $currentProduct ? $currentProduct->image : '';

            // 2. Kiểm tra xem người dùng có chọn file ảnh mới hay không
            $new_image = $this->uploadImage();
            
            // Nếu có ảnh mới thì sử dụng ảnh mới, ngược lại giữ nguyên tên ảnh cũ
            $image_to_save = !empty($new_image) ? $new_image : $old_image;
            
            // 3. Cập nhật vào cơ sở dữ liệu
            $edit = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image_to_save);
            
            if ($edit) {
                // Nếu cập nhật thành công VÀ có upload ảnh mới -> Tiến hành xóa file ảnh cũ trên ổ đĩa server
                if (!empty($new_image) && !empty($old_image) && file_exists("public/images/" . $old_image)) {
                    unlink("public/images/" . $old_image);
                }
                header('Location: /webbanhang/Product');
            } else {
                echo "Đã xảy ra lỗi khi lưu sản phẩm.";
            }
        }
    }

    // Action xóa sản phẩm (Đã thêm chức năng tự động xóa file ảnh vật lý trên Server)
    public function delete($id)
    {
        // 1. Lấy thông tin sản phẩm để lấy tên file ảnh trước khi thực hiện xóa dữ liệu
        $product = $this->productModel->getProductById($id);
        
        if ($product) {
            $image_name = $product->image;
            
            // 2. Tiến hành xóa bản ghi trong Database
            if ($this->productModel->deleteProduct($id)) {
                // 3. Xóa file ảnh vật lý nằm trong thư mục public/images/ nếu tồn tại
                if (!empty($image_name) && file_exists("public/images/" . $image_name)) {
                    unlink("public/images/" . $image_name);
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