
<?php
class Product {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db->getConnection();
    }

    public function create($name, $description, $price, $category_id, $image, $stock) {
        $stmt = $this->db->prepare("INSERT INTO products (name, description, price, category_id, image, stock) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $description, $price, $category_id, $image, $stock]);
    }

    public function readAll() {
        $stmt = $this->db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById($id) {
        $stmt = $this->db->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $description, $price, $category_id, $image, $stock) {
        $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ?, stock = ? WHERE product_id = ?");
        return $stmt->execute([$name, $description, $price, $category_id, $image, $stock, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE product_id = ?");
        return $stmt->execute([$id]);
    }

    public function search($keyword, $category_id = null) {
        $query = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE (p.name LIKE ? OR p.description LIKE ?)";
        $params = ["%$keyword%", "%$keyword%"];
        if ($category_id) {
            $query .= " AND p.category_id = ?";
            $params[] = $category_id;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
