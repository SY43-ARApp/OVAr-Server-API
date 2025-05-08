<?php
require 'DB.php';

$uuid = $_GET['uuid'] ?? '';
$username = $_GET['username'] ?? '';

if($username) {
    $q = $mysqli->prepare("SELECT uuid FROM user WHERE username = '?'");
    $q->bind_param("s", $username);
} else {
    $q = $mysqli->prepare("SELECT 'GOOD' FROM user WHERE uuid = '?'");
    $q->bind_param("s", $uuid);
}

$q->execute();
$result = $q->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if ($username) {
        $uuid = $row['uuid'];
        echo $uuid;
    } else if ($uuid) {
        echo $row['uuid'];
    }
} else {
    echo "FAIL";
}
?>
