<?php
session_start();
require_once 'common.php';

// Prevent browser caching after logout
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// ====== Handle Logout Request ======
if (isset($_GET['logout'])) {
    // Destroy all session data
    $_SESSION = [];
    session_destroy();
    // Redirect to home page
    header("Location: index.php");
    exit;
}

// Secure session check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['delete_product'];
    $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit;
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['delete_user'];
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit;
}

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($username !== '' && $email !== '' && $password !== '') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Store plain text password as well
        $stmt = $mysqli->prepare("
            INSERT INTO users (username, email, password, password_plain, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $password);
        $stmt->execute();
        $stmt->close();

        header("Location: admin_dashboard.php");
        exit;
    }
}


// Fetch all products
$products = $mysqli->query("SELECT id, name, brand, price, image FROM products ORDER BY id DESC");

// Fetch all users
$users = $mysqli->query("SELECT id, username, email, password_plain, created_at FROM users ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | ShoeShop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
body {
  background: #f5f1e8;
  color: #2a2a2a;
  font-family: "Georgia", serif;
  min-height: 100vh;
  margin: 0;
  padding: 0;
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
  padding: 2rem;
  max-width: 1100px;
  margin: auto;
}

/* Section Titles */
h2 {
  color: #1f2d24;
  font-size: 1.8rem;
  margin-bottom: 1.2rem;
  border-left: 6px solid #d4c49c;
  padding-left: 10px;
}

/* Section Wrapper */
.section {
  background: #fffdf7;
  padding: 1.5rem;
  border-radius: 15px;
  margin-bottom: 2rem;
  border: 1px solid #e7d8a6;
  box-shadow: 0 6px 16px rgba(0,0,0,0.1);
}

/* Buttons */
.add-btn {
  background: #1f2d24;
  color: #e7d8a6;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  display: inline-block;
  margin-bottom: 1rem;
  text-decoration: none;
  transition: 0.3s ease;
}
.add-btn:hover {
  background: #32493c;
  transform: translateY(-3px);
}

.action-btn {
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  cursor: pointer;
  color: #fff;
  font-weight: bold;
  transition: 0.3s ease;
}

.edit-btn {
  background: #2a9d8f;
}
.edit-btn:hover {
  background: #3fb9a9;
}

.delete-btn {
  background: #e63946;
}
.delete-btn:hover {
  background: #ff4b5c;
}

/* Tables */
table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1.5rem;
  background: #fffdf7;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

table th {
  background: #1f2d24;
  color: #e7d8a6;
  padding: 0.8rem;
  font-weight: 600;
}

table td {
  padding: 0.8rem;
  border-bottom: 1px solid #e7d8a6;
  text-align: center;
}

table tr:hover {
  background: #f3e9d4;
}

.img-thumb {
  border-radius: 8px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.2);
}

/* Add User Form */
.add-user-form {
  background: #f3e9d4;
  padding: 1rem;
  border-radius: 10px;
  border: 1px solid #d4c49c;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.add-user-form input {
  width: 97.5%;
  padding: 0.7rem;
  margin: 0.4rem 0;
  border: 1px solid #d4c49c;
  border-radius: 6px;
  background: #fffdf7;
}

.add-user-form button {
  background: #1f2d24;
  color: #e7d8a6;
  padding: 0.6rem 1rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: 0.3s ease;
}
.add-user-form button:hover {
  background: #32493c;
  transform: translateY(-2px);
}

</style>
<script>
function confirmDelete(type, id) {
  if (confirm("Are you sure you want to delete this " + type + "?")) {
    document.getElementById(type + "_form_" + id).submit();
  }
}
</script>
</head>
<body>
<header>
  <h1><i class="fa-solid fa-shield-halved"></i> Admin Panel</h1>
  <a href="admin_dashboard.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</header>

<div class="container">

  <!-- Product Management Section -->
  <div class="section">
    <h2>Product Management</h2>
    <a class="add-btn" href="add_product.php"><i class="fa-solid fa-plus"></i> Add Product</a>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Brand</th><th>Price (₹)</th><th>Image</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($p = $products->fetch_assoc()): ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['brand']) ?></td>
            <td><?= number_format($p['price'], 2) ?></td>
            <td><img class="img-thumb" src="uploads/<?= htmlspecialchars($p['image']) ?>" width="60" alt=""></td>
            <td>
              <a class="action-btn edit-btn" href="edit_product.php?id=<?= $p['id'] ?>"><i class="fa-solid fa-pen"></i> Edit</a>
              <form id="product_form_<?= $p['id'] ?>" method="POST" style="display:inline;">
                <input type="hidden" name="delete_product" value="<?= $p['id'] ?>">
                <button type="button" class="action-btn delete-btn" onclick="confirmDelete('product', <?= $p['id'] ?>)">
                  <i class="fa-solid fa-trash"></i> Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- User Accounts Section -->
  <div class="section">
    <h2>User Accounts</h2>

    <!-- Add User Form -->
    <div class="add-user-form">
      <form method="POST">
        <input type="hidden" name="add_user" value="1">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit"><i class="fa-solid fa-user-plus"></i> Add User</button>
      </form>
    </div>

    <table>
      <thead>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Password</th><th>Registered On</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['password_plain']) ?></td>
            <td><?= htmlspecialchars($u['created_at']) ?></td>

            <td>
              <a class="action-btn edit-btn" href="edit_user.php?id=<?= $u['id'] ?>"><i class="fa-solid fa-pen"></i> Edit</a>
              <form id="user_form_<?= $u['id'] ?>" method="POST" style="display:inline;">
                <input type="hidden" name="delete_user" value="<?= $u['id'] ?>">
                <button type="button" class="action-btn delete-btn" onclick="confirmDelete('user', <?= $u['id'] ?>)">
                  <i class="fa-solid fa-user-slash"></i> Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
