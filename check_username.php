<?php
require 'DB.php';

$username = $_GET['username'] ?? '';

header('Content-Type: application/json');

if (!$username) {
    echo json_encode(['available' => false, 'message' => 'NO_USERNAME']);
    exit;
}

$sql = "SELECT 1 FROM user WHERE name = ? LIMIT 1";
$q = $mysqli->prepare($sql);
$q->bind_param("s", $username);
$q->execute();
$q->store_result();

if ($q->num_rows > 0) {
    echo json_encode(['available' => false, 'message' => 'Username is taken']);
} else {
    echo json_encode(['available' => true]);
}
?>
