<?php
require 'DB.php';

header('Content-Type: application/json');

$uuid = $_GET['uuid'] ?? '';

// Check if uuid parameter is provided
if (!$uuid) {
    http_response_code(400);
    echo json_encode(["error" => "ERROR_MISSING_UUID"]);
    exit;
}

// Check if user exists
$userCheck = $mysqli->prepare("SELECT uuid FROM user WHERE uuid = ?");
$userCheck->bind_param("s", $uuid);
$userCheck->execute();
$userResult = $userCheck->get_result();

if ($userResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "ERROR_USER_NOT_FOUND"]);
    exit;
}

// Query to get all skins owned by the user with their type
$query = $mysqli->prepare("SELECT us.skin_id as skinId, s.id_type as type 
                          FROM userSkins us
                          JOIN skins s ON us.skin_id = s.id
                          WHERE us.user_id = ?");
$query->bind_param("s", $uuid);
$query->execute();
$result = $query->get_result();

// Check if query was successful
if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $mysqli->error]);
    exit;
}

$userSkins = [];
while ($row = $result->fetch_assoc()) {
    $userSkins[] = [
        'skinId' => intval($row['skinId']),
        'type' => intval($row['type'])
    ];
}

// Return the result as JSON
echo json_encode($userSkins);
?>
