<?php
require 'DB.php';

$username = $_POST['username'] ?? '';
$password = $_POST['hashed_password'] ?? '';

$q = $mysqli->prepare("SELECT user_id FROM Users WHERE username = ? AND hashed_password = ?");
$q->bind_param("ss", $username, $password);
$q->execute();
$result = $q->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    echo $row['user_id'];
} else {
    echo "FAIL";
}
?>
