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
$product_id = $_GET['id'] ?? 0;
$p = $product->readById($product_id);

if (!$p) {
    header("Location: manage_product.php");
    exit;
}

$categories = $db->getConnection()->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = $_POST['category_id'];
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
    $deal_price = !empty($_POST['deal_price']) ? filter_var($_POST['deal_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (empty($name) || empty($price) || empty($category_id) || empty($stock)) {
        $errors[] = "Name, price, category, and stock are required.";
    } else {
        $image_path = $p['image']; 
        
        // Check if new image is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/png'];
            if (!in_array($image['type'], $allowed_types) || $image['size'] > 2 * 1024 * 1024) {
                $errors[] = "Invalid image format or size (max 2MB, JPG/PNG only).";
            } else {
                // Generate unique filename
                $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
                $unique_filename = uniqid() . '.' . $file_extension;
                
                // Full path for file system operations
                $full_image_path = UPLOAD_DIR . $unique_filename;
                
                // Relative path for database storage
                $new_relative_path = 'assets/images/products/' . $unique_filename;
                
                // Create upload directory if it doesn't exist
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }
                
                if (move_uploaded_file($image['tmp_name'], $full_image_path)) {
                    // Delete old image if it exists and is different
                    if (!empty($p['image']) && $p['image'] !== $new_relative_path) {
                        $old_image_path = '../../' . $p['image'];
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                    $image_path = $new_relative_path;
                } else {
                    $errors[] = "Failed to upload image.";
                }
            }
        }

        if (empty($errors)) {
            if ($product->update($product_id, $name, $description, $price, $category_id, $image_path, $stock, $featured, $deal_price)) {
                header("Location: manage_product.php");
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <aside class="sidebar">
        <h2>Wemart Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_product.php">Manage Products</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="../logout.php">Logout</a>
    </aside>
    <header>
        <h1>Edit Product</h1>
    </header>
    <main>
        <div class="flex justify-between items-center mb-6">
            <h1>Edit Product: <?php echo htmlspecialchars($p['name']); ?></h1>
            <a href="manage_product.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">← Back to Products</a>
        </div>
        
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold mb-4">Update Product Information</h3>
            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block font-medium mb-2">Name:</label>
                    <input type="text" name="name" id="name" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none" value="<?php echo htmlspecialchars($p['name']); ?>">
                </div>
                <div>
                    <label for="price" class="block font-medium mb-2">Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none" value="<?php echo htmlspecialchars($p['price']); ?>">
                </div>
                <div>
                    <label for="deal_price" class="block font-medium mb-2">Deal Price (optional):</label>
                    <input type="number" name="deal_price" id="deal_price" step="0.01" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none" value="<?php echo htmlspecialchars($p['deal_price'] ?? ''); ?>">
                </div>
                <div>
                    <label for="category_id" class="block font-medium mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($p['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="stock" class="block font-medium mb-2">Stock:</label>
                    <input type="number" name="stock" id="stock" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none" value="<?php echo htmlspecialchars($p['stock']); ?>">
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block font-medium mb-2">Description:</label>
                    <textarea name="description" id="description" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"><?php echo htmlspecialchars($p['description']); ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="image" class="block font-medium mb-2">Image (leave blank to keep current):</label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none">
                    <?php if (!empty($p['image'])): ?>
                        <div class="mt-2">
                            <p class="text-sm text-gray-600">Current image:</p>
                            <img src="../../<?php echo htmlspecialchars($p['image']); ?>" alt="Current product image" class="h-32 w-32 object-cover mt-2 border rounded">
                            <p class="text-xs text-gray-500 mt-1">Path: <?php echo htmlspecialchars($p['image']); ?></p>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-600 mt-2">No current image</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="featured" class="block font-medium mb-2">Featured:</label>
                    <input type="checkbox" name="featured" id="featured" <?php echo $p['featured'] ? 'checked' : ''; ?> class="h-5 w-5 text-blue-500 focus:ring-blue-500 border-gray-300 rounded">
                </div>
                <div class="md:col-span-2 mt-6">
                    <button type="submit" class="bg-blue-500 text-white px-8 py-3 rounded hover:bg-blue-600 font-medium">Update Product</button>
                </div>
            </form>
        </div>
    </main>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>