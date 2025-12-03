<?php
session_start();

// Assuming 'common.php' contains the $mysqli database connection
require_once 'common.php'; 

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Retaining the intentional post-parameter bypass for demonstration
    $isAdminBypass = $_POST['isAdmin'] ?? ''; 

    // --- Intentional Authentication Bypass Vulnerability (Still present for a different attack vector) ---
    if ($isAdminBypass == '1') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = 'bypassed@local';
        header("Location: admin_dashboard.php");
        exit;
    }
    // -------------------------------------------------------------------------------------------------

    // !!! VULNERABLE SQL INJECTION CODE START !!!
    // The previous hardcoded check has been replaced with a database query.
    // The query is constructed by directly inserting user input ($username and $password) 
    // without any sanitization or use of prepared statements.
    
    $sql = "SELECT id FROM admin WHERE username = '$username' AND password = '$password'";
    
    // Execute the constructed query
    $res = $mysqli->query($sql);
    
    // Check if exactly one row was returned, which indicates a successful login for this simple scheme
    if ($res && $res->num_rows === 1) {
        $admin = $res->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        // In a real app, you'd fetch the email, but for this demo, we'll use the username
        $_SESSION['admin_email'] = $username; 
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials!";
    }
    // !!! VULNERABLE SQL INJECTION CODE END !!!
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login | ShoeShop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
/* ... (Your CSS remains here) ... */
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

/* ===== INPUT GROUP (Improved alignment) ===== */
.input-group {
  position: relative;
  margin-bottom: 1.3rem;
}

.input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #8f7f56;
  font-size: 1.15rem;
  pointer-events: none;
}

input {
  width: 100%;
  padding: 0.9rem 2.8rem 0.9rem 3rem; /* matching offset for equal alignment */
  background: #f5f1e8;
  border: 1px solid #d4c49c;
  border-radius: 10px;
  color: #1f2d24;
  font-size: 1rem;
  transition: border-color 0.3s ease, transform 0.1s ease;
}

input:focus {
  outline: none;
  border-color: #8f7f56;
  transform: scale(1.015);
}

/* ===== PASSWORD TOGGLE ===== */
.toggle-pass {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #8f7f56;
  font-size: 1.15rem;
  cursor: pointer;
}

.toggle-pass:hover {
  color: #6d5e3b;
}

/* Responsive icon sizing fix */
@media (max-width: 420px) {
  .input-icon,
  .toggle-pass {
    font-size: 1.05rem;
  }
}

/* ===== PASSWORD TOGGLE (Redefined for style consistency) ===== */
.toggle-pass {
  position: absolute;
  top: 50%;
  right: 14px;
  transform: translateY(-50%);
  color: #8f7f56;
  font-size: 1.1rem;
  cursor: pointer;
  transition: color 0.3s ease;
}

.toggle-pass:hover {
  color: #6d5e3b;
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

/* ===== RESPONSIVE ===== */
@media (max-width: 420px) {
  form {
    width: 90%;
    padding: 2rem 1.5rem;
  }
  h2 { font-size: 1.6rem; }
}
</style>
</head>

<body>

<form method="post" action="admin_login.php" autocomplete="off">
  <h2><i class="fa-solid fa-user-shield"></i> Admin Panel</h2>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="input-group">
    <i class="fa-solid fa-user-shield input-icon"></i>
    <input type="text" name="username" placeholder="Admin Username" required>
</div>

<div class="input-group">
    <i class="fa-solid fa-lock input-icon"></i>
    <input type="password" name="password" id="password" placeholder="Password" required>
    <i class="fa-solid fa-eye toggle-pass" id="togglePass"></i>
</div>


  <button type="submit"><i class="fa-solid fa-right-to-bracket"></i> Login</button>

  <footer>
    Back to <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
  </footer>
</form>

<script>
const toggle = document.getElementById('togglePass');
const password = document.getElementById('password');

toggle.addEventListener('click', () => {
  const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
  password.setAttribute('type', type);
  toggle.classList.toggle('fa-eye');
  toggle.classList.toggle('fa-eye-slash');
});
</script>

</body>
</html>