<?php
$mysqli = new mysqli('localhost', 'shoe_shop', 'ChangeMeStrong!', 'shoe_shop');

if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');
?>
