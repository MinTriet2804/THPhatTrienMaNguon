<?php
class CategoryModel
{
    private $conn;
    private $table_name = "category";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy tất cả danh mục kèm số lượng sản phẩm
    public function getCategories()
    {
        $query = "SELECT c.id, c.name, c.description,
                         COUNT(p.id) AS product_count
                  FROM " . $this->table_name . " c
                  LEFT JOIN product p ON p.category_id = c.id
                  GROUP BY c.id, c.name, c.description
                  ORDER BY c.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy một danh mục theo ID
    public function getCategoryById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Thêm danh mục mới
    public function addCategory($name, $description)
    {
        $errors = [];
        if (empty(trim($name))) {
            $errors['name'] = 'Tên danh mục không được để trống.';
        }
        if (count($errors) > 0) {
            return $errors;
        }

        $query = "INSERT INTO " . $this->table_name . " (name, description) VALUES (:name, :description)";
        $stmt  = $this->conn->prepare($query);
        $name        = htmlspecialchars(strip_tags(trim($name)));
        $description = htmlspecialchars(strip_tags(trim($description)));
        $stmt->bindParam(':name',        $name);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    // Cập nhật danh mục
    public function updateCategory($id, $name, $description)
    {
        $errors = [];
        if (empty(trim($name))) {
            $errors['name'] = 'Tên danh mục không được để trống.';
        }
        if (count($errors) > 0) {
            return $errors;
        }

        $query = "UPDATE " . $this->table_name . "
                  SET name = :name, description = :description
                  WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        $name        = htmlspecialchars(strip_tags(trim($name)));
        $description = htmlspecialchars(strip_tags(trim($description)));
        $stmt->bindParam(':id',          $id,          PDO::PARAM_INT);
        $stmt->bindParam(':name',        $name);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    // Xóa danh mục
    public function deleteCategory($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
