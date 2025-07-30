<?php
class User {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function login($email, $password) {
        $stmt = $this->db->getConnection()->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    public function register($name, $email, $password, $phone, $address_line, $city, $state, $postal_code, $country) {
        try {
            $this->db->getConnection()->beginTransaction();

            // Check for duplicate email
            $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                return ["success" => false, "error" => "Email is already registered. <a href='login.php'>Login here</a>."];
            }

            // Insert into users
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->getConnection()->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->execute([$name, $email, $hashed_password, $phone]);
            $user_id = $this->db->getConnection()->lastInsertId();

            // Insert into addresses
            $stmt = $this->db->getConnection()->prepare("INSERT INTO addresses (user_id, address_line, city, state, postal_code, country, address_type) VALUES (?, ?, ?, ?, ?, ?, 'billing')");
            $stmt->execute([$user_id, $address_line, $city, $state, $postal_code, $country]);

            $this->db->getConnection()->commit();
            return ["success" => true];
        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return ["success" => false, "error" => "Registration failed: " . htmlspecialchars($e->getMessage(), ENT_QUOTES)];
        }
    }
}
?>