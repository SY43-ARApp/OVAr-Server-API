<?php
require 'DB.php';

// TODO: Check if uuid is valid
$uuid = $_GET['user_id'] ?? $_GET['uuid'] ?? '';
$score = intval($_GET['score'] ?? 0);
$arrows_thrown = intval($_GET['arrows_thrown'] ?? 0);
$planets_hit = intval($_GET['planets_hit'] ?? 0);
$levels_passed = intval($_GET['levels_passed'] ?? 0);

$q = $mysqli->prepare("INSERT INTO score (uuid, score, arrows_thrown, planets_hit, levels_passed) VALUES (?, ?, ?, ?, ?)");
$q->bind_param("siiii", $uuid, $score, $arrows_thrown, $planets_hit, $levels_passed);

// Update bestscore if necessary
$update = $mysqli->prepare("UPDATE user SET bestscore = ? WHERE uuid = ? AND bestscore < ?");
$update->bind_param("isi", $score, $uuid, $score);
$update->execute();

if ($q->execute()) {
    echo "SCORE_ADDED";
} else {
    echo "FAIL";
}
?>
