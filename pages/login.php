<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Ensure session is always started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle logout cleanly 
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Clear session data
    $_SESSION = [];

    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();

    // Redirect to login
    header("Location: login.php");
    exit;
}

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

            if ($_SESSION['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
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
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
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
            <input type="email" name="email" id="email"
                value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>" required
                aria-required="true">
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