<?php
require 'DB.php';

$limit = intval($_GET['limit'] ?? -1);
$uuid = $_GET['user_id'] ?? $_GET['uuid'] ?? '';
$order = $_GET['order'] ?? 'DESC';
$param = $_GET['param'] ?? 'score';
$valid_orders = ['ASC', 'DESC'];
$valid_params = ['score', 'time'];


if (!in_array($order, $valid_orders)) {
    echo 'ERROR_ORDER';
    exit;
}
if (!in_array($param, $valid_params)) {
    echo 'ERROR_PARAM';
    exit;
}

if(!$uuid)
{
    echo 'ERROR_USER_ID';
    exit;
}

if ($limit > 0) {
    $sql = "SELECT score, time FROM score WHERE uuid=? ORDER BY $param $order";
    $sql .= " LIMIT ?";
    $q = $mysqli->prepare($sql);
    $q->bind_param("si", $uuid, $limit);
} else {
    $sql = "SELECT score, time FROM score WHERE uuid=? ORDER BY $param $order";
    $q = $mysqli->prepare($sql);
    $q->bind_param("s", $uuid);
}

$q->execute();
$res = $q->get_result();
$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

if (empty($data)) {
    echo json_encode(['message' => 'None']);
} else {
    header('Content-Type: application/json');
    echo json_encode($data);
}
?>
