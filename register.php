<?php
require 'DB.php';

$uuid = $_GET['uuid'] ?? '';
$username = $_GET['username'] ?? '';

if (!$uuid || !$username) {
    echo "ERROR_MISSING_PARAMS";
    exit;
}

// Check if UUID exists
$check = $mysqli->prepare("SELECT uuid FROM user WHERE uuid = ?");
$check->bind_param("s", $uuid);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 1) {
    // UUID exists, update username
    $update = $mysqli->prepare("UPDATE user SET name = ? WHERE uuid = ?");
    $update->bind_param("ss", $username, $uuid);
    if ($update->execute()) {
        echo "REGISTERED:" . $uuid;
    } else {
        echo "ERROR_UPDATE";
    }
} else {
    // New registration
    $insert = $mysqli->prepare("INSERT INTO user (uuid, name, bestscore) VALUES (?, ?, 0)");
    $insert->bind_param("ss", $uuid, $username);
    if ($insert->execute()) {
        echo "REGISTERED:" . $uuid;
    } else {
        echo "ERROR_INSERT";
    }
}
?>
