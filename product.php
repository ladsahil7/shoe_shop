<?php
session_start();
require_once 'common.php';

$is_logged_in = isset($_SESSION['user_id']);
$cart_success = false;

// Handle Add to Cart
// Old (SECURE) Logic:
// The product price is fetched securely from the database based on the ID.
// $product = $stmt->get_result()->fetch_assoc();

// New (VULNERABLE) Logic:
// We skip the database fetch and trust a hidden input field for the price.

// ==============================================================================
// VULNERABLE CODE MODIFICATION START
// ==============================================================================

// Modify the check: We no longer need the database fetch to find the product price.
// Instead, we trust two NEW POST parameters: 'product_name' and 'product_price'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $size = trim($_POST['size'] ?? '');

    // *** VULNERABILITY INTRODUCED HERE ***
    // Accept the price and name directly from the user's POST submission
    $submitted_price = floatval($_POST['product_price'] ?? 0.00); // Danger!
    $submitted_name = trim($_POST['product_name'] ?? 'Unknown Product'); // Danger!

    // We still do a minimal database check just to ensure the ID is valid (optional, but shows the flaw better)
    $stmt = $mysqli->prepare("SELECT id, image FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product_check = $stmt->get_result()->fetch_assoc();

    if ($product_check) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        $cart_key = $product_id . '_' . $size;
        
        // Define the product details using the *UNTRUSTED* user input
        $product_details = [
            'id' => $product_id,
            'name' => $submitted_name, // Untrusted input
            'price' => $submitted_price, // **THE CRITICAL FLAW: Untrusted price**
            'image' => $product_check['image'],
            'quantity' => $quantity,
            'size' => $size
        ];
        
        // Redo the cart update logic using the product_details array...
        if (isset($_SESSION['cart'][$cart_key])) {
             // ... logic gets complicated for update, let's simplify the flaw by always setting price
             // In a real flaw, an UPDATE would also have to be vulnerable.
             $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
             // VULNERABILITY (in update): Price is NOT updated, but if it were, it'd still be manipulated.
        } else {
             $_SESSION['cart'][$cart_key] = $product_details; // New item uses manipulated price
        }
    
        $cart_success = true;
    }
}


// ==============================================================================
// VULNERABLE CODE MODIFICATION END
// You must also remove the old database fetch, if present, or it will override.
// You must also add the price and name hidden fields to the HTML form.

// Restore original product fetch for displaying the page (it's safe)
// $product is used outside the POST block for the display price.
// ==============================================================================

// Get product details
$id = $_GET['id'] ?? 0;
$stmt = $mysqli->prepare("SELECT id, name, price, description, image FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<h2 style='color:white;text-align:center;'>Product not found.</h2>";
    exit;
}

