
<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

$db = new Database();
$user = new User($db);
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);

    if (empty($name) || empty($email) || empty($password) || empty($address) || empty($phone)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } else {
        $stmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email already registered.";
        } else {
            if ($user->register($name, $email, $password, $address, $phone)) {
                $user_id = $db->getConnection()->lastInsertId();
                $token = bin2hex(random_bytes(16)); // Generate random token
                $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
                $stmt = $db->getConnection()->prepare("INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, ?)");
                if ($stmt->execute([$user_id, $token, $expires_at])) {
                    // In production, send $token via email. For testing, display it.
                    $success = "Registration successful! Please verify your email. Verification token: $token (Check email in production).";
                } else {
                    $errors[] = "Failed to generate verification token.";
                }
            } else {
                $errors[] = "Registration failed.";
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
    <title>Register - Wemart</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <h1>Register</h1>
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required onblur="checkEmailAvailability(this.value)">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <label for="address">Address:</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
            <label for="phone">Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
        <div id="email-message"></div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>
