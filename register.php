<?php
session_start();
require_once 'common.php';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $password_plain = $password;

$stmt = $mysqli->prepare("
    INSERT INTO users (username, email, password, password_plain) 
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param('ssss', $username, $email, $hashed_password, $password_plain);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                header('Location: index.php');
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | ShoeShop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
<style>
/* ===== GLOBAL ===== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

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
  color: #1f2d24;
  margin-bottom: 1.7rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  font-weight: 700;
}

h2 i {
  color: #8f7f56;
}

/* ===== INPUTS ===== */
.input-group {
  position: relative;
  margin-bottom: 1.3rem;
  display: flex;
  align-items: center;
}

.input-group i {
  position: absolute;
  left: 14px;
  font-size: 1.15rem;
  color: #8f7f56;
  pointer-events: none;
}

.input-group input {
  width: 100%;
  padding: 0.9rem 1rem 0.9rem 3rem; /* aligned padding */
  background: #f5f1e8;
  border: 1px solid #d4c49c;
  border-radius: 10px;
  color: #1f2d24;
  font-size: 1rem;
  transition: border-color 0.3s ease, transform 0.1s ease;
}

.input-group input:focus {
  border-color: #8f7f56;
  transform: scale(1.015);
  outline: none;
}


/* ===== BUTTON ===== */
button {
  width: 100%;
  margin-top: 0.7rem;
  background: #1f2d24;
  padding: 0.9rem;
  border-radius: 10px;
  border: none;
  color: #e8e1d2;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  letter-spacing: 0.6px;
  transition: transform 0.2s ease, box-shadow 0.3s ease;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
}

button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.25);
}

/* ===== ERROR ===== */
.error {
  color: #8b1a1a;
  background: rgba(139, 26, 26, 0.12);
  padding: 0.6rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.92rem;
  border: 1px solid rgba(139, 26, 26, 0.3);
}

/* ===== FOOTER ===== */
footer {
  margin-top: 1.6rem;
  font-size: 0.95rem;
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

@media (max-width: 420px) {
  form { width: 90%; padding: 2rem 1.5rem; }
  h2 { font-size: 1.6rem; }
}
</style>

</style>
</head>
<body>
<form method="POST">
  <h2><i class="fa-solid fa-user-plus"></i> Create Account</h2>

  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="input-group">
    <i class="fa-solid fa-user"></i>
    <input type="text" name="username" placeholder="Username" required>
</div>

<div class="input-group">
    <i class="fa-solid fa-envelope"></i>
    <input type="email" name="email" placeholder="Email" required>
</div>

<div class="input-group">
    <i class="fa-solid fa-lock"></i>
    <input type="password" name="password" placeholder="Password" required>
</div>

<div class="input-group">
    <i class="fa-solid fa-key"></i>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
</div>


  <button type="submit"><i class="fa-solid fa-user-plus"></i> Register</button>

  <footer>
    Already have an account? 
    <a href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
  </footer>
</form>
</body>
</html>
