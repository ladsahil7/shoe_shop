<?php
session_start();
require_once 'common.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = (int)$_GET['id'];

// Handle delete single image
if (isset($_GET['delete_image'])) {
    $img_id = (int)$_GET['delete_image'];
    $stmt = $mysqli->prepare("SELECT image FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->bind_param("ii", $img_id, $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        @unlink(__DIR__ . '/uploads/' . $res['image']);
        $del = $mysqli->prepare("DELETE FROM product_images WHERE id = ?");
        $del->bind_param("i", $img_id);
        $del->execute();
        $del->close();
    }
    header("Location: admin_edit_product.php?id=$id");
    exit;
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = (float)($_POST['price'] ?? 0);

    if ($name === '' || $brand === '' || $price <= 0) {
        $error = "Please provide valid name, brand, and price.";
    } else {
        // Update main product info
        $stmt = $mysqli->prepare("UPDATE products SET name=?, brand=?, price=? WHERE id=?");
        $stmt->bind_param("ssdi", $name, $brand, $price, $id);
        $stmt->execute();
        $stmt->close();

        // Handle multiple new images
        if (!empty($_FILES['images']['name'][0])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            foreach ($_FILES['images']['name'] as $index => $imgName) {
                $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) continue;

                $newName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $imgName);
                $target = __DIR__ . '/uploads/' . $newName;
                if (move_uploaded_file($_FILES['images']['tmp_name'][$index], $target)) {
                    $stmt = $mysqli->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
                    $stmt->bind_param("is", $id, $newName);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        header("Location: admin_dashboard.php");
        exit;
    }
}

// Load product info
$stmt = $mysqli->prepare("SELECT id, name, brand, price FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch product images
$stmt = $mysqli->prepare("SELECT id, image FROM product_images WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Product</title>
<style>
body {
  background: #111;
  color: #fff;
  font-family: Poppins, sans-serif;
  padding: 2rem;
}
.card {
  background: #0f0f0f;
  padding: 1.5rem;
  border-radius: 10px;
  max-width: 700px;
  margin: auto;
  box-shadow: 0 0 15px rgba(255,106,0,0.2);
}
label { display: block; margin-top: 1rem; font-weight: 600; }
input[type=text], input[type=number], input[type=file] {
  width: 100%;
  padding: 0.7rem;
  border-radius: 6px;
  border: 1px solid #333;
  background: #111;
  color: #fff;
  margin-top: 0.3rem;
}
.btn {
  background: linear-gradient(45deg, #ff6a00, #d56001);
  color: #fff;
  padding: 0.7rem 1.3rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  margin-top: 1.2rem;
  font-weight: 600;
}
.btn:hover { transform: scale(1.05); }
.cancel {
  background: #555;
  margin-left: 10px;
}
.images {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 1rem;
}
.images div {
  position: relative;
}
.images img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #333;
}
.delete-btn {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #ff4d4d;
  border: none;
  border-radius: 50%;
  color: #fff;
  cursor: pointer;
  width: 22px;
  height: 22px;
  font-size: 14px;
}
.err { color: #ff6b6b; margin-top: 10px; }
</style>
</head>
<body>
<div class="card">
  <h2>Edit Product #<?= $product['id'] ?></h2>
  <?php if (!empty($error)): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

    <label>Brand</label>
    <input type="text" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" required>

    <label>Price (₹)</label>
    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>

    <label>Upload New Images (You can select multiple)</label>
    <input type="file" name="images[]" multiple accept="image/*">

    <?php if (!empty($images)): ?>
    <label>Current Images:</label>
    <div class="images">
      <?php foreach ($images as $img): ?>
        <div>
          <img src="uploads/<?= htmlspecialchars($img['image']) ?>" alt="">
          <a href="?id=<?= $id ?>&delete_image=<?= $img['id'] ?>" class="delete-btn" title="Delete">&times;</a>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <button class="btn" type="submit">Save</button>
    <a class="btn cancel" href="admin_dashboard.php">Cancel</a>
  </form>
</div>
</body>
</html>
