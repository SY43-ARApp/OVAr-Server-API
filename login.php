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
    $user_id = $row['user_id'];
    
    // updte last_connected for fun
    $today = date('Y-m-d');
    $update = $mysqli->prepare("UPDATE Users SET last_connected = ? WHERE user_id = ?");
    $update->bind_param("si", $today, $user_id);
    $update->execute();
    
    echo $user_id;
} else {
    echo "FAIL";
}
?>
