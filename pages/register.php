<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Session is started
$db = new Database();
$user = new User($db);
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_STRING);
    $address_line = filter_var($_POST['address_line'] ?? '', FILTER_SANITIZE_STRING);
    $city = filter_var($_POST['city'] ?? '', FILTER_SANITIZE_STRING);
    $state = filter_var($_POST['state'] ?? '', FILTER_SANITIZE_STRING);
    $postal_code = filter_var($_POST['postal_code'] ?? '', FILTER_SANITIZE_STRING);
    $country = filter_var($_POST['country'] ?? '', FILTER_SANITIZE_STRING);
    $user_type = filter_var($_POST['user_type'] ?? 'user', FILTER_SANITIZE_STRING);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($address_line) || empty($city) || empty($state) || empty($postal_code) || empty($country)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif (!in_array($user_type, ['admin', 'user'])) {
        $errors[] = "Invalid user type selected.";
    } else {
        $result = $user->register($name, $email, $password, $phone, $address_line, $city, $state, $postal_code, $country, $user_type);
        if ($result['success']) {
            $success = "Registration successful! Please Login to continue.";
        } else {
            $errors[] = $result['error'];
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
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <style>
        /* Email validation message styles */
        .email-message {
            margin-top: 5px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            display: none;
        }

        .email-available {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .email-exists {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .email-loading {
            background-color: #e2e3e5;
            color: #495057;
            border: 1px solid #d6d8db;
        }

        .email-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <h1 id="register-form">Register</h1>
        <?php if ($errors): ?>
            <ul class="errors" role="alert">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success" role="alert"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></p>
        <?php endif; ?>
        <form method="POST" aria-labelledby="register-form">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name"
                value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>" required aria-required="true">

            <label for="email">Email:</label>
            <input type="email" name="email" id="email"
                value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>" required aria-required="true">
            <div id="email-message" class="email-message"></div>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required aria-required="true">

            <label for="phone">Phone:</label>
            <input type="text" name="phone" id="phone"
                value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES); ?>" required
                aria-required="true">

            <label for="address_line">Address Line:</label>
            <input type="text" name="address_line" id="address_line"
                value="<?php echo htmlspecialchars($_POST['address_line'] ?? '', ENT_QUOTES); ?>" required
                aria-required="true">

            <label for="city">City:</label>
            <input type="text" name="city" id="city"
                value="<?php echo htmlspecialchars($_POST['city'] ?? '', ENT_QUOTES); ?>" required aria-required="true">

            <label for="state">State/Province:</label>
            <input type="text" name="state" id="state"
                value="<?php echo htmlspecialchars($_POST['state'] ?? '', ENT_QUOTES); ?>" required
                aria-required="true">

            <label for="postal_code">Postal Code:</label>
            <input type="text" name="postal_code" id="postal_code"
                value="<?php echo htmlspecialchars($_POST['postal_code'] ?? '', ENT_QUOTES); ?>" required
                aria-required="true">

            <label for="country">Country:</label>
            <input type="text" name="country" id="country"
                value="<?php echo htmlspecialchars($_POST['country'] ?? '', ENT_QUOTES); ?>" required
                aria-required="true">

            <label for="user_type">User Type:</label>
            <select name="user_type" id="user_type" required aria-required="true">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit" id="submit-btn">Register</button>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/checkEmailAvailability.js?v=<?php echo time(); ?>"></script>
</body>

</html>