<?php
session_start();
require_once 'common.php'; // Assumes this includes the $mysqli connection
$search_query = ''; // Initialize the variable to an empty string

// Check if the query parameter has been passed in the URL
if (isset($_GET['query'])) {
    $search_query = $_GET['query'];
}
// ... rest of the page to display results ...
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Results - <?= htmlspecialchars($search_query) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
body {
  font-family: "Georgia", serif;
  background: #f5f1e8;
  padding: 2rem;
  color: #1f2d24;
}

.container {
  max-width: 1100px;
  margin: 0 auto;
}

h1 {
  margin-bottom: 1.5rem;
  font-size: 2rem;
}

.results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
  gap: 1.7rem;
}

.card {
  background: #fffdf7;
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 4px 14px rgba(0,0,0,0.15);
  border: 1px solid #d4c49c;
  transition: .3s ease;
  text-align: center;
}

.card:hover {
  transform: translateY(-6px);
}

.card img {
  width: 100%;
  height: 220px;
  object-fit: contain;
  margin-bottom: 1rem;
  background: #eee2c8;
  border-radius: 10px;
}

button {
  background: #1f2d24;
  color: #e8e1d2;
  padding: .7rem 1.2rem;
  border:none;
  border-radius: 10px;
  cursor:pointer;
  margin-top:.5rem;
  font-weight:600;
}
button:hover {
  opacity: .85;
}
</style>
</head>

<body>

<!-- 🚨 VULNERABILITY 2: REFLECTED XSS -->
<!-- $search_query is echoed directly without htmlspecialchars, allowing scripts. -->
<div class="vulnerable-echo">
  <?php
    if ($search_query !== '') {
        echo $search_query;
    }
  ?>
</div>

<div class="container">
  <h1>Search results for: <strong><?= htmlspecialchars($search_query) ?></strong></h1> 

  <?php if (!is_null($result) && $result->num_rows == 0): ?>
    <p>No matching products were found. Try a different keyword.</p>
  <?php elseif (!is_null($result)): ?>
    <div class="results-grid">
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="card">
          <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="">
          <h2><?= htmlspecialchars($row['name']) ?></h2>
          <p><strong>₹<?= number_format($row['price']) ?></strong></p>

          <!-- ✅ Updated link: Now goes to product.php -->
          <a href="product.php?id=<?= $row['id'] ?>">
            <button><i class="fa-solid fa-eye"></i> View</button>
          </a>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>