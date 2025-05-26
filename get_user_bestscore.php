<?php
require 'DB.php';
header('Content-Type: application/json');

$uuid = $_GET['uuid'] ?? '';
if (empty($uuid)) {
    http_response_code(400);
    echo json_encode(["error" => "ERROR_MISSING_UUID"]);
    exit;
}

$userCheck = $mysqli->prepare("SELECT bestscore FROM user WHERE uuid = ?");
$userCheck->bind_param("s", $uuid);
$userCheck->execute();
$userResult = $userCheck->get_result();

if ($userResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "ERROR_USER_NOT_FOUND"]);
    exit;
}

$userData = $userResult->fetch_assoc();
echo json_encode(["bestscore" => intval($userData['bestscore'])]);
?>
