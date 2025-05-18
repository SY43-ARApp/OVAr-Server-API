<?php
require 'DB.php';

$uuid = $_GET['uuid'] ?? $_GET['user_id'] ?? '';

header('Content-Type: application/json');

if (!$uuid) {
    echo json_encode(['rank' => -1, 'totalPlayers' => 0]);
    exit;
}

$res = $mysqli->query("SELECT COUNT(*) as total FROM user");
$totalPlayers = 0;
if ($row = $res->fetch_assoc()) {
    $totalPlayers = intval($row['total']);
}

$sql = "SELECT COUNT(*) + 1 AS player_rank FROM user WHERE bestscore > (SELECT bestscore FROM user WHERE uuid = ?)";
$q = $mysqli->prepare($sql);
$q->bind_param("s", $uuid);
$q->execute();
$q->bind_result($player_rank);
$q->fetch();
$q->close();

if ($player_rank === null) {
    $player_rank = -1;
}

echo json_encode([
    'rank' => intval($player_rank),
    'totalPlayers' => $totalPlayers
]);
?>
