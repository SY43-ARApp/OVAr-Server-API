<?php
require 'DB.php';
header('Content-Type: application/json');

$result = $mysqli->query("SELECT COUNT(*) as total FROM user");
if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(intval($row['total']));
} else {
    http_response_code(500);
    echo json_encode(["error" => "ERROR_DB_QUERY"]);
}
?>
