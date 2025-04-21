<?php
require 'DB.php';

$username = $_POST['username'] ?? '';
$password = $_POST['hashed_password'] ?? '';
$today = date('Y-m-d');

$q = $mysqli->prepare("INSERT INTO Users (username, hashed_password, last_connected) VALUES (?, ?, ?)");
$q->bind_param("sss", $username, $password, $today);
if ($q->execute()) {
    echo "REGISTERED";
} else {
    echo "ERROR";
}
?>
