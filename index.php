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
    $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalProducts = $conn->query(
    "SELECT COUNT(*) FROM products"
)->fetchColumn();

$totalStock = $conn->query(
    "SELECT COALESCE(SUM(quantity), 0) FROM products"
)->fetchColumn();

$totalValue = $conn->query(
    "SELECT COALESCE(SUM(quantity * price), 0) FROM products"
)->fetchColumn();

$lowStock = $conn->query(
    "SELECT COUNT(*) FROM products WHERE quantity <= 5"
)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory Management System</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="sidebar">
    <h2><b>IMS</b></h2>
    <p class="menu-title">MAIN MENU</p>

    <a href="index.php" class="active">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="add_product.php">Add Product</a>

    <div class="sidebar-bottom">
        Inventory Management
        <small>Version 1.0</small>
    </div>
</div>

<div class="main">

    <header>
        <div>
            <h1>Dashboard</h1>
            <p>Welcome to your inventory management system.</p>
        </div>

        <a href="add_product.php" class="add-btn">
            + Add Product
        </a>
    </header>

    <div class="cards">

        <div class="card">
            <p>Total Products</p>
            <h2><?= e($totalProducts) ?></h2>
        </div>

        <div class="card">
            <p>Total Stock</p>
            <h2><?= e($totalStock) ?></h2>
        </div>

        <div class="card">
            <p>Inventory Value</p>
            <h2>₦<?= number_format((float)$totalValue, 2) ?></h2>
        </div>

        <div class="card">
            <p>Low Stock Items</p>
            <h2><?= e($lowStock) ?></h2>
        </div>

    </div>

    <section id="products">

        <div class="section-header">
            <h2>Product Inventory</h2>

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

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Value</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($products) > 0): ?>

                    <?php foreach ($products as $product): ?>

                    <tr>
                        <td><?= e($product['id']) ?></td>

                        <td><?= e($product['product_name']) ?></td>

                        <td><?= e($product['category']) ?></td>

                        <td><?= e($product['quantity']) ?></td>

                        <td>₦<?= number_format((float)$product['price'], 2) ?></td>

                        <td>₦<?= number_format(
                            $product['quantity'] * $product['price'], 2
                        ) ?></td>

                        <td>
                            <?php if ($product['quantity'] <= 5): ?>
                                <span class="low">Low Stock</span>
                            <?php else: ?>
                                <span class="available">Available</span>
                            <?php endif; ?>
                        </td>

                        <td class="actions">
                            <a href="edit_product.php?id=<?= e($product['id']) ?>">
                                Edit
                            </a>

                            <form action="delete_product.php" method="POST"
                                  onsubmit="return confirmDelete()">
                                <input type="hidden" name="id"
                                       value="<?= e($product['id']) ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8">No products found.</td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </section>

</div>

<script src="js/script.js"></script>

</body>
</html>