<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Session is started in config.php, no need to call here
$db = new Database();
$user = new User($db);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        if ($user->login($email, $password)) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Invalid email or password. Create an account if you don't have one.";
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
            <ul class="errors" role="alert">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="POST" aria-labelledby="login-form">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>" required aria-required="true">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required aria-required="true">
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
</body>
</html>