<?php
session_start();
require_once 'common.php';

// Secure session check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Handle new product submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $image = '';

    // Upload image
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $fileName;
        }
    }

    // Insert into DB
    $stmt = $mysqli->prepare("INSERT INTO products (name, brand, price, description, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $name, $brand, $price, $description, $image);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product | ShoeShop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
body {
  background: #f5f1e8;
  color: #2a2a2a;
  font-family: "Georgia", serif;
  min-height: 100vh;
  margin: 0;
}

/* Header */
header {
  background: #1f2d24;
  color: #d4c49c;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #e7d8a6;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
header a {
  color: #1f2d24;
  text-decoration: none;
  background: #e7d8a6;
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
  font-weight: bold;
  transition: 0.3s ease;
}
header a:hover {
  background: #f5f1e8;
  transform: translateY(-3px);
}

/* Container */
.container {
  max-width: 600px;
  margin: 3rem auto;
  background: #fffdf7;
  padding: 2rem;
  border-radius: 15px;
  border: 1px solid #e7d8a6;
  box-shadow: 0 6px 16px rgba(0,0,0,0.1);
}

h2 {
  color: #1f2d24;
  font-size: 1.8rem;
  text-align: center;
  margin-bottom: 1.5rem;
  border-left: 6px solid #d4c49c;
  padding-left: 10px;
}

/* Form */
label {
  font-weight: bold;
  color: #1f2d24;
  margin-top: 1rem;
  display: block;
}
input, textarea {
  width: 100%;
  padding: 0.7rem;
  margin-top: 0.4rem;
  border-radius: 8px;
  border: 1px solid #d4c49c;
  background: #fffdf7;
  font-size: 1rem;
}

textarea {
  min-height: 120px;
}

/* Buttons */
button {
  background: #1f2d24;
  color: #e7d8a6;
  padding: 0.8rem;
  width: 100%;
  border: none;
  margin-top: 1.4rem;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1.1rem;
  transition: 0.3s ease;
}
button:hover {
  background: #32493c;
  transform: translateY(-3px);
}

/* Back link */
.back-link {
  display: inline-block;
  margin-top: 1rem;
  color: #1f2d24;
  text-decoration: none;
  font-weight: bold;
}
.back-link:hover {
  text-decoration: underline;
}
</style>
</head>

<body>

<header>
  <h1><i class="fa-solid fa-plus"></i> Add Product</h1>
  <a href="admin_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back</a>
</header>

<div class="container">
  <h2>Add New Product</h2>

  <form method="POST" enctype="multipart/form-data">

    <label>Product Name</label>
    <input type="text" name="name" required>

    <label>Brand</label>
    <input type="text" name="brand" required>

    <label>Price (₹)</label>
    <input type="number" step="0.01" name="price" required>

    <label>Description</label>
    <textarea name="description"></textarea>

    <label>Product Image</label>
    <input type="file" name="image" accept="image/*" required>

    <button type="submit">
      <i class="fa-solid fa-circle-plus"></i> Add Product
    </button>

  </form>

</div>

</body>
</html>
