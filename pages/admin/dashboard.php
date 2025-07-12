

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
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <main>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="manage_products.php">Manage Products</a>
            <a href="manage_users.php">Manage Users</a>
        </nav>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
