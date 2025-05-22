<?php
require 'DB.php';

header('Content-Type: application/json');

// Query to get all skins with their prices, minimum scores and type
$query = "SELECT id, price, unlockingScore as minimalScore, id_type as type FROM skins";
$result = $mysqli->query($query);

// Check if query was successful
if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $mysqli->error]);
    exit;
}

$skins = [];
while ($row = $result->fetch_assoc()) {    $skins[] = [
        'id' => intval($row['id']),
        'price' => intval($row['price']),
        'minimalScore' => intval($row['minimalScore']),
        'type' => intval($row['type'])
    ];
}

// Return the result as JSON
echo json_encode($skins);
?>
