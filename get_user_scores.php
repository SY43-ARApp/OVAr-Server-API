<?php
require 'DB.php';

$limit = intval($_GET['limit'] ?? -1);
$uuid = $_POST['user_id'] ?? '';
$order = $_POST['order'] ?? 'DESC';
$param = $_POST['param'] ?? 'score';
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

#       SELECT score, time FROM score WHERE uuid='uuidtropbien' ORDER BY score DESC;
$sql = "SELECT score, time FROM score WHERE uuid= ?             ORDER BY ?     ?";

if ($limit > 0) {
    $sql .= " LIMIT ?";
    $q = $mysqli->prepare($sql);
    $q->bind_param("sssi", $uuid, $order, $param, $limit);
} else {
    $q = $mysqli->prepare($sql);
}

$q->execute();
$res = $q->get_result();
$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
