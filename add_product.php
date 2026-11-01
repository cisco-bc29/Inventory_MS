
<?php
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = filter_var($_POST['quantity'] ?? '', FILTER_VALIDATE_INT);
    $price = $_POST['price'] ?? '';

    $imageName = null;

    if (
        $name === '' ||
        $category === '' ||
        $quantity === false ||
        $quantity < 0 ||
        !is_numeric($price) ||
        $price < 0
    ) {
        $message = "Please enter valid product information.";

    } elseif (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $message = "Image upload failed.";

        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $message = "Image must not exceed 2MB.";

        } else {

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            if (!isset($allowed[$mime])) {
                $message = "Only JPG, PNG and WEBP images are allowed.";

            } else {

                $imageName = bin2hex(random_bytes(16))
                    . '.' . $allowed[$mime];

                $uploadPath = __DIR__ . '/uploads/';

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                if (!move_uploaded_file(
                    $file['tmp_name'],
                    $uploadPath . $imageName
                )) {
                    $message = "Could not save the product image.";
                    $imageName = null;
                }
            }
        }
    }

    if ($message === "") {

        $stmt = $conn->prepare(
            "INSERT INTO products
            (product_name, category, quantity, price, image)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $category,
            $quantity,
            $price,
            $imageName
        ]);

        header("Location: products.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add New Product</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="form-container product-form">

    <h1>Add New Product</h1>
    <p>Enter product details and upload a product picture.</p>

    <?php if ($message): ?>
        <p class="error">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Product Name</label>
        <input type="text"
               name="product_name"
               placeholder="Enter product name"
               required>

        <label>Category</label>
        <input type="text"
               name="category"
               placeholder="e.g Electronics"
               required>

        <label>Quantity</label>
        <input type="number"
               name="quantity"
               min="0"
               placeholder="Enter quantity"
               required>

        <label>Price (₦)</label>
        <input type="number"
               name="price"
               min="0"
               step="0.01"
               placeholder="Enter price"
               required>

        <label>Product Picture</label>

        <input type="file"
               name="image"
               id="image"
               accept="image/jpeg,image/png,image/webp">

        <div class="image-preview">
            <img id="preview" src="" alt="Product preview"
                 style="display:none;">
            <p id="preview-text">Product image preview</p>
        </div>

        <button type="submit" class="add-btn">
            Save Product
        </button>

        <a href="products.php" class="back-btn">
            Back to Products
        </a>

    </form>

</div>

<script src="js/script.js"></script>

</body>
</html>
