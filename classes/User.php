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

    public function register($name, $email, $password, $phone, $address_line, $city, $state, $postal_code, $country, $user_type = 'user') {
        try {
            $this->db->getConnection()->beginTransaction();

            // Check for duplicate email
            $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                return ["success" => false, "error" => "Email is already registered. <a href='login.php'>Login here</a>."];
            }

            // Insert into users with dynamic role
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hashed_password, $phone, $user_type]);
            $user_id = $this->db->getConnection()->lastInsertId();

            // Insert into addresses
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO addresses (user_id, address_line, city, state, postal_code, country, address_type)
                 VALUES (?, ?, ?, ?, ?, ?, 'billing')"
            );
            $stmt->execute([$user_id, $address_line, $city, $state, $postal_code, $country]);

            $this->db->getConnection()->commit();
            return ["success" => true];
        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return ["success" => false, "error" => "Registration failed: " . htmlspecialchars($e->getMessage(), ENT_QUOTES)];
        }
    }

    // Method to get all users (for admin panel)
    public function readAll() {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT u.user_id, u.name, u.email, u.role, u.phone, u.created_at,
                       a.address_line, a.city, a.state, a.postal_code, a.country
                FROM users u
                LEFT JOIN addresses a ON u.user_id = a.user_id AND a.address_type = 'billing'
                ORDER BY u.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }

    // Method to get user by ID
    public function readById($user_id) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT u.user_id, u.name, u.email, u.role, u.phone, u.created_at,
                       a.address_line, a.city, a.state, a.postal_code, a.country
                FROM users u
                LEFT JOIN addresses a ON u.user_id = a.user_id AND a.address_type = 'billing'
                WHERE u.user_id = ?
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return false;
        }
    }

    // Method to delete user
    public function delete($user_id) {
        try {
            // Check if user exists and is not the current admin
            $current_user_id = $_SESSION['user_id'] ?? 0;
            if ($user_id == $current_user_id) {
                return false; // Don't allow self-deletion
            }

            $this->db->getConnection()->beginTransaction();

            // Delete related addresses first
            $stmt = $this->db->getConnection()->prepare("DELETE FROM addresses WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Delete related orders (or you might want to keep them for records)
            // $stmt = $this->db->getConnection()->prepare("DELETE FROM orders WHERE user_id = ?");
            // $stmt->execute([$user_id]);

            // Delete the user
            $stmt = $this->db->getConnection()->prepare("DELETE FROM users WHERE user_id = ?");
            $result = $stmt->execute([$user_id]);

            $this->db->getConnection()->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    // Method to update user
    public function update($user_id, $name, $email, $role, $phone = null) {
        try {
            $this->db->getConnection()->beginTransaction();

            // Check if email is already taken by another user
            $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                return false; // Email already exists
            }

            // Update user
            $stmt = $this->db->getConnection()->prepare("
                UPDATE users 
                SET name = ?, email = ?, role = ?, phone = ?
                WHERE user_id = ?
            ");
            $result = $stmt->execute([$name, $email, $role, $phone, $user_id]);

            $this->db->getConnection()->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    // Method to update user address
    public function updateAddress($user_id, $address_line, $city, $state, $postal_code, $country) {
        try {
            // Check if address exists
            $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ? AND address_type = 'billing'");
            $stmt->execute([$user_id]);
            
            if ($stmt->fetchColumn() > 0) {
                // Update existing address
                $stmt = $this->db->getConnection()->prepare("
                    UPDATE addresses 
                    SET address_line = ?, city = ?, state = ?, postal_code = ?, country = ?
                    WHERE user_id = ? AND address_type = 'billing'
                ");
                return $stmt->execute([$address_line, $city, $state, $postal_code, $country, $user_id]);
            } else {
                // Insert new address
                $stmt = $this->db->getConnection()->prepare("
                    INSERT INTO addresses (user_id, address_line, city, state, postal_code, country, address_type)
                    VALUES (?, ?, ?, ?, ?, ?, 'billing')
                ");
                return $stmt->execute([$user_id, $address_line, $city, $state, $postal_code, $country]);
            }
        } catch (PDOException $e) {
            error_log("Error updating address: " . $e->getMessage());
            return false;
        }
    }

    // Method to create new user (admin function)
    public function create($name, $email, $password, $role = 'customer', $phone = null) {
        try {
            // Check if email already exists
            $stmt = $this->db->getConnection()->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                return false; // Email already exists
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO users (name, email, password, role, phone) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$name, $email, $hashed_password, $role, $phone]);
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    // Method to update password
    public function updatePassword($user_id, $new_password) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->getConnection()->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            return $stmt->execute([$hashed_password, $user_id]);
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    // Method to get user count for dashboard
    public function getUserCount() {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting user count: " . $e->getMessage());
            return 0;
        }
    }

    // Method to get users by role
    public function getUsersByRole($role) {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
            $stmt->execute([$role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching users by role: " . $e->getMessage());
            return [];
        }
    }

    // Method to verify current password (for password change)
    public function verifyPassword($user_id, $password) {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error verifying password: " . $e->getMessage());
            return false;
        }
    }

    // Method to get user profile with address
    public function getProfile($user_id) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT u.user_id, u.name, u.email, u.phone, u.created_at,
                       a.address_line, a.city, a.state, a.postal_code, a.country
                FROM users u
                LEFT JOIN addresses a ON u.user_id = a.user_id AND a.address_type = 'billing'
                WHERE u.user_id = ?
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user profile: " . $e->getMessage());
            return false;
        }
    }
}
?>