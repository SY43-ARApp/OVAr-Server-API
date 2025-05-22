<?php
require 'DB.php';

// Get parameters from request
$uuid = $_GET['uuid'] ?? '';
$skinId = intval($_GET['skinId'] ?? 0);

// Validate inputs
if (empty($uuid)) {
    echo "ERROR_MISSING_UUID";
    exit;
}

if ($skinId <= 0) {
    echo "ERROR_INVALID_SKIN_ID";
    exit;
}

// Check if user exists
$userCheck = $mysqli->prepare("SELECT uuid FROM user WHERE uuid = ?");
$userCheck->bind_param("s", $uuid);
$userCheck->execute();
$userResult = $userCheck->get_result();

if ($userResult->num_rows === 0) {
    echo "ERROR_USER_NOT_FOUND";
    exit;
}

// Check if skin exists
$skinCheck = $mysqli->prepare("SELECT id, id_type FROM skins WHERE id = ?");
$skinCheck->bind_param("i", $skinId);
$skinCheck->execute();
$skinResult = $skinCheck->get_result();

if ($skinResult->num_rows === 0) {
    echo "ERROR_SKIN_NOT_FOUND";
    exit;
}

// Insert or update userSkins record
$query = $mysqli->prepare("INSERT INTO userSkins (user_id, skin_id) VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE user_id = user_id");
$query->bind_param("si", $uuid, $skinId);

if ($query->execute()) {
    echo "SKIN_ADDED";
} else {
    echo "FAIL";
}
?>
