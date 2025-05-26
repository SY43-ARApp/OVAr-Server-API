<?php
require 'DB.php';
header('Content-Type: application/json');

$uuid = $_GET['uuid'] ?? '';
$skin_id = intval($_GET['skin_id'] ?? -1);

// Validate inputs
if (empty($uuid)) {
    echo json_encode(["success" => false, "error" => "MISSING_UUID"]);
    exit;
}
if ($skin_id == -1) {
    echo json_encode(["success" => false, "error" => "INVALID_SKIN_ID"]);
    exit;
}

// Check if user exists
$userCheck = $mysqli->prepare("SELECT uuid, money FROM user WHERE uuid = ?");
$userCheck->bind_param("s", $uuid);
$userCheck->execute();
$userResult = $userCheck->get_result();
if ($userResult->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "USER_NOT_FOUND"]);
    exit;
}
$userData = $userResult->fetch_assoc();
$currentMoney = intval($userData['money'] ?? 0);

// Check if skin exists
$skinCheck = $mysqli->prepare("SELECT id, price FROM skins WHERE id = ?");
$skinCheck->bind_param("i", $skin_id);
$skinCheck->execute();
$skinResult = $skinCheck->get_result();
if ($skinResult->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "SKIN_NOT_FOUND"]);
    exit;
}
$skinData = $skinResult->fetch_assoc();
$skinPrice = intval($skinData['price']);

// Check if user already owns the skin
$ownCheck = $mysqli->prepare("SELECT * FROM userSkins WHERE user_id = ? AND skin_id = ?");
$ownCheck->bind_param("si", $uuid, $skin_id);
$ownCheck->execute();
$ownResult = $ownCheck->get_result();
if ($ownResult->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "ALREADY_OWNED"]);
    exit;
}

// Check if user has enough money
if ($currentMoney < $skinPrice) {
    echo json_encode(["success" => false, "error" => "INSUFFICIENT_FUNDS"]);
    exit;
}

// Check if user best score is sufficient
$scoreCheck = $mysqli->prepare("SELECT bestscore FROM user WHERE uuid = ?");
$scoreCheck->bind_param("s", $uuid);
$scoreCheck->execute();
$scoreResult = $scoreCheck->get_result();
if ($scoreResult->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "USER_NOT_FOUND"]);
    exit;
}
$scoreData = $scoreResult->fetch_assoc();
$bestScore = intval($scoreData['bestscore'] ?? 0);
if ($bestScore < $skinData['unlockingScore']) {
    echo json_encode(["success" => false, "error" => "INSUFFICIENT_SCORE"]);
    exit;
}


// Deduct money and add skin in a transaction
$mysqli->begin_transaction();
try {
    $updateMoney = $mysqli->prepare("UPDATE user SET money = money - ? WHERE uuid = ?");
    $updateMoney->bind_param("is", $skinPrice, $uuid);
    if (!$updateMoney->execute()) throw new Exception("UPDATE_MONEY_FAIL");

    $addSkin = $mysqli->prepare("INSERT INTO userSkins (user_id, skin_id) VALUES (?, ?)");
    $addSkin->bind_param("si", $uuid, $skin_id);
    if (!$addSkin->execute()) throw new Exception("ADD_SKIN_FAIL");

    $mysqli->commit();
    echo json_encode(["success" => true, "message" => "SKIN_PURCHASED"]);
} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
