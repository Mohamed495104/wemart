
<?php
class Cart {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db->getConnection();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function add($user_id, $product_id, $quantity) {
        $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
        $_SESSION['cart'][$product_id] = $quantity;
    }

    public function update($user_id, $product_id, $quantity) {
        $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $user_id, $product_id]);
        $_SESSION['cart'][$product_id] = $quantity;
    }

    public function remove($user_id, $product_id) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        unset($_SESSION['cart'][$product_id]);
    }

    public function getCart($user_id) {
        $stmt = $this->db->prepare("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
