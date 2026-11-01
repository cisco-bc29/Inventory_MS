
<?php
require 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid product ID.");
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = filter_var($_POST['quantity'] ?? '', FILTER_VALIDATE_INT);
    $price = $_POST['price'] ?? '';

    $imageName = $product['image'];

    if (
        $name === '' ||
        $category === '' ||
        $quantity === false ||
        $quantity < 0 ||
        !is_numeric($price) ||
        $price < 0
    ) {

        $message = "Please enter valid product information.";

    } else {

        // CHECK IF A NEW IMAGE WAS UPLOADED

        if (
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

                    $newImage = bin2hex(random_bytes(16))
                        . '.' . $allowed[$mime];

                    $uploadPath = __DIR__ . '/uploads/';

                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    if (move_uploaded_file(
                        $file['tmp_name'],
                        $uploadPath . $newImage
                    )) {

                        // DELETE OLD IMAGE AFTER NEW UPLOAD SUCCEEDS

                        if (!empty($product['image'])) {
                            $oldImage = basename($product['image']);
                            $oldPath = $uploadPath . $oldImage;

                            if (is_file($oldPath)) {
                                unlink($oldPath);
                            }
                        }

                        $imageName = $newImage;

                    } else {

                        $message = "Could not save the new image.";

                    }
                }
            }
        }

        // UPDATE PRODUCT INFORMATION

        if ($message === "") {

            $stmt = $conn->prepare(
                "UPDATE products
                 SET product_name = ?,
                     category = ?,
                     quantity = ?,
                     price = ?,
                     image = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $name,
                $category,
                $quantity,
                $price,
                $imageName,
                $id
            ]);

            header("Location: index.php");
            exit;
        }
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Product</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="form-container product-form">

    <h1>Edit Product</h1>

    <p>Update product information and picture.</p>

    <?php if ($message): ?>
        <p class="error"><?= e($message) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Product Name</label>

        <input type="text"
               name="product_name"
               value="<?= e($product['product_name']) ?>"
               required>


        <label>Category</label>

        <input type="text"
               name="category"
               value="<?= e($product['category']) ?>"
               required>


        <label>Quantity</label>

        <input type="number"
               name="quantity"
               value="<?= e($product['quantity']) ?>"
               min="0"
               required>


        <label>Price (₦)</label>

        <input type="number"
               name="price"
               value="<?= e($product['price']) ?>"
               min="0"
               step="0.01"
               required>


        <label>Current Product Picture</label>

        <div class="image-preview">

            <?php
            $currentImage = basename($product['image'] ?? '');
            $currentPath = __DIR__ . '/uploads/' . $currentImage;
            ?>

            <?php if (
                $currentImage !== '' &&
                is_file($currentPath)
            ): ?>

                <img
                    id="preview"
                    src="uploads/<?= rawurlencode($currentImage) ?>"
                    alt="Current product picture"
                >

            <?php else: ?>

                <img
                    id="preview"
                    src=""
                    alt="Product preview"
                    style="display:none;"
                >

                <p id="preview-text">No product picture available.</p>

            <?php endif; ?>

        </div>


        <label>Upload New Picture</label>

        <input type="file"
               name="image"
               id="image"
               accept="image/jpeg,image/png,image/webp">

        <small>Leave empty to keep the current picture. Maximum 2MB.</small>


        <button type="submit" class="add-btn">
            Update Product
        </button>

        <a href="index.php" class="back-btn">
            Back to Dashboard
        </a>

    </form>

</div>

<script src="js/script.js"></script>

</body>
</html>