// Split multiple images (comma-separated)
$images = array_map('trim', explode(',', $product['image']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($product['name']) ?> - ShoeShop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
/* GLOBAL */
body {
  font-family: "Georgia", serif;
  background: #f5f1e8;        /* warm cream */
  color: #1f2d24;             /* deep green */
  margin: 0;
  padding: 0;
}

/* HEADER */
header {
  background: #1f2d24;
  color: #d4c49c;
  padding: 1rem 2rem;
  text-align: center;
  font-size: 1.6rem;
  font-weight: 700;
  box-shadow: 0 3px 10px rgba(0,0,0,0.25);
  letter-spacing: 1px;
}

/* MAIN CONTAINER */
.container {
  max-width: 1150px;
  margin: 3rem auto;
  display: flex;
  flex-wrap: wrap;
  gap: 2.5rem;
  background: #fffdf7;
  border-radius: 18px;
  padding: 2.8rem;
  border: 1px solid #e7d8a6;
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  animation: fadeUp 0.6s ease;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* GALLERY */
.gallery {
  flex: 1;
  max-width: 460px;
  text-align: center;
}

.main-image {
  width: 100%;
  border-radius: 16px;
  background: #f3e9d4;
  padding: 12px;
  border: 1px solid #e7d8a6;
  object-fit: contain;
  box-shadow: 0 6px 18px rgba(0,0,0,0.18);
  transition: opacity .4s ease;
}

.thumbnails {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.thumbnails img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  background: #f3e9d4;
  padding: 6px;
  border-radius: 12px;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.25s ease;
}

.thumbnails img:hover,
.thumbnails img.active {
  border-color: #c5ae76;
  transform: scale(1.06);
}

/* DETAILS */
.details {
  flex: 1;
  min-width: 320px;
}

.details h1 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #1f2d24;
  letter-spacing: 1px;
}

.price {
  font-size: 1.7rem;
  font-weight: 800;
  margin-bottom: 1rem;
  color: #8f7f56;          /* warm gold accent */
}

.details p {
  font-size: 1rem;
  margin-bottom: 1.5rem;
  color: #4d4d4d;
  line-height: 1.7;
}

/* FORM */
form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 300px;
}

label {
  font-weight: 700;
  color: #1f2d24;
  font-size: 0.95rem;
}

select, input[type=number] {
  background: #f3e9d4;
  border: 1px solid #d4c49c;
  padding: 0.6rem;
  font-size: 1rem;
  border-radius: 8px;
  font-weight: 600;
  text-align: center;
  color: #1f2d24;
  transition: border-color 0.3s ease;
}

select:hover, input[type=number]:hover {
  border-color: #8f7f56;
}

/* BUTTON */
button {
  background: #1f2d24;
  color: #f5f1e8;
  border: 1px solid #d4c49c;
  padding: 0.8rem 1.3rem;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 700;
  display: flex;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s ease;
}

button:hover {
  background: #2e4236;
  transform: translateY(-2px);
}

/* LINKS */
.back {
  display: inline-block;
  margin-top: 1rem;
  text-decoration: none;
  font-weight: 700;
  color: #1f2d24;
  transition: 0.3s ease;
}

.back:hover {
  color: #8f7f56;
}

/* TOAST */
.toast {
  position: fixed;
  bottom: 25px;
  right: 25px;
  background: #1f2d24;
  color: #f5f1e8;
  padding: 1rem 1.5rem;
  border-radius: 10px;
  display: none;
  font-weight: 700;
  border: 1px solid #d4c49c;
  box-shadow: 0 5px 12px rgba(0,0,0,0.25);
  animation: slideUp .4s ease;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

</style>
</head>
<body>
<header>
  <i class="fa-solid fa-shoe-prints"></i> ShoeShop
</header>

<div class="container">
  <div class="gallery">
    <img id="mainImage" src="uploads/<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-image">
    <div class="thumbnails">
      <?php foreach ($images as $index => $img): ?>
        <img src="uploads/<?= htmlspecialchars($img) ?>" alt="thumb<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" onclick="changeImage(this)">
      <?php endforeach; ?>
    </div>
  </div>

  <div class="details">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <div class="price">₹<?= number_format((float)$product['price'], 2) ?></div>
    <p><?= htmlspecialchars($product['description'] ?? 'No description available.') ?></p>

    <form method="post" action="product.php?id=<?= (int)$product['id'] ?>">
  <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
  
  <!-- VULNERABILITY REQUIRED HIDDEN FIELDS -->
  <input type="hidden" name="product_price" value="<?= (float)$product['price'] ?>">
  <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
  <!-- END VULNERABILITY REQUIRED HIDDEN FIELDS -->
   
      <label for="size">Select Size</label>
      <select name="size" id="size" required>
        <option value="">Choose Size</option>
        <option value="UK 6">UK 6</option>
        <option value="UK 7">UK 7</option>
        <option value="UK 8">UK 8</option>
        <option value="UK 9">UK 9</option>
        <option value="UK 10">UK 10</option>
      </select>

      <label for="quantity">Quantity</label>
      <input type="number" name="quantity" value="1" min="1">

      <button type="submit"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
    </form>

    <a href="cart.php" class="back"><i class="fa-solid fa-cart-shopping"></i> View Cart</a><br>
    <a href="index.php" class="back"><i class="fa-solid fa-arrow-left"></i> Back to Products</a>
  </div>
</div>

<div class="toast" id="toast">
  <i class="fa-solid fa-circle-check"></i> Added to cart successfully!
</div>

<script>
function changeImage(thumbnail) {
  const main = document.getElementById('mainImage');
  const allThumbs = document.querySelectorAll('.thumbnails img');
  allThumbs.forEach(t => t.classList.remove('active'));
  thumbnail.classList.add('active');
  main.style.opacity = 0;
  setTimeout(() => {
    main.src = thumbnail.src;
    main.style.opacity = 1;
  }, 200);
}

<?php if ($cart_success): ?>
const toast = document.getElementById('toast');
toast.style.display = 'flex';
setTimeout(() => { toast.style.display = 'none'; }, 2500);
<?php endif; ?>
</script>
</body>
</html>
