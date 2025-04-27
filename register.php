<?php
require 'DB.php';

$uuid     = $_POST['uuid'] ?? uniqid('', true);
$username = $_POST['username'] ?? '';

$q = $mysqli->prepare("INSERT INTO user (uuid, name) VALUES (?, ?)");
$q->bind_param("ss", $uuid, $username);
if ($q->execute()) {
    echo "REGISTERED";
} else {
    echo "ERROR";
}
?>
