

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

if (isset($_POST['delete_user'])) {
    $user->delete($_POST['user_id']);
}

$users = $user->readAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Wemart</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <main>
        <h1>Manage Users</h1>
        <table class="user-table">
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['role']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $u['user_id']; ?>">Edit</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                            <button type="submit" name="delete_user" onclick="return confirm('Are you sure?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>

