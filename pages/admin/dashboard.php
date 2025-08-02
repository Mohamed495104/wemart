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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Wemart</title>
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
        <h1>Wemart Admin Dashboard</h1>
    </header>
    <main>
        <h1>Admin Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card">
                <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                <p>Manage your Wemart store efficiently.</p>
                <div class="flex gap-4">
                    <a href="manage_product.php" class="button">Manage Products</a>
                    <a href="manage_users.php" class="button">Manage Users</a>
                </div>
            </div>
            <div class="card">
                <h2 class="text-lg font-semibold mb-4">Store Overview</h2>
                <p>Total Products: <?php echo $db->getConnection()->query("SELECT COUNT(*) FROM products")->fetchColumn(); ?></p>
                <p>Total Users: <?php echo $db->getConnection()->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?></p>
                <p>Total Orders: <?php echo $db->getConnection()->query("SELECT COUNT(*) FROM orders")->fetchColumn(); ?></p>
            </div>
        </div>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>