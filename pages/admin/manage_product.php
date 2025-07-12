

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
$errors = [];
$categories = $db->getConnection()->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_product'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = $_POST['category_id'];
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
    
    if (empty($name) || empty($price) || empty($category_id) || empty($stock)) {
        $errors[] = "All fields are required.";
    } elseif (!is_uploaded_file($_FILES['image']['tmp_name'])) {
        $errors[] = "Image is required.";
    } else {
        $image = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($image['type'], $allowed_types) || $image['size'] > 2 * 1024 * 1024) {
            $errors[] = "Invalid image format or size (max 2MB, JPG/PNG only).";
        } else {
            $image_path = UPLOAD_DIR . uniqid() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($image['tmp_name'], $image_path)) {
                if ($product->create($name, $description, $price, $category_id, $image_path, $stock)) {
                    header("Location: manage_products.php");
                    exit;
                } else {
                    $errors[] = "Failed to create product.";
                }
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }
}

if (isset($_POST['delete_product'])) {
    $product->delete($_POST['product_id']);
}

$products = $product->readAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Wemart</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <main>
        <h1>Manage Products</h1>
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <h2>Add Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            <label for="description">Description:</label>
            <textarea name="description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            <label for="price">Price:</label>
            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
            <label for="category_id">Category:</label>
            <select name="category_id">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="image">Image:</label>
            <input type="file" name="image" accept="image/jpeg,image/png">
            <label for="stock">Stock:</label>
            <input type="number" name="stock" value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>">
            <button type="submit" name="create_product">Add Product</button>
        </form>
        <h2>Product List</h2>
        <table class="product-table">
            <tr><th>Name</th><th>Price</th><th>Category</th><th>Stock</th><th>Action</th></tr>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td>$<?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td><?php echo $p['stock']; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $p['product_id']; ?>">Edit</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                            <button type="submit" name="delete_product" onclick="return confirm('Are you sure?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
