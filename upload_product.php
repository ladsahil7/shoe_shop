<?php
require_once 'common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $imageName = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = uniqid('shoe_', true) . '.' . $fileExt;
            $uploadPath = __DIR__ . '/uploads/' . $newFileName;
            move_uploaded_file($fileTmp, $uploadPath);
            $imageName = $newFileName;
        }
    }

    if ($name && $price && $imageName) {
        $stmt = $mysqli->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $name, $price, $imageName);
        $stmt->execute();
        echo "<p style='color:green;'>Product added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Please fill all fields and upload a valid image.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload Product</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
body {
  font-family: "Poppins", sans-serif;
  background: #f4f4f4;
  padding: 2rem;
}
form {
  background: #fff;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  width: 400px;
  margin: 0 auto;
}
input, button {
  width: 100%;
  padding: 0.8rem;
  margin: 0.5rem 0;
  border: 1px solid #ccc;
  border-radius: 6px;
}
button {
  background: #111;
  color: #fff;
  cursor: pointer;
  transition: background 0.3s;
}
button:hover {
  background: #d56001;
}
h2 {
  text-align: center;
  color: #333;
}
</style>
</head>
<body>

<h2><i class="fa-solid fa-upload"></i> Add New Product</h2>
<form method="post" enctype="multipart/form-data">
  <label>Product Name:</label>
  <input type="text" name="name" required>

  <label>Price (₹):</label>
  <input type="number" name="price" step="0.01" required>

  <label>Image:</label>
  <input type="file" name="image" accept="image/*" required>

  <button type="submit"><i class="fa-solid fa-plus"></i> Add Product</button>
</form>

</body>
</html>
