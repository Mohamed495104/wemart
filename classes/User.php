
<?php
class User {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db->getConnection();
    }

    public function register($name, $email, $password, $address, $phone) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, address, phone) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $email, $hashed_password, $address, $phone]);
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            return $user;
        }
        return false;
    }

    public function readAll() {
        $stmt = $this->db->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $email, $address, $phone, $role) {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, address = ?, phone = ?, role = ? WHERE user_id = ?");
        return $stmt->execute([$name, $email, $address, $phone, $role, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = ?");
        return $stmt->execute([$id]);
    }
}
?>
