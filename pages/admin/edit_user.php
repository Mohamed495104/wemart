
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($address) || empty($phone) || empty($role)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        if ($user->update($user_id, $name, $email, $address, $phone, $role)) {
            header("Location: manage_users.php");
            exit;
        } else {
            $errors[] = "Failed to update user.";
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
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <main>
        <h1>Edit User</h1>
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($u['name']); ?>">
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>">
            <label for="address">Address:</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($u['address']); ?>">
            <label for="phone">Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($u['phone']); ?>">
            <label for="role">Role:</label>
            <select name="role">
                <option value="user" <?php echo ($u['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo ($u['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
            <button type="submit">Update User</button>
        </form>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
