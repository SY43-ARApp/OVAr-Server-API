<?php
require 'DB.php';

$sql = "SELECT uuid, name as username, bestscore, money
        FROM user
        ORDER BY name ASC";

$q = $mysqli->prepare($sql);
$q->execute();
$res = $q->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>