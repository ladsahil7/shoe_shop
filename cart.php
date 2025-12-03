<?php
session_start();
require_once 'common.php';

// Remove item using raw key (supports numeric keys and composite keys like "12_UK 8")
if (isset($_GET['remove'])) {
    $raw = $_GET['remove'];
    // decode in case link was encoded
    $key = urldecode($raw);

    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }

    // Redirect to avoid resubmission and to refresh cart state
    header('Location: cart.php');
    exit;
}

// Get current cart
$cart = $_SESSION['cart'] ?? [];

// Calculate total
$total = 0.0;
foreach ($cart as $item) {
    $total += (float)$item['price'] * (int)$item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Cart - Shoe Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
body {
  font-family: "Poppins", sans-serif;
  background-color: #1e1e1e;
  color: #f5f5f5;
}
.container {
  max-width: 900px;
  margin: 3rem auto;
  background: #2b2b2b;
  padding: 2rem;
  border-radius: 12px;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 1rem;
  text-align: center;
}
th {
  background: #000;
  color: #d56001;
}
tr:nth-child(even) {
  background: #222;
}
img {
  width: 80px;
  border-radius: 6px;
}
a {
  color: #ff6a00;
  text-decoration: none;
}
.total {
  text-align: right;
  margin-top: 1.5rem;
  font-size: 1.3rem;
  font-weight: 600;
  color: #d56001;
}
button {
  background: #d56001;
  border: none;
  color: #fff;
  padding: 0.7rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
}
button:hover {
  background: #ff6a00;
}
.size { font-size: 0.9rem; opacity: 0.85; }
.remove-link { color: #ff6a00; font-weight:700; }
</style>
</head>
<body>
<div class="container">
  <h1><i class="fa-solid fa-cart-shopping"></i> Your Cart</h1>

  <?php if (empty($cart)): ?>
    <p>Your cart is empty. <a href="index.php">Continue shopping</a>.</p>

  <?php else: ?>
  <table>
    <tr>
      <th>Image</th>
      <th>Product</th>
      <th>Price</th>
      <th>Size</th>
      <th>Qty</th>
      <th>Subtotal</th>
      <th>Action</th>
    </tr>

    <?php foreach ($cart as $key => $item): ?>
    <tr>
      <td><img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt=""></td>
      <td><?= htmlspecialchars($item['name']) ?></td>
      <td>₹<?= number_format((float)$item['price'], 2) ?></td>
      <td class="size"><?= htmlspecialchars($item['size'] ?? '') ?></td>
      <td><?= (int)$item['quantity'] ?></td>
      <td>₹<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></td>
      <td>
        <!-- Encode key in link so spaces/special chars are preserved -->
        <a class="remove-link" href="?remove=<?= urlencode($key) ?>" onclick="return confirm('Remove this item from cart?');">
          <i class="fa-solid fa-trash"></i> Remove
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>

  <p class="total">Total: ₹<?= number_format($total, 2) ?></p>

  <button onclick="location.href='checkout.php'">Proceed to Checkout</button>
  <?php endif; ?>
</div>
</body>
</html>
