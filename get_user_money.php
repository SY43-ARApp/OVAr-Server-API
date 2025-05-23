<?php
require 'DB.php';

header('Content-Type: application/json');

// Get parameters from request
$uuid = $_GET['uuid'] ?? '';

// Validate UUID
if (empty($uuid)) {
    http_response_code(400);
    echo json_encode(["error" => "ERROR_MISSING_UUID"]);
    exit;
}

// Check if user exists and get money
$userCheck = $mysqli->prepare("SELECT money FROM user WHERE uuid = ?");
$userCheck->bind_param("s", $uuid);
$userCheck->execute();
$userResult = $userCheck->get_result();

if ($userResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "ERROR_USER_NOT_FOUND"]);
    exit;
}

// Get money value
$userData = $userResult->fetch_assoc();
$money = intval($userData['money']);

// Return the result as JSON
echo json_encode(["money" => $money]);
?>
