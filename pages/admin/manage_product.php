<?php
require_once '../../includes/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Product.php';

// Ensure only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$product = new Product($db);
$errors = [];

// Get categories for dropdown
$categories = $db->getConnection()->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Handle product creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_product'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = $_POST['category_id'];
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);

    if (empty($name) || empty($price) || empty($category_id) || empty($stock)) {
        $errors[] = "All fields are required.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Image is required or upload failed.";
    } else {
        // Image validation
        $image = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

        if (!in_array($image['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions) || $image['size'] > 5 * 1024 * 1024) {
            $errors[] = "Invalid image format or size (max 5MB, JPG/JPEG/PNG only).";
        } else {
            // Clean and sanitize original filename
            $original_filename = pathinfo($image['name'], PATHINFO_BASENAME);
            $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_filename);

            // Build safe upload path
            $upload_path = rtrim(UPLOAD_DIR, '/') . '/' . $safe_filename;

            // Create upload directory if it doesn't exist
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0777, true);
            }

            if (file_exists($upload_path)) {
                $errors[] = "File already exists. Please rename and try again.";
            } elseif (move_uploaded_file($image['tmp_name'], $upload_path)) {
                // Save product using only the filename in DB
                if ($product->create($name, $description, $price, $category_id, $safe_filename, $stock)) {
                    header("Location: manage_product.php");
                    exit;
                } else {
                    $errors[] = "Failed to create product.";
                }
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    }
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product->delete($_POST['product_id']);
    header("Location: manage_product.php");
    exit;
}

// Fetch all products for listing
$products = $product->readAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Wemart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>
    <aside class="sidebar">
        <h2>Wemart Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_product.php">Manage Products</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="../login.php">Logout</a>
    </aside>
    <header>
        <h1>Manage Products</h1>
    </header>
    <main>
        <h1>Manage Products</h1>
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-4">Add Product</h2>
            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block font-medium mb-2">Name:</label>
                    <input type="text" name="name" id="name" class="w-full p-3 border border-gray-300 rounded" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div>
                    <label for="price" class="block font-medium mb-2">Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="w-full p-3 border border-gray-300 rounded" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                </div>
                <div>
                    <label for="category_id" class="block font-medium mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="w-full p-3 border border-gray-300 rounded">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="stock" class="block font-medium mb-2">Stock:</label>
                    <input type="number" name="stock" id="stock" class="w-full p-3 border border-gray-300 rounded" value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>">
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block font-medium mb-2">Description:</label>
                    <textarea name="description" id="description" class="w-full p-3 border border-gray-300 rounded"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="image" class="block font-medium mb-2">Image:</label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/jpg,image/png" class="w-full p-3 border border-gray-300 rounded">
                </div>
                <div class="md:col-span-2 mt-6">
                    <button type="submit" name="create_product" class="bg-blue-500 text-white px-8 py-3 rounded hover:bg-blue-600">Add Product</button>
                </div>
            </form>
        </div>

        <h2 class="text-lg font-bold mt-8 mb-4">Product List</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Image</th>
                <th>Action</th>
            </tr>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td>$<?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td><?php echo $p['stock']; ?></td>
                    <td>
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?php echo BASE_URL . 'assets/images/products/' . htmlspecialchars($p['image']); ?>" alt="Product Image" class="h-12 w-12 object-cover">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                            <button type="submit" name="delete_product" class="text-red-600 hover:underline">Delete</button>
                        </form>
                        <a href="edit_product.php?id=<?php echo $p['product_id']; ?>" class="ml-4 text-blue-600 hover:underline">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>

</html>