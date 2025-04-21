<?php
require 'DB.php';

$user_id = intval($_POST['user_id'] ?? 0);
$score = intval($_POST['score'] ?? 0);

$q = $mysqli->prepare("INSERT INTO Leaderboard (user_id, score) VALUES (?, ?)");
$q->bind_param("ii", $user_id, $score);
if ($q->execute()) {
    echo "SCORE_ADDED";
} else {
    echo "FAIL";
}
?>
