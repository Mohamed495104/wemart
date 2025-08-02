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
$errors = [];
$success = [];

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $current_user_id = $_SESSION['user_id'];
    
    // Prevent admin from deleting themselves
    if ($user_id == $current_user_id) {
        $errors[] = "You cannot delete your own account.";
    } else {
        if ($user->delete($user_id)) {
            $success[] = "User deleted successfully.";
        } else {
            $errors[] = "Failed to delete user.";
        }
    }
}

$users = $user->readAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Wemart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    
</head>
<body>
    <aside class="sidebar">
        <h2>Wemart Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_product.php">Manage Products</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="../logout.php">Logout</a>
    </aside>
    <header>
        <h1>Manage Users</h1>
    </header>
    <main>
        <h1>Manage Users</h1>
        
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
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Name</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Phone</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Role</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Created</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($u['user_id']); ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($u['name']); ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <span class="role-badge role-<?php echo strtolower($u['role']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($u['role'])); ?>
                                    </span>
                                </td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <?php echo $u['created_at'] ? date('M j, Y', strtotime($u['created_at'])) : 'N/A'; ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <a href="edit_user.php?id=<?php echo $u['user_id']; ?>" class="button-small">Edit</a>
                                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                        <button type="button" class="delete" onclick="deleteUser(<?php echo $u['user_id']; ?>, '<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>')">Delete</button>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                No users found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>


    </main>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script>
        function deleteUser(userId, userName) {
            if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_id';
                input.value = userId;
                
                const submitInput = document.createElement('input');
                submitInput.type = 'hidden';
                submitInput.name = 'delete_user';
                submitInput.value = '1';
                
                form.appendChild(input);
                form.appendChild(submitInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>