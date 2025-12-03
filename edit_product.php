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

// Handle update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $brand === '' || $price <= 0) {
        $error = "Please provide valid name, brand and price.";
    } else {
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "Invalid image type.";
            } else {
                $imageName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['image']['name']);
                $target = __DIR__ . '/uploads/' . $imageName;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $error = "Failed to upload image.";
                }
            }
        }

        if (!isset($error)) {
            if ($imageName) {
                $stmt = $mysqli->prepare("UPDATE products SET name=?, brand=?, price=?, description=?, image=? WHERE id=?");
                $stmt->bind_param("ssdssi", $name, $brand, $price, $description, $imageName, $id);
            } else {
                $stmt = $mysqli->prepare("UPDATE products SET name=?, brand=?, price=?, description=? WHERE id=?");
                $stmt->bind_param("ssdsi", $name, $brand, $price, $description, $id);
            }
            $stmt->execute();
            $stmt->close();
            header("Location: admin_dashboard.php");
            exit;
        }
    }
}

// Load product
$stmt = $mysqli->prepare("SELECT id, name, brand, price, image, description FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product | Admin Panel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
/* ===== GLOBAL ===== */
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  background: #f5f1e8;
  color: #2a2a2a;
  font-family: "Georgia", serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ===== CARD CONTAINER ===== */
.card {
  width: 550px;
  background: #fffdf7;
  border: 1px solid #e7d8a6;
  border-radius: 18px;
  padding: 2.2rem;
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* ===== HEADER ===== */
h2 {
  color: #1f2d24;
  font-size: 1.9rem;
  font-weight: 700;
  text-align: center;
  letter-spacing: 0.5px;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #d4c49c;
  padding-bottom: 0.7rem;
}

h2 i {
  color: #d4c49c;
}

/* ===== FORM ELEMENTS ===== */
label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 600;
  color: #1f2d24;
}

input[type="text"],
input[type="number"],
input[type="file"],
textarea {
  width: 100%;
  padding: 0.9rem 1rem;
  border: 1px solid #d4c49c;
  border-radius: 10px;
  background: #fffdf7;
  color: #1f2d24;
  margin-bottom: 1.3rem;
  font-size: 1rem;
  transition: 0.3s ease;
}

input:focus,
textarea:focus {
  outline: none;
  border-color: #1f2d24;
  box-shadow: 0 0 8px rgba(31,45,36,0.3);
}

textarea {
  min-height: 110px;
  resize: vertical;
}

/* ===== BUTTONS ===== */
.btn {
  width: 100%;
  background: #1f2d24;
  color: #e7d8a6;
  padding: 0.9rem;
  border-radius: 10px;
  border: none;
  font-weight: 700;
  cursor: pointer;
  font-size: 1.05rem;
  transition: 0.3s ease;
  margin-top: 0.3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn:hover {
  background: #2d4237;
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}

.cancel {
  width: 100%;
  display: inline-block;
  margin-top: 0.8rem;
  padding: 0.9rem;
  border-radius: 10px;
  text-align: center;
  background: #d4c49c;
  color: #1f2d24;
  text-decoration: none;
  font-weight: 700;
  transition: 0.3s ease;
}

.cancel:hover {
  background: #e7d8a6;
  transform: translateY(-2px);
}

/* ===== ERROR MESSAGE ===== */
.err {
  color: #7a0000;
  background: #f3d5d5;
  border: 1px solid #caa5a5;
  padding: 0.8rem;
  border-radius: 10px;
  text-align: center;
  margin-bottom: 1rem;
  font-weight: 600;
}

/* ===== IMAGE PREVIEW ===== */
img.preview {
  margin-top: 0.5rem;
  border-radius: 10px;
  border: 1px solid #d4c49c;
  box-shadow: 0 3px 10px rgba(0,0,0,0.15);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
  .card {
    width: 92%;
    padding: 1.6rem;
  }
  h2 {
    font-size: 1.6rem;
  }
}

</style>
</head>
<body>
  <div class="card">
    <h2><i class="fa-solid fa-pen-to-square"></i> Edit Product #<?= $product['id'] ?></h2>

    <?php if (!empty($error)): ?>
      <div class="err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <label for="name">Product Name</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

      <label for="brand">Brand</label>
      <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" required>

      <label for="price">Price (₹)</label>
      <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>

      <label for="description">Description</label>
      <textarea id="description" name="description" placeholder="Enter product details..."><?= htmlspecialchars($product['description'] ?? '') ?></textarea>

      <label for="image">Product Image</label>
      <input type="file" id="image" name="image" accept="image/*">
      <?php if ($product['image']): ?>
        <p>Current Image:</p>
        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" width="120" class="preview" alt="">
      <?php endif; ?>

      <button class="btn" type="submit"><i class="fa-solid fa-save"></i> Save Changes</button>
      <a class="cancel" href="admin_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Cancel</a>
    </form>
  </div>
</body>
</html>
