<?php
require 'DB.php';

header('Content-Type: application/json');

$limit = intval($_GET['limit'] ?? -1);

$sql = "SELECT u.name as username, u.bestscore as score
        FROM user u
        ORDER BY bestscore DESC, name ASC";

if ($limit > 0) {
    $sql .= " LIMIT ?";
    $q = $mysqli->prepare($sql);
    $q->bind_param("i", $limit);
} else {
    $q = $mysqli->prepare($sql);
}

$q->execute();
$res = $q->get_result();
$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
