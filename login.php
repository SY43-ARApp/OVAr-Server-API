<?php
require 'DB.php';

$username = $_GET['username'] ?? '';

$q = $mysqli->prepare("SELECT uuid FROM user WHERE name = ?");
$q->bind_param("s", $username);
$q->execute();
$result = $q->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    echo $row['uuid'];
} else {
    echo "FAIL";
}
?>
