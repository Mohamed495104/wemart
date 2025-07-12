
<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

$db = new Database();
$user = new User($db);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        // Check if email exists and is verified
        $stmt = $db->getConnection()->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user_id = $stmt->fetchColumn();

        if ($user_id) {
            $stmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM email_verifications WHERE user_id = ? AND expires_at > NOW()");
            $stmt->execute([$user_id]);
            $is_verified = $stmt->fetchColumn() > 0;

            if (!$is_verified) {
                $errors[] = "Please verify your email before logging in. <a href='verify_email.php'>Verify now</a>.";
            } else {
                if ($user->login($email, $password)) {
                    header("Location: index.php");
                    exit;
                } else {
                    $errors[] = "Invalid email or password.";
                }
            }
        } else {
            $errors[] = "Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Wemart</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <h1>Login</h1>
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required onblur="checkEmailAvailability(this.value)">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </form>
        <div id="email-message"></div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>
