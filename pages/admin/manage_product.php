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
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Image is required or upload failed: " . ($_FILES['image']['error'] ?? 'No file uploaded');
    } else {
        $image = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($image['type'], $allowed_types) || $image['size'] > 2 * 1024 * 1024) {
            $errors[] = "Invalid image format or size (max 2MB, JPG/PNG only).";
        } else {
           
            $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $unique_filename = uniqid() . '.' . $file_extension;
            
            // Full path for file system operations
            $full_image_path = UPLOAD_DIR . $unique_filename;
            
            // Relative path for database storage (relative to web root)
            $relative_image_path = 'assets/images/products/' . $unique_filename;
            
            $upload_dir = UPLOAD_DIR;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            if (!is_writable($upload_dir)) {
                $errors[] = "Upload directory is not writable: " . $upload_dir;
            } elseif (move_uploaded_file($image['tmp_name'], $full_image_path)) {
                
                if ($product->create($name, $description, $price, $category_id, $relative_image_path, $stock)) {
                    header("Location: manage_product.php");
                    exit;
                } else {
                    $errors[] = "Failed to create product.";
                }
            } else {
                $errors[] = "Failed to move uploaded file to: " . $full_image_path;
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
                    <input type="text" name="name" id="name" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div>
                    <label for="price" class="block font-medium mb-2">Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                </div>
                <div>
                    <label for="category_id" class="block font-medium mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="stock" class="block font-medium mb-2">Stock:</label>
                    <input type="number" name="stock" id="stock" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none" value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>">
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block font-medium mb-2">Description:</label>
                    <textarea name="description" id="description" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="image" class="block font-medium mb-2">Image:</label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png" class="w-full p-3 border border-gray-300 rounded focus:border-blue-500 focus:outline-none">
                </div>
                <div class="md:col-span-2 mt-6">
                    <button type="submit" name="create_product" class="bg-blue-500 text-white px-8 py-3 rounded hover:bg-blue-600 font-medium">Add Product</button>
                </div>
            </form>
        </div>
        <h2 class="text-lg font-bold mt-8 mb-4">Product List</h2>
        <table>
            <tr><th>Name</th><th>Price</th><th>Category</th><th>Stock</th><th>Image</th><th>Action</th></tr>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td>$<?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td><?php echo $p['stock']; ?></td>
                    <td>
                        <?php if (!empty($p['image'])): ?>
                            <img src="../../<?php echo htmlspecialchars($p['image']); ?>" alt="Product Image" class="h-12 w-12 object-cover">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $p['product_id']; ?>">Edit</a>
                        <button type="button" class="delete" onclick="deleteProduct(<?php echo $p['product_id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
    <?php include '../../includes/footer.php'; ?>
    <script>
    function deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
          
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_id';
            input.value = productId;
            
            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'delete_product';
            submitInput.value = '1';
            
            form.appendChild(input);
            form.appendChild(submitInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>