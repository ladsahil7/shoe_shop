<?php
session_start();
require_once 'common.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = (int)$_GET['id'];
$error = '';
$info  = '';

/* ============================================================
   HANDLE FORCE RESET — runs before main update to avoid clashes
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['force_reset'])) {

    $token   = bin2hex(random_bytes(16));
    $expires = time() + 3600;

    $stmt = $mysqli->prepare(
        "INSERT INTO password_resets (user_id, token, expires_at)
         VALUES (?, ?, FROM_UNIXTIME(?))"
    );
    $stmt->bind_param("isi", $id, $token, $expires);

    if ($stmt->execute()) {
        $info = "Password reset token created successfully.";
    } else {
        $error = "Failed to create password reset token.";
    }

    $stmt->close();
}

/* ============================================================
   HANDLE NORMAL USER UPDATE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['force_reset'])) {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $newpass  = trim($_POST['new_password'] ?? '');

    if ($username === '' || $email === '') {
        $error = "Username and email are required.";
    } else {

        /* Update with NEW password */
if ($newpass !== '') {

    if (strlen($newpass) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {

        $pass_hash = password_hash($newpass, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("
            UPDATE users
            SET username = ?, email = ?, password = ?, password_plain = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $username, $email, $pass_hash, $newpass, $id);
    }

} else {

    $stmt = $mysqli->prepare("
        UPDATE users
        SET username = ?, email = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $username, $email, $id);

}
        if (!$error && $stmt->execute()) {
            $stmt->close();
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = $error ?: "Database update failed.";
        }
    }
}

/* ============================================================
   LOAD USER INFO
   ============================================================ */
$stmt = $mysqli->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit User</title>
  <style>
    /* ===== GLOBAL ===== */
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  background: #f5f1e8;
  color: #2a2a2a;
  font-family: "Georgia", serif;
  padding: 2rem;
  display: flex;
  justify-content: center;
}

/* ===== CARD ===== */
.card {
  background: #fffdf7;
  border: 1px solid #e7d8a6;
  border-radius: 18px;
  padding: 2rem;
  max-width: 650px;
  width: 100%;
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* ===== HEADER ===== */
h2 {
  text-align: center;
  color: #1f2d24;
  font-size: 1.9rem;
  font-weight: 700;
  margin-bottom: 1.4rem;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #d4c49c;
  padding-bottom: 0.7rem;
}

/* ===== LABELS ===== */
label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 600;
  color: #1f2d24;
}

/* ===== INPUTS ===== */
input[type=text],
input[type=email],
input[type=password] {
  width: 100%;
  padding: 0.9rem 1rem;
  border-radius: 10px;
  border: 1px solid #d4c49c;
  background: #fffdf7;
  color: #1f2d24;
  margin-bottom: 1.2rem;
  font-size: 1rem;
  transition: 0.3s ease;
}

input:focus {
  outline: none;
  border-color: #1f2d24;
  box-shadow: 0 0 8px rgba(31,45,36,0.3);
}

/* ===== BUTTONS ===== */
.btn {
  background: #1f2d24;
  color: #e7d8a6;
  padding: 0.9rem 1rem;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  font-weight: 700;
  font-size: 1.05rem;
  transition: 0.3s ease;
  display: inline-block;
  text-align: center;
}

.btn:hover {
  background: #2d4237;
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}

.btn.danger {
  background: #a52e2e;
  color: #fffaf2;
}

.btn.danger:hover {
  background: #7e1e1e;
}

/* Cancel Button */
a.btn {
  text-decoration: none;
  margin-left: 8px;
  background: #d4c49c;
  color: #1f2d24;
}

a.btn:hover {
  background: #e7d8a6;
  transform: translateY(-2px);
}

/* ===== DIVIDERS ===== */
hr {
  border: none;
  border-top: 1px solid #d4c49c;
  margin: 1.5rem 0;
}

/* ===== STATUS MESSAGES ===== */
.err {
  color: #7a0000;
  background: #f3d5d5;
  border: 1px solid #caa5a5;
  padding: 0.8rem;
  border-radius: 10px;
  margin-bottom: 1rem;
  text-align: center;
  font-weight: 600;
}

.info {
  color: #1f2d24;
  background: #e8e2c8;
  border: 1px solid #d4c49c;
  padding: 0.8rem;
  border-radius: 10px;
  margin-bottom: 1rem;
  text-align: center;
  font-weight: 600;
}

/* ===== FOOTNOTE ===== */
p {
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #4a4a4a;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
  .card {
    padding: 1.5rem;
  }
  h2 {
    font-size: 1.6rem;
  }
}

  </style>
</head>
<body>
  <div class="card">
    <h2>Edit User #<?= $user['id'] ?></h2>
    <?php if (!empty($error)): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if (!empty($info)): ?><p class="info"><?= htmlspecialchars($info) ?></p><?php endif; ?>

    <form method="post">
      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

      <label>Set New Password (leave empty to keep current)</label>
      <input type="password" name="new_password" placeholder="New password (min 6 chars)">

      <button class="btn" type="submit">Save</button>
      <a class="btn" href="admin_dashboard.php" style="background:#444;margin-left:8px">Cancel</a>
    </form>

    <hr style="border-color:#222;margin:1.2rem 0;">

    <form method="post" onsubmit="return confirm('Force a password reset for this user? This will create a reset token and require the user to pick a new password.');">
      <input type="hidden" name="force_reset" value="1">
      <button class="btn danger" type="submit">Force Password Reset (send reset link)</button>
    </form>

    <p style="margin-top:1rem;color:#bbb;font-size:0.9rem;">
      Note: For security we do not show users' existing passwords. Use the "Set New Password" field or "Force Password Reset" to regain account access safely.
    </p>
  </div>
</body>
</html>
