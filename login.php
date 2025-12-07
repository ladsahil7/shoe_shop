<?php
session_start();
require_once 'common.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email']; 
    $password = $_POST['password'];

    // Secure Prepared Statement — Eliminates SQL Injection Risk
    $stmt = $mysqli->prepare("SELECT id, username, password, profile_image FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['username'],
            'image' => !empty($user['profile_image']) ? $user['profile_image'] : "default.png"
        ];

        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | ShoeShop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
/* ===== GLOBAL ===== */
* {margin:0; padding:0; box-sizing:border-box;}
body {
  font-family: "Georgia", serif;
  background: linear-gradient(145deg, #f5f1e8, #e8e1d2);
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1f2d24;
}

/* ===== FORM ===== */
form {
  background: #fffdf7;
  padding: 2.6rem 2.4rem;
  border-radius: 18px;
  width: 380px;
  border: 1px solid #d4c49c;
  box-shadow: 0 6px 20px rgba(60, 60, 60, 0.2);
  text-align: center;
  animation: fadeIn 0.45s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ===== HEADER ===== */
h2 {
  font-size: 1.8rem;
  margin-bottom: 1.7rem;
  display: flex;
  justify-content: center;
  gap: 10px;
  font-weight: 700;
}

h2 i {color: #8f7f56;}

/* ===== INPUT ===== */
.input-group {
  position: relative;
  margin-bottom: 1.3rem;
  display: flex;
  align-items: center;
}
.input-group .input-icon {
  position: absolute;
  left: 14px;
  font-size: 1.15rem;
  color: #8f7f56;
}
.input-group input {
  width: 100%;
  padding: 0.9rem 1rem 0.9rem 3rem;
  background: #f5f1e8;
  border: 1px solid #d4c49c;
  border-radius: 10px;
  font-size: 1rem;
}
.toggle-pass {
  position: absolute;
  right: 14px;
  cursor: pointer;
}

/* ===== ERROR MESSAGE ===== */
.error {
  color: #721c24;
  background: #f8d7da;
  border:1px solid #f5c6cb;
  padding: 0.6rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

/* ===== BUTTON ===== */
button {
  width:100%;
  margin-top:.7rem;
  background:#1f2d24;
  padding:.9rem;
  border-radius:10px;
  color:#e8e1d2;
  cursor:pointer;
  font-size:1rem;
}

/* ===== FOOTER ===== */
footer {
  margin-top: 1.6rem;
  font-size: .95rem;
  color: #6e6e6e;
}

footer a {
  color: #1f2d24;
  text-decoration: none;
  font-weight: 600;
}

footer a:hover {
  text-decoration: underline;
}

</style>
</head>

<body>
<form method="post" action="login.php">

  <h2><i class="fa-solid fa-right-to-bracket"></i> Welcome Back</h2>

  <?php if ($error): ?>
      <div class="error"><?= $error ?></div>
  <?php endif; ?>

  <div class="input-group">
    <i class="fa-solid fa-envelope input-icon"></i>
    <input type="text" name="email" placeholder="Email" required>
  </div>

  <div class="input-group">
    <i class="fa-solid fa-lock input-icon"></i>
    <input type="password" name="password" placeholder="Password" required>
  </div>

  <button type="submit">
    <i class="fa-solid fa-right-to-bracket"></i> Login
  </button>

  <footer>
    Back to <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
  </footer>

</form>
</body>
</html>
