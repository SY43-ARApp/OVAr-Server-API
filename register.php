<?php
require 'DB.php';

$uuid     = $_GET['uuid'] ?? uniqid('', true);
$username = $_GET['username'] ?? '';
$q = $mysqli->prepare("INSERT INTO user (uuid, name, bestscore) VALUES (?, ?, 0)");
$q->bind_param("ss", $uuid, $username);
if ($q->execute()) {
    echo "REGISTERED:".$uuid;
} else {
    echo "ERROR";
}
?>
