<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
require_once 'common.php';

$brand = $_GET['brand'] ?? '';
if ($brand === '') {
    header("Location: index.php");
    exit;
}

$stmt = $mysqli->prepare("SELECT id, name, price, image FROM products WHERE brand = ? LIMIT 100");
$stmt->bind_param('s', $brand);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($brand) ?> - Products | Shoe Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
* {margin:0; padding:0; box-sizing:border-box;}

body {
  font-family: "Georgia", serif;
  background: #f5f1e8;               /* Same warm cream background */
  color: #1f2d24;                    /* Deep green text */
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* NAVBAR (same as index.php) */
header {
  background: #1f2d24;
  color: #d4c49c;
  box-shadow: 0 3px 10px rgba(0,0,0,0.2);
  position: sticky; top: 0; z-index: 100;
}

.navbar {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 1.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  color: #d4c49c;
}
.logo i {margin-right: 10px; color: #d4c49c;}
.logo span {color: #e7d8a6;}

.nav-links {
  list-style: none;
  display: flex; align-items: center; gap: 1.7rem;
}

.nav-links li a {
  text-decoration: none;
  color: #f5f1e8;
  transition: color .3s ease, transform .2s ease;
  font-weight: 500;
}

.nav-links li a:hover {
  color: #e7d8a6;
  transform: translateY(-2px);
}

.hamburger { display:none; flex-direction: column; cursor:pointer; }
.hamburger span {
  background: #fff; height:3px; width:25px;
  margin:4px; border-radius:2px;
}

@media (max-width:768px){
  .nav-links {
    position:absolute; top:70px; left:0; right:0;
    background:#1f2d24;
    flex-direction:column;
    padding: 1rem 0;
    display:none;
  }
  .nav-links.active { display:flex; }
  .hamburger { display:flex; }
}

/* MAIN SECTION */
main {
  flex:1;
  width:90%; max-width:1200px;
  margin:2rem auto;
  text-align:center;
}

h1 {
  color:#1f2d24;
  margin-bottom:2rem;
  font-size:2rem;
  font-weight:700;
  letter-spacing:1px;
}

/* PRODUCT GRID (same style concept as brand cards) */
.products {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:2rem;
}

.product {
  background:#fffdf7;
  border-radius: 15px;
  overflow:hidden;
  box-shadow:0 6px 16px rgba(0,0,0,0.15);
  border:1px solid #e7d8a6;
  transition:transform .3s ease, box-shadow .3s ease;
  text-decoration:none; color:#1f2d24;
}

.product:hover {
  transform:translateY(-8px);
  box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.product img {
  width:100%;
  height:240px;
  object-fit:contain;
  background:#f3e9d4;      /* same warm cream */
  padding:15px;
  border-bottom:1px solid #e7d8a6;
}

.product h2 {
  margin:1rem 1rem 0.5rem;
  color:#1f2d24;
  font-size:1.2rem;
  font-weight:700;
}

.product p {
  margin:0 1rem 1.2rem;
  font-weight:700;
  color:#8f7f56;           /* warm gold accent */
  font-size:1.1rem;
}

/* FOOTER */
footer {
  background:#1f2d24;
  color:#d4c49c;
  text-align:center;
  padding:1.2rem;
  border-top:2px solid #d4c49c;
  font-size:0.9rem;
  margin-top:auto;
}

</style>
</head>
<body>
<header>
  <nav class="navbar">
    <div class="logo"><i class="fa-solid fa-shoe-prints"></i> Shoe<span>Shop</span></div>
    <ul class="nav-links" id="navMenu">
      <li><a href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to Brands</a></li>
    </ul>
    <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
  </nav>
</header>

<main>
  <h1><?= htmlspecialchars($brand) ?> Collection</h1>

  <section class="products">
    <?php if ($res->num_rows === 0): ?>
      <p style="text-align:center; grid-column:1/-1; color:#aaa;">No products found for this brand.</p>
    <?php endif; ?>

    <?php while ($row = $res->fetch_assoc()): ?>
      <article class="product">
        <a href="product.php?id=<?= (int)$row['id'] ?>" style="text-decoration:none; color:inherit;">
          <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy">
          <h2><?= htmlspecialchars($row['name']) ?></h2>
          <p>₹<?= number_format((float)$row['price'], 2) ?></p>
        </a>
      </article>
    <?php endwhile; ?>
  </section>
</main>

<footer>
  <p>&copy; <?= date('Y') ?> ShoeShop. All rights reserved.</p>
</footer>

<script>
document.getElementById('hamburger').addEventListener('click',()=>{
  document.getElementById('navMenu').classList.toggle('active');
});
</script>
</body>
</html>
