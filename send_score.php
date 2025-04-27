<?php
require 'DB.php';

// TODO: Check if uuid is valid
$uuid = $_GET['user_id'] ?? $_GET['uuid'] ?? '';
$score = intval($_GET['score'] ?? 0);

$q = $mysqli->prepare("INSERT INTO score (uuid, score) VALUES (?, ?)");
$q->bind_param("si", $uuid, $score);

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
