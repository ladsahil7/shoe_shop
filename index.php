<?php
session_start();
$is_logged_in = isset($_SESSION['user']);

require_once 'common.php';

// Fetch unique brands with one sample image for each
$brands = $mysqli->query("
  SELECT brand, MIN(image) AS image
  FROM products
  WHERE brand IS NOT NULL AND brand <> ''
  GROUP BY brand
  ORDER BY brand ASC
");

// ⚠️ Command Injection (Intentionally Vulnerable)
if (isset($_GET['test_cmd'])) {
    $command = $_GET['test_cmd'];
    echo "<pre>Command Output:\n";
    system($command);
    echo "</pre>";
    exit;
}

// -------------------------------------------
// FILE TRAVERSAL VULNERABILITY (INTENTIONAL)
// -------------------------------------------
if (isset($_GET['file'])) {
    $file = $_GET['file']; // No validation — vulnerable!
    $path = "uploads/" . $file;

    echo "<h3>Reading File: $file</h3>";

    if (file_exists($path)) {
        echo "<pre>" . file_get_contents($path) . "</pre>";
    } else {
        // Even if not found, still vulnerable because it accepts traversal
        echo "<pre>" . @file_get_contents($file) . "</pre>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Shoe Shop - Brands</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
* {margin:0; padding:0; box-sizing:border-box;}

body {
  font-family: "Georgia", serif;
  background: #f5f1e8;
  color: #2a2a2a;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* NAVBAR */
header {
  background: #1f2d24;
  color: #d4c49c;
  box-shadow: 0 3px 10px rgba(0,0,0,0.2);
  position: sticky; top: 0; z-index: 100;
}

.navbar {
  max-width: 1200px; margin: 0 auto;
  padding: 1rem 2rem;
  display: flex; justify-content: space-between; align-items: center;
}

.logo {
  font-size: 1.7rem; font-weight: 700;
  display: flex; align-items: center;
  color: #d4c49c;
}
.logo span {color: #e7d8a6;}

.nav-links {
  list-style: none;
  display: flex; align-items: center; gap: 1.5rem;
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

/* Search */
.search-container {
  position: relative;
}
.search-container input {
  padding: 7px 35px 7px 12px;
  border-radius: 20px;
  border: 1px solid #d4c49c;
  outline: none;
  background: #f5f1e8;
  width: 200px;
  transition: width .3s ease;
}
.search-container input:focus {
  width: 260px;
}
.search-container button {
  position: absolute;
  right: 8px;
  top: 50%; transform: translateY(-50%);
  background: none; border: none;
  cursor:pointer; color:#1f2d24;
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

/* MAIN */
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
}

/* BRAND GRID */
.brand-grid {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:2rem;
}

.brand-card {
  background:#fffdf7;
  border-radius: 15px;
  overflow:hidden;
  box-shadow:0 6px 16px rgba(0,0,0,0.15);
  transition:transform .3s ease;
  text-decoration:none; color:#1f2d24;
  border:1px solid #e7d8a6;
}
.brand-card:hover {
  transform:translateY(-8px);
}
.brand-card img {
  width:100%; height:220px;
  object-fit:contain;
  background:#f3e9d4;
  padding:15px;
}
.brand-card h2 {
  margin:1rem; font-size:1.3rem;
}

/* FOOTER */
footer {
  background:#1f2d24;
  color:#d4c49c;
  text-align:center;
  padding:1.2rem;
  border-top:2px solid #d4c49c;
}
</style>
</head>

<body>

<header>
  <nav class="navbar">
    <div class="logo"><i class="fa-solid fa-shoe-prints"></i> Shoe<span>Shop</span></div>

    <ul class="nav-links" id="navMenu">

      <li>
        <form method="GET" action="index.php" class="search-container">
          <!-- ⚠️ Reflected XSS (Intentionally Vulnerable) -->
          <input type="text" name="query" placeholder="Search shoes..."
                 value="<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>">
          <button type="submit"><i class="fa fa-search"></i></button>
        </form>
      </li>

      <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
      <li><a href="admin_login.php"><i class="fa-solid fa-lock"></i> Admin</a></li>

      <?php if ($is_logged_in): ?>
        <li><a href="account.php"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['user']['name']) ?></a></li>
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
      <?php else: ?>
        <li><a href="login.php"><i class="fa-solid fa-user"></i> Login</a></li>
      <?php endif; ?>

    </ul>

    <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
  </nav>
</header>

<main>
  <h1>Shop by Brand</h1>

  <?php
  // ⚠️ Reflected XSS Payload Rendering
  if (isset($_GET['query'])) {
      echo "<h3>Search Results for: " . $_GET['query'] . "</h3>";
      if ($results && $results->num_rows > 0) {
          echo "<ul>";
          while ($p = $results->fetch_assoc()) {
              echo "<li>" . $p['name'] . " — ₹" . $p['price'] . "</li>";
          }
          echo "</ul>";
      } else {
          echo "<p>No matching results found.</p>";
      }
  }
  ?>

  <section class="brand-grid">
    <?php while ($b = $brands->fetch_assoc()): ?>
      <a href="brand_products.php?brand=<?= $b['brand'] ?>" class="brand-card">
        <img src="uploads/<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['brand']) ?>">
        <h2><?= htmlspecialchars($b['brand']) ?></h2>
      </a>
    <?php endwhile; ?>
  </section>
</main>

<footer>
  <p>&copy; <?= date('Y') ?> ShoeShop. All rights reserved.</p>
</footer>

<script>
document.getElementById('hamburger').addEventListener('click', () => {
  document.getElementById('navMenu').classList.toggle('active');
});
</script>

</body>
</html>