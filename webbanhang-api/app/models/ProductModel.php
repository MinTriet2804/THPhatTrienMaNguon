<?php
class ProductModel
{
    private $conn;
    private $table_name = "product";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy tất cả sản phẩm kèm tên danh mục
    public function getProducts()
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  ORDER BY p.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy sản phẩm theo ID danh mục
    public function getProductsByCategory($category_id)
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  WHERE p.category_id = :category_id
                  ORDER BY p.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy chi tiết một sản phẩm
    public function getProductById($id)
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  WHERE p.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Tìm kiếm sản phẩm theo tên (API mới)
    public function searchByName(string $keyword)
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  WHERE p.name LIKE :keyword
                  ORDER BY p.name ASC";
        $stmt = $this->conn->prepare($query);
        $like = '%' . $keyword . '%';
        $stmt->bindParam(':keyword', $like);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Sắp xếp sản phẩm theo giá (API mới)
    public function getProductsSortedByPrice(string $order = 'asc')
    {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  ORDER BY p.price " . $order;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Thêm sản phẩm mới
    public function addProduct($name, $description, $price, $category_id, $image)
    {
        $errors = [];
        if (empty($name)) { $errors['name'] = 'Tên sản phẩm không được để trống'; }
        if (!is_numeric($price) || $price <= 0) { $errors['price'] = 'Giá sản phẩm phải là số lớn hơn 0'; }
        if (count($errors) > 0) { return $errors; }

        $query = "INSERT INTO " . $this->table_name . " (name, description, price, category_id, image)
                  VALUES (:name, :description, :price, :category_id, :image)";
        $stmt = $this->conn->prepare($query);
        $name        = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price       = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));
        $image       = htmlspecialchars(strip_tags($image));
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);
        return $stmt->execute();
    }

    // Cập nhật sản phẩm
    public function updateProduct($id, $name, $description, $price, $category_id, $image)
    {
        $query = "UPDATE " . $this->table_name . "
                  SET name=:name, description=:description, price=:price, category_id=:category_id, image=:image
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $id          = htmlspecialchars(strip_tags($id));
        $name        = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price       = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));
        $image       = htmlspecialchars(strip_tags($image));
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);
        return $stmt->execute();
    }

    // Xóa sản phẩm
    public function deleteProduct($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
