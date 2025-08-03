<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Product.php';

$db = new Database();
$product = new Product($db);

// Handle AJAX search
if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $search_term = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? null;

    $products = $search_term ? $product->search($search_term, $category) : $product->readAll();

    $is_first_order = true;
    $discount_code = 'WEMART20';
    $discount_percentage = 0.20;

    $html = '';
    foreach ($products as $p) {
        $base_price = $p['deal_price'] !== null ? $p['deal_price'] : $p['price'];
        $discounted_price = $is_first_order ? $base_price * (1 - $discount_percentage) : $base_price;

        $html .= '<div class="product-card">';
        $html .= '<a href="product_details.php?id=' . $p['product_id'] . '" class="card-link">';
        $html .= '<img src="../assets/images/products/' . htmlspecialchars($p['image']) . '" alt="' . htmlspecialchars($p['name']) . ' product image" />';
        $html .= '<h3>' . htmlspecialchars($p['name']) . '</h3>';
        $html .= '<p>' . htmlspecialchars($p['category_name']) . '</p>';
        $html .= '<p class="price">';
        if ($is_first_order && $base_price != $discounted_price) {
            $html .= '<span class="original-price">$' . number_format($base_price, 2) . '</span>';
            $html .= '$' . number_format($discounted_price, 2);
            $html .= '<span class="discount-note">(20% off with ' . htmlspecialchars($discount_code) . ')</span>';
        } else {
            $html .= '$' . number_format($base_price, 2);
        }
        $html .= '</p>';
        $html .= '</a>';
        $html .= '<div class="cart-actions">';
        $html .= '<input type="number" value="1" min="1" max="' . $p['stock'] . '" class="qty" />';
        $html .= '<button class="add-to-cart" data-id="' . $p['product_id'] . '">Add to Cart</button>';
        $html .= '</div>';
        $html .= '</div>';
    }

    echo json_encode(['html' => $html]);
    exit;
}

$products = isset($_GET['search']) ? $product->search($_GET['search'], $_GET['category'] ?? null) : $product->readAll();
$categories = $db->getConnection()->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$is_first_order = true;
$discount_code = 'WEMART20';
$discount_percentage = 0.20;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wemart - Your Everyday Shopping Destination</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            align-items: stretch;
        }

        .product-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card a.card-link {
            display: block;
            text-decoration: none;
            color: inherit;
            padding: 1rem;
            flex-grow: 1;
        }

        .product-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .product-card h3 {
            font-size: 1.1rem;
            margin: 0.5rem 0 0.25rem;
            font-weight: 600;
        }

        .product-card p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
        }

        .product-card .price {
            font-weight: bold;
            margin-top: 0.5rem;
        }

        .product-card .original-price {
            color: #999;
            text-decoration: line-through;
            margin-right: 0.5rem;
        }

        .product-card .discount-note {
            color: #dc2626;
            font-size: 0.8rem;
            display: block;
            margin-top: 0.25rem;
        }

        .product-card .cart-actions {
            padding: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 0.5rem;
            align-items: center;
            justify-content: space-between;
        }

        .product-card input[type="number"] {
            width: 60px;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .product-card button {
            background-color: #0055a6;
            color: #fff;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .product-card button:hover {
            background-color: #003e7a;
        }

        .cart-bump {
            animation: bump 0.3s ease;
        }

        @keyframes bump {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="hero">
            <h1>Shop All Products</h1>
            <p>Discover our full range of products with exclusive deals!</p>
        </section>

        <section class="promo-banner">
            <h2>First Order Discount!</h2>
            <p>Use code <strong><?php echo htmlspecialchars($discount_code); ?></strong> for 20% off your first order!</p>
        </section>

        <form method="GET" class="search-filter">
            <input type="text" name="search" id="search-input" placeholder="Search products" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <select name="category" id="category-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
        </form>

        <section class="products">
            <h2>All Products</h2>
            <div class="products-grid" id="products-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <a href="product_details.php?id=<?php echo $p['product_id']; ?>" class="card-link">
                            <img src="../assets/images/products/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?> product image" />
                            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                            <p><?php echo htmlspecialchars($p['category_name']); ?></p>
                            <?php
                            $base_price = $p['deal_price'] !== null ? $p['deal_price'] : $p['price'];
                            $discounted_price = $is_first_order ? $base_price * (1 - $discount_percentage) : $base_price;
                            ?>
                            <p class="price">
                                <?php if ($is_first_order && $base_price != $discounted_price): ?>
                                    <span class="original-price">$<?php echo number_format($base_price, 2); ?></span>
                                    $<?php echo number_format($discounted_price, 2); ?>
                                    <span class="discount-note">(20% off with <?php echo htmlspecialchars($discount_code); ?>)</span>
                                <?php else: ?>
                                    $<?php echo number_format($base_price, 2); ?>
                                <?php endif; ?>
                            </p>
                        </a>

                        <div class="cart-actions">
                            <input type="number" value="1" min="1" max="<?php echo $p['stock']; ?>" class="qty" />
                            <button class="add-to-cart" data-id="<?php echo $p['product_id']; ?>">Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script>
        let searchTimeout;

        document.getElementById('search-input').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 300);
        });

        document.getElementById('category-select').addEventListener('change', performSearch);

        function performSearch() {
            const searchTerm = document.getElementById('search-input').value;
            const category = document.getElementById('category-select').value;

            const params = new URLSearchParams();
            params.append('ajax_search', '1');
            if (searchTerm) params.append('search', searchTerm);
            if (category) params.append('category', category);

            fetch('shop.php?' + params.toString())
                .then(res => res.json())
                .then(data => {
                    document.getElementById('products-grid').innerHTML = data.html;
                    attachCartListeners(); // Re-attach events
                });
        }

        function attachCartListeners() {
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.id;
                    const quantityInput = this.closest('.cart-actions').querySelector('.qty');
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

                    const formData = new FormData();
                    formData.append('product_id', productId);
                    formData.append('quantity', quantity);

                    fetch('../pages/add_to_cart.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                const countEl = document.getElementById('cart-count');
                                if (countEl) {
                                    countEl.textContent = data.cart_count;
                                    countEl.classList.add('cart-bump');
                                    setTimeout(() => countEl.classList.remove('cart-bump'), 300);
                                }
                            } else if (data.error === 'Not logged in') {
                                alert("Please login or register to add items to your cart.");
                                window.location.href = "../pages/login.php";
                            } else {
                                alert(data.error || "Could not add to cart.");
                            }
                        })
                        .catch(() => {
                            alert('Something went wrong.');
                        });
                });
            });
        }

        attachCartListeners();
    </script>
</body>

</html>