<?php
require 'DB.php';
header('Content-Type: application/json');

$score = $_GET['score'] ?? -1;
$username = $_GET['username'] ?? '';

// Validate inputs
if ($score == -1 ) {
    echo json_encode(["success" => false, "error" => "MISSING_SCORE"]);
    exit;
}
if (empty($username)) {
    echo json_encode(["success" => false, "error" => "MISSING_USERNAME"]);
    exit;
}

// Check if user exists
$userCheck = $mysqli->prepare("SELECT uuid, bestScore, role FROM user WHERE name = ?");
$userCheck->bind_param("s", $username);
$userCheck->execute();
$userResult = $userCheck->get_result();
if ($userResult->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "USER_NOT_FOUND"]);
    exit;
}
$userData = $userResult->fetch_assoc();
$currentRole = $userData['role'] ?? 'U';

if ($currentRole !== 'A') {
    echo json_encode(["success" => false, "error" => "INSUFFICIENT_PERMISSIONS"]);
    exit;
}

$currentUUID = $userData['uuid'] ?? '';

// Check if score exists
$scoreCheck = $mysqli->prepare("SELECT id FROM scores WHERE uuid = ? AND score = ?");
$scoreCheck->bind_param("si", $currentUUID, $score);
$scoreCheck->execute();
$scoreResult = $scoreCheck->get_result();
if ($scoreResult->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "SCORE_NOT_FOUND"]);
    exit;
}
$scoreId = $scoreResult->fetch_assoc()['id'];

// Delete the score
$deleteScore = $mysqli->prepare("DELETE FROM scores WHERE id = ?");
$deleteScore->bind_param("i", $scoreId);
$deleteScore->execute();
if ($deleteScore->affected_rows === 0) {
    echo json_encode(["success" => false, "error" => "DELETE_FAILED"]);
    exit;
}

//Get the user's best score
$bestScoreCheck = $mysqli->prepare("SELECT score FROM score WHERE uuid = ?");
$bestScoreCheck->bind_param("s", $currentUUID);
$bestScoreCheck->execute();
$bestScoreResult = $bestScoreCheck->get_result();
if ($bestScoreResult->num_rows > 0) {
    $bestScoreData = $bestScoreResult->fetch_assoc();
    $bestScore = intval($bestScoreData['score'] ?? 0);
}

//Update the user's best score
$updateBestScore = $mysqli->prepare("UPDATE user SET bestScore = ? WHERE uuid = ?");
$updateBestScore->bind_param("si", $bestScore, $currentUUID);
$updateBestScore->execute();
if ($updateBestScore->affected_rows === 0) {
    echo json_encode(["success" => false, "error" => "UPDATE_BEST_SCORE_FAILED"]);
}
?>
