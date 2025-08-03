<?php
class Cart
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function add($user_id, $product_id, $quantity = 1)
    {
        // Check if product already exists in cart
        $stmt = $this->db->prepare("
            SELECT cart_id, quantity FROM cart 
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing quantity (add to current quantity)
            $new_quantity = $existing['quantity'] + $quantity;
            $stmt = $this->db->prepare("
                UPDATE cart SET quantity = ? WHERE cart_id = ?
            ");
            $result = $stmt->execute([$new_quantity, $existing['cart_id']]);

            // Update session cart
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $new_quantity;
            }

            return $result;
        } else {
            // Add new item
            $stmt = $this->db->prepare("
                INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)
            ");
            $result = $stmt->execute([$user_id, $product_id, $quantity]);

            // Update session cart
            $_SESSION['cart'][$product_id] = $quantity;

            return $result;
        }
    }

    public function update($user_id, $product_id, $quantity)
    {
        if ($quantity <= 0) {
            return $this->remove($user_id, $product_id);
        }

        $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $result = $stmt->execute([$quantity, $user_id, $product_id]);
        $_SESSION['cart'][$product_id] = $quantity;
        return $result;
    }

    public function remove($user_id, $product_id)
    {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $result = $stmt->execute([$user_id, $product_id]);
        unset($_SESSION['cart'][$product_id]);
        return $result;
    }

    public function getCart($user_id)
    {
        $stmt = $this->db->prepare("
            SELECT c.*, p.name, p.price, p.image, p.deal_price,
                   COALESCE(p.deal_price, p.price) as effective_price
            FROM cart c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE c.user_id = ?
            ORDER BY c.cart_id DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCartCount($user_id)
    {
        // Get total quantity (sum of all quantities, not just row count)
        $stmt = $this->db->prepare("
            SELECT SUM(quantity) as total_quantity FROM cart WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total_quantity'] ?? 0);
    }

    // Method to sync session cart with database (useful for consistency)
    public function syncSessionCart($user_id)
    {
        $cart_items = $this->getCart($user_id);
        $_SESSION['cart'] = [];
        foreach ($cart_items as $item) {
            $_SESSION['cart'][$item['product_id']] = $item['quantity'];
        }
    }
}
