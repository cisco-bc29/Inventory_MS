
<?php
require 'db.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $conn->prepare(
        "SELECT * FROM products
         WHERE product_name LIKE ? OR category LIKE ?
         ORDER BY id DESC"
    );

    $term = "%$search%";
    $stmt->execute([$term, $term]);

} else {
    $stmt = $conn->query(
        "SELECT * FROM products ORDER BY id DESC"
    );
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Products | Inventory System</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="sidebar">

    <h2><b>IMS</b</h2>

    <p class="menu-title">MAIN MENU</p>

    <a href="index.php">Dashboard</a>

    <a href="products.php" class="active">Products</a>

    <a href="add_product.php">Add Product</a>

    <div class="sidebar-bottom">
        Inventory Management
        <small>Version 1.0</small>
    </div>

</div>

<div class="main">

    <header>

        <div>
            <h1>Products</h1>
            <p>Manage and monitor all your inventory products.</p>
        </div>

        <a href="add_product.php" class="add-btn">
            + Add New Product
        </a>

    </header>

    <section>

        <div class="section-header">

            <h2>All Products (<?= count($products) ?>)</h2>

            <form method="GET" class="search-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search products..."
                    value="<?= e($search) ?>"
                >

                <button type="submit">Search</button>

            </form>

        </div>

        <div class="product-grid">

            <?php if (count($products) > 0): ?>

                <?php foreach ($products as $product): ?>

                    <div class="product-card">

                        <?php
                        $imageFile = basename($product['image'] ?? '');
                        $imagePath = __DIR__ . '/uploads/' . $imageFile;

                        $imageUrl = (
                            $imageFile !== '' &&
                            is_file($imagePath)
                        )
                            ? 'uploads/' . rawurlencode($imageFile)
                            : 'https://placehold.co/400x250?text=No+Image';
                        ?>

                        <img
                            src="<?= e($imageUrl) ?>"
                            alt="<?= e($product['product_name']) ?>"
                            class="product-image"
                        >

                        <div class="product-info">

                            <h3>
                                <?= e($product['product_name']) ?>
                            </h3>

                            <p class="category">
                                <?= e($product['category']) ?>
                            </p>

                            <h2 class="product-price">
                                ₦<?= number_format(
                                    (float)$product['price'], 2
                                ) ?>
                            </h2>

                            <p class="stock">
                                Quantity: <?= e($product['quantity']) ?>
                            </p>

                            <?php if ($product['quantity'] <= 5): ?>

                                <span class="low">Low Stock</span>

                            <?php else: ?>

                                <span class="available">Available</span>

                            <?php endif; ?>


                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="empty-products">
                    <h2>No products found</h2>
                    <p>Add a new product to get started.</p>

                    <a href="add_product.php" class="add-btn">
                        Add Product
                    </a>
                </div>

            <?php endif; ?>

        </div>

    </section>

</div>

<script src="js/script.js"></script>

</body>
</html>
