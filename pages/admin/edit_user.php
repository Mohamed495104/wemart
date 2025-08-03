<?php
require_once '../../includes/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/User.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$user = new User($db);
$user_id = $_GET['id'] ?? 0;
$u = $user->readById($user_id);
$errors = [];
$success = [];


if (!$u) {
    header("Location: manage_users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['update_user'])) {
        $name = trim(filter_var($_POST['name'], FILTER_SANITIZE_STRING));
        $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
        $phone = trim(filter_var($_POST['phone'], FILTER_SANITIZE_STRING));
        $role = $_POST['role'];
        
        
        // Address fields
        $address_line = trim(filter_var($_POST['address_line'], FILTER_SANITIZE_STRING));
        $city = trim(filter_var($_POST['city'], FILTER_SANITIZE_STRING));
        $state = trim(filter_var($_POST['state'], FILTER_SANITIZE_STRING));
        $postal_code = trim(filter_var($_POST['postal_code'], FILTER_SANITIZE_STRING));
        $country = trim(filter_var($_POST['country'], FILTER_SANITIZE_STRING));

        
        if (empty($name) || empty($email) || empty($role)) {
            $errors[] = "Name, email, and role are required.";
            $debug[] = "Validation failed - missing required fields";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
            $debug[] = "Email validation failed";
        } else {
            
            
            // Update user information
            $update_result = $user->update($user_id, $name, $email, $role, $phone);
            $debug[] = "Update result: " . ($update_result ? 'SUCCESS' : 'FAILED');
            
            if ($update_result) {
                $success[] = "User information updated successfully.";
                
                // Update address if provided
                if (!empty($address_line) && !empty($city) && !empty($state) && !empty($postal_code) && !empty($country)) {
                    $address_result = $user->updateAddress($user_id, $address_line, $city, $state, $postal_code, $country);
                    $debug[] = "Address update result: " . ($address_result ? 'SUCCESS' : 'FAILED');
                    
                    if ($address_result) {
                        $success[] = "Address updated successfully.";
                    } else {
                        $errors[] = "Failed to update address.";
                    }
                }
                
                // Refresh user data
                $u = $user->readById($user_id);
                
                
            } else {
                $errors[] = "Failed to update user information. Email might already be in use.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Wemart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <aside class="sidebar">
        <h2>Wemart Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_product.php">Manage Products</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="../login.php">Logout</a>
    </aside>
    <header>
        <h1>Edit User</h1>
    </header>
    <main>
        <div class="flex justify-between items-center mb-6">
            <h1>Edit User: <?php echo htmlspecialchars($u['name']); ?></h1>
            <a href="manage_users.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">← Back to Users</a>
        </div>
        
        <?php if ($debug): ?>
            <div class="debug">
                <h4>Debug Information:</h4>
                <?php foreach ($debug as $msg): ?>
                    <div><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <ul class="success">
                <?php foreach ($success as $msg): ?>
                    <li><?php echo htmlspecialchars($msg); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
       
        <div class="bg-gray-100 p-4 rounded mb-6">
            <h3 class="font-bold mb-2">Current User Data:</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><strong>ID:</strong> <?php echo htmlspecialchars($u['user_id']); ?></div>
                <div><strong>Name:</strong> <?php echo htmlspecialchars($u['name']); ?></div>
                <div><strong>Email:</strong> <?php echo htmlspecialchars($u['email']); ?></div>
                <div><strong>Role:</strong> <span class="bg-blue-100 px-2 py-1 rounded"><?php echo htmlspecialchars($u['role']); ?></span></div>
                <div><strong>Phone:</strong> <?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></div>
                <div><strong>Created:</strong> <?php echo $u['created_at'] ? date('Y-m-d H:i:s', strtotime($u['created_at'])) : 'N/A'; ?></div>
            </div>
        </div>
        
    
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold mb-4">Update User Information</h3>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block font-medium mb-2">Name: <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" required 
                           class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"
                           value="<?php echo htmlspecialchars($u['name']); ?>">
                </div>
                <div>
                    <label for="email" class="block font-medium mb-2">Email: <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" required
                           class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"
                           value="<?php echo htmlspecialchars($u['email']); ?>">
                </div>
                <div>
                    <label for="phone" class="block font-medium mb-2">Phone:</label>
                    <input type="text" name="phone" id="phone"
                           class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"
                           value="<?php echo htmlspecialchars($u['phone'] ?? ''); ?>">
                </div>
                <div>
                    <label for="role" class="block font-medium mb-2">Role: <span class="text-red-500">*</span></label>
                    <select name="role" id="role" required class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select Role --</option>
                        <option value="customer" <?php echo ($u['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="user" <?php echo ($u['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo ($u['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
              
                <div class="md:col-span-2">
                    <h4 class="font-medium text-lg mb-3 mt-4 border-b border-gray-200 pb-2">Address Information (Optional)</h4>
                </div>
                <div>
                    <label for="address_line" class="block font-medium mb-2">Address Line:</label>
                    <input type="text" name="address_line" id="address_line"
                           class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"
                           value="<?php echo htmlspecialchars($u['address_line'] ?? ''); ?>">
                </div>
                <div>
                    <label for="city" class="block font-medium mb-2">City:</label>
                    <input type="text" name="city" id="city"
                           class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"
                           value="<?php echo htmlspecialchars($u['city'] ?? ''); ?>">
                </div>
                <div>
                    <label for="state" class="block font-medium mb-2">State:</label>
                    <input type="text" name="state" id="state"
                           class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"
                           value="<?php echo htmlspecialchars($u['state'] ?? ''); ?>">
                </div>
                <div>
                    <label for="postal_code" class="block font-medium mb-2">Postal Code:</label>
                    <input type="text" name="postal_code" id="postal_code"
                           class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"
                           value="<?php echo htmlspecialchars($u['postal_code'] ?? ''); ?>">
                </div>
                <div class="md:col-span-2">
                    <label for="country" class="block font-medium mb-2">Country:</label>
                    <input type="text" name="country" id="country"
                           class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"
                           value="<?php echo htmlspecialchars($u['country'] ?? ''); ?>">
                </div>
                
                <div class="md:col-span-2 mt-6">
                    <button type="submit" name="update_user" class="bg-blue-500 text-white px-8 py-3 rounded hover:bg-blue-600 font-medium">
                        Update User Information
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>