<?php
require 'DB.php';

header('Content-Type: application/json');

// Query to get all skins with their prices and minimum scores
$query = "SELECT id, price, unlockingScore as minimalScore FROM skins";
$result = $mysqli->query($query);

// Check if query was successful
if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $mysqli->error]);
    exit;
}

$skins = [];
while ($row = $result->fetch_assoc()) {
    $skins[] = [
        'id' => intval($row['id']),
        'price' => intval($row['price']),
        'minimalScore' => intval($row['minimalScore'])
    ];
}

// Return the result as JSON
echo json_encode($skins);
?>
