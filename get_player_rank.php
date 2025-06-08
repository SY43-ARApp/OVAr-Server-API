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

$sql = "SELECT bestscore, name FROM user WHERE uuid = ?";
$q = $mysqli->prepare($sql);
$q->bind_param("s", $uuid);
$q->execute();
$q->bind_result($bestscore, $name);
$q->fetch();
$q->close();

if ($bestscore === null || $name === null) {
    echo json_encode(['rank' => -1, 'totalPlayers' => $totalPlayers, 'score' => null]);
    exit;
}

$sql = "SELECT COUNT(*) FROM user WHERE (bestscore > ?) OR (bestscore = ? AND name < ?)";
$q = $mysqli->prepare($sql);
$q->bind_param("iis", $bestscore, $bestscore, $name);
$q->execute();
$q->bind_result($higher_count);
$q->fetch();
$q->close();

$rank = $higher_count + 1;

echo json_encode([
    'rank' => intval($rank),
    'totalPlayers' => $totalPlayers,
    'score' => $bestscore
]);
?>
