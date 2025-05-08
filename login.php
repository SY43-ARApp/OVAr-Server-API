<?php
require 'DB.php';

$username = $_GET['uuid'] ?? '';

$q = $mysqli->prepare("SELECT 'GOOD' FROM user WHERE uuid = '?'");
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
