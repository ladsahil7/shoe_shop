<?php
// 🚨 INTENTIONALLY VULNERABLE: Use ONLY on localhost for learning/testing!
$conn = mysqli_connect("localhost", "root", "sahil@070103", "shoe_shop");

$query = $_GET['query'] ?? '';

$sql = "SELECT * FROM products WHERE name LIKE '%$query%'"; // SQL Injection here 👀
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SQLi Search Lab</title>
</head>
<body>

<h2>Search Test (SQL Injection Enabled)</h2>

<form method="GET">
  <input type="text" name="query" value="<?= htmlspecialchars($query) ?>" placeholder="Find shoes...">
  <button type="submit">Search</button>
</form>

<hr>

<?php
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "• " . $row['name'] . "<br>";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>

<br><hr>
<strong>Executed Query:</strong> <?= $sql ?>

</body>
</html>
