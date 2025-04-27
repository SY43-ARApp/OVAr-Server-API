<?php
require 'DB.php';

$uuid = $_POST['user_id'] ?? '';
$score = intval($_POST['score'] ?? 0);

$q = $mysqli->prepare("INSERT INTO scores (uuid, score) VALUES (?, ?)");
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
