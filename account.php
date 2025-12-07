<?php
session_start();
require_once "common.php";

/* Intentional IDOR Weakness for Pentesting Purposes */
$userId = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user']['id'];

/* If NO session AND no ?id=, redirect */
if (!isset($_SESSION['user']) && !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}
// -----------------------------------------------------
// ⚠️ CSRF & IDOR VULNERABILITY (INTENTIONAL)
// -----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    // 1. CSRF Vulnerability: Action is triggered by a simple GET request with no token check.
    // 2. IDOR Vulnerability: Deletion is based on the controllable $userId parameter.
    
    // Use the potentially user-controlled $userId derived from ?id= or the session.
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Log out the user whose account *was* deleted
    if ($userId == $_SESSION['user']['id']) {
        session_destroy();
    }

    echo "<script>alert('Account (ID: " . htmlspecialchars($userId) .") has been deleted!'); window.location.href='index.php';</script>";
    exit;
}
// -----------------------------------------------------
$error = "";
$success = "";

/* Fetch current user info */
$stmt = $mysqli->prepare("SELECT username, email, profile_pic, phone, address, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


/* Handle profile update */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $bio = trim($_POST['bio']);

    $profilePic = $user['profile_pic'];

    if (!empty($_FILES['profile_pic']['name'])) {
        /* ----- VULNERABLE FILE UPLOAD SECTION START ----- */
        // The original logic was already highly vulnerable due to no validation.
        // This simplified path makes the vulnerability clearer for testing.
        $fileName = basename($_FILES['profile_pic']['name']); // Using basename() to mitigate basic path traversal
        $targetPath = "uploads/" . time() . "_" . $fileName; // Added timestamp prefix back for minor defense against name collision, but still vulnerable.

        // NO validation on file type, extension, or content.
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
            $profilePic = time() . "_" . $fileName;
        }
        /* ----- VULNERABLE FILE UPLOAD SECTION END ----- */
    }

    $update = $mysqli->prepare("
        UPDATE users 
        SET username=?, email=?, phone=?, address=?, bio=?, profile_pic=? 
        WHERE id=?
    ");
    $update->bind_param("ssssssi",
        $username, $email, $phone, $address, $bio, $profilePic, $userId
    );

    if ($update->execute()) {
        $success = "Profile updated successfully.";
        $_SESSION['user']['name'] = $username;
    } else {
        $error = "Something went wrong while updating.";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Account | ShoeShop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
/* ===== GLOBAL ===== */
body {
    font-family: Georgia, serif;
    background: #f5f1e8;
    padding: 40px;
    color:#1f2d24;
    display:flex;
    justify-content:center;
}

/* ===== CONTAINER ===== */
.container {
    width: 100%;
    max-width: 720px;
    padding: 35px;
    background: #fffdf7;
    border-radius: 20px;
    border:1px solid #e1d6b9;
    box-shadow: 0 10px 30px rgba(0,0,0,0.18);
    animation: slideUp 0.55s ease-out;
}

@keyframes slideUp {
    from { transform: translateY(40px); opacity:0; }
    to { transform: translateY(0); opacity:1; }
}

/* ===== HEADER ===== */
h2 {
    text-align:center;
    margin-bottom:25px;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing:0.5px;
    color:#1f2d24;
    display:flex;
    justify-content:center;
    align-items:center;
    gap:12px;
}

h2 i {
    color:#8f7f56;
}

/* ===== PROFILE IMAGE ===== */
.profile-pic-area {
    text-align: center;
    margin-bottom: 30px;
    position: relative;
}

.profile-pic-area img {
    width: 200px; /* Increased size */
    height: 200px;
    object-fit: cover;
    border-radius: 50%;
    border: 6px solid #cbb98e;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.profile-pic-area img:hover {
    transform: scale(1.04);
    box-shadow: 0 10px 22px rgba(0,0,0,0.28);
}


/* ===== FORM ELEMENTS ===== */
label {
    font-weight:600;
    display:block;
    margin-bottom:6px;
    color:#3a3a3a;
}

form input, form textarea {
    width: 100%;
    padding: 13px 14px;
    margin-bottom: 18px;
    background: #faf7ef;
    border-radius: 12px;
    border: 1px solid #d4c49c;
    color: #1f2d24;
    font-size: 1rem;
    transition: all 0.25s ease;
    backdrop-filter: blur(4px);
}

form input:focus, form textarea:focus {
    border-color: #8f7f56;
    box-shadow: 0 0 0 3px rgba(143, 127, 86, 0.25);
    transform: translateY(-2px);
}

/* ===== BUTTON ===== */
button {
    width: 100%;
    padding: 13px;
    background:#1f2d24;
    color:#f5f1e8;
    border:none;
    border-radius:12px;
    cursor:pointer;
    font-weight:700;
    letter-spacing:0.7px;
    font-size:1.05rem;
    transition: all 0.25s ease;
    display:flex;
    justify-content:center;
    align-items:center;
    gap:10px;
}

button:hover {
    background:#24382e;
    transform: translateY(-3px);
    box-shadow:0 8px 18px rgba(0,0,0,0.25);
}

/* ===== ALERTS ===== */
.success, .error {
    padding:12px;
    border-radius:10px;
    margin-bottom:20px;
    font-weight:600;
    border-left:5px solid;
}

.success {
    background:#dff2df;
    border-color:#2f7d2f;
    color:#0a3d0a;
}
.error {
    background:#f7d4d4;
    border-color:#b03333;
    color:#702020;
}

/* ===== BACK LINK ===== */
.back-link {
    display:block;
    text-align:center;
    margin-top:25px;
    color:#1f2d24;
    font-weight:600;
    text-decoration:none;
}
.back-link:hover {
    text-decoration:underline;
}
</style>
</head>

<body>

<div class="container">
    <h2><i class="fa-solid fa-user-circle"></i> My Account</h2>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-pic-area">
        <img src="uploads/<?= htmlspecialchars($user['profile_pic'] ?: 'default.png') ?>" alt="Profile Picture">
    </div>

    <form method="post" enctype="multipart/form-data">

        <label>Profile Picture</label>
        <input type="file" name="profile_pic">

        <label>Full Name</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Email Address</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">

        <label>Address</label>
        <textarea name="address" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>

        <label>Bio</label>
        <textarea name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

        <button type="submit">
            <i class="fa-solid fa-save"></i> Update Profile
        </button>
    </form>

    <a href="index.php" class="back-link">← Back to Home</a>
</div>

</body>
</html>