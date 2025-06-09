<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require 'DB.php';
header('Content-Type: application/json');

$score = $_GET['score'] ?? -1;
$username = $_GET['username'] ?? '';
$uuid = $_GET['uuid'] ?? '';

// Validate inputs
if ($score == -1 ) {
    echo json_encode(["success" => false, "error" => "MISSING_SCORE"]);
    exit;
}
if (empty($username)) {
    echo json_encode(["success" => false, "error" => "MISSING_USERNAME"]);
    exit;
}
if (empty($uuid)) {
    echo json_encode(["success" => false, "error" => "MISSING_UUID"]);
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
$userUUID = $userData['uuid'];

//Check if UUID is an admin
$adminCheck = $mysqli->prepare("SELECT role FROM user WHERE uuid = ?");
$adminCheck->bind_param("s", $uuid);
$adminCheck->execute();
$adminResult = $adminCheck->get_result();
$role = $adminResult->fetch_assoc()['role'];

if ($role != "A") {
    echo json_encode(["success" => false, "error" => "INSUFFICIENT_PERMISSIONS"]);
    exit;
}

// Check if score exists
$scoreCheck = $mysqli->prepare("SELECT id FROM score WHERE uuid = ? AND score = ?");
$scoreCheck->bind_param("si", $userUUID, $score);
$scoreCheck->execute();
$scoreResult = $scoreCheck->get_result();
if ($scoreResult->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "SCORE_NOT_FOUND"]);
    exit;
}
$scoreId = $scoreResult->fetch_assoc()['id'];


// Delete the score
$deleteScore = $mysqli->prepare("DELETE FROM score WHERE id = ?");
$deleteScore->bind_param("i", $scoreId);
$deleteScore->execute();
if ($deleteScore->affected_rows === 0) {
    echo json_encode(["success" => false, "error" => "DELETE_FAILED"]);
    exit;
}

//Get the user's best score
$bestScoreCheck = $mysqli->prepare("SELECT s.score FROM score s WHERE uuid = ? ORDER BY s.score DESC LIMIT 1");
$bestScoreCheck->bind_param("s", $userUUID);
$bestScoreCheck->execute();
$bestScoreResult = $bestScoreCheck->get_result();

$bestScore = 0;
if ($bestScoreResult->num_rows > 0) {
    $bestScoreData = $bestScoreResult->fetch_assoc();
    $bestScore = intval($bestScoreData['score'] ?? 0);
    echo json_encode(["success" => true, "bestScore" => $bestScore]);
}

//Update the user's best score
$updateBestScore = $mysqli->prepare("UPDATE user SET bestScore = ? WHERE uuid = ?");
$updateBestScore->bind_param("is", $bestScore, $userUUID);
$updateBestScore->execute();

if ($updateBestScore->affected_rows === 0) {
    echo json_encode(["success" => false, "error" => "UPDATE_BEST_SCORE_FAILED"]);
}
?>
