
<?php
require_once '../../includes/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Product.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$product = new Product($db);
$product_id = $_GET['id'] ?? 0;
$prod = $product->readById($product_id);
$categories = $db->getConnection()->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = $_POST['category_id'];
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
    $image = $prod['image'];

    if (empty($name) || empty($price) || empty($category_id) || empty($stock)) {
        $errors[] = "All fields are required.";
    } else {
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $image_file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/png'];
            if (!in_array($image_file['type'], $allowed_types) || $image_file['size'] > 2 * 1024 * 1024) {
                $errors[] = "Invalid image format or size (max 2MB, JPG/PNG only).";
            } else {
                $image = UPLOAD_DIR . uniqid() . '.' . pathinfo($image_file['name'], PATHINFO_EXTENSION);
                move_uploaded_file($image_file['tmp_name'], $image);
            }
        }
        if (empty($errors)) {
            if ($product->update($product_id, $name, $description, $price, $category_id, $image, $stock)) {
                header("Location: manage_products.php");
                exit;
            } else {
                $errors[] = "Failed to update product.";
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
    <title>Edit Product - Wemart</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <main>
        <h1>Edit Product</h1>
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($prod['name']); ?>">
            <label for="description">Description:</label>
            <textarea name="description"><?php echo htmlspecialchars($prod['description']); ?></textarea>
            <label for="price">Price:</label>
            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($prod['price']); ?>">
            <label for="category_id">Category:</label>
            <select name="category_id">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($prod['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="image">Image:</label>
            <input type="file" name="image" accept="image/jpeg,image/png">
            <label for="stock">Stock:</label>
            <input type="number" name="stock" value="<?php echo htmlspecialchars($prod['stock']); ?>">
            <button type="submit">Update Product</button>
        </form>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
