<?php
require_once 'app/models/ProductModel.php';

class ProductController
{
    private $products = [];

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['products'])) {
            $this->products = $_SESSION['products'];
        }
    }

    // Action mặc định
    public function index()
    {
        $this->list();
    }

    // Action hiển thị danh sách - Phải tên là 'list' để khớp với /Product/list
    public function list()
    {
        $products = $this->products;
        include 'app/views/product/list.php';
    }

    // Action thêm sản phẩm - Phải tên là 'add' để khớp với /Product/add
    public function add()
    {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $imageName = 'default.jpg';

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $uploadDir = 'public/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imageName = time() . '_' . $_FILES['image']['name'];
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }

            $id = count($this->products) + 1;
            $product = new ProductModel($id, $name, $description, $price, $imageName);
            $this->products[] = $product;
            $_SESSION['products'] = $this->products;

            header('Location: /project1/Product/list');
            exit();
        }
        include 'app/views/product/add.php';
    }

    // Action sửa sản phẩm - Phải tên là 'edit' để khớp với /Product/edit/{id}
    public function edit($id)
    {
        $product = null;
        foreach ($this->products as $p) {
            if ($p->getID() == $id) {
                $product = $p;
                break;
            }
        }

        if (!$product) {
            die("Không tìm thấy sản phẩm có ID: " . $id);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($this->products as $key => $p) {
                if ($p->getID() == $id) {
                    $this->products[$key]->name = $_POST['name'];
                    $this->products[$key]->description = $_POST['description'];
                    $this->products[$key]->price = $_POST['price'];

                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $imageName = time() . '_' . $_FILES['image']['name'];
                        move_uploaded_file($_FILES['image']['tmp_name'], 'public/images/' . $imageName);
                        $this->products[$key]->image = $imageName;
                    }
                    break;
                }
            }
            $_SESSION['products'] = $this->products;
            header('Location: /project1/Product/list');
            exit();
        }
        include 'app/views/product/edit.php';
    }

    // Action xóa sản phẩm
    public function delete($id)
    {
        foreach ($this->products as $key => $p) {
            if ($p->getID() == $id) {
                unset($this->products[$key]);
                break;
            }
        }
        $_SESSION['products'] = array_values($this->products);
        header('Location: /project1/Product/list');
        exit();
    }
}