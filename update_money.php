<?php
require 'DB.php';

// Get parameters from request
$uuid = $_GET['uuid'] ?? '';
$amount = intval($_GET['amount'] ?? -1);

// Validate inputs
if (empty($uuid)) {
    echo "ERROR_MISSING_UUID";
    exit;
}

if ($amount == -1) {
    echo "ERROR_INVALID_AMOUNT";
    exit;
}

// Check if user exists
$userCheck = $mysqli->prepare("SELECT uuid, money FROM user WHERE uuid = ?");
$userCheck->bind_param("s", $uuid);
$userCheck->execute();
$userResult = $userCheck->get_result();

if ($userResult->num_rows === 0) {
    echo "ERROR_USER_NOT_FOUND";
    exit;
}

// Get current money
$userData = $userResult->fetch_assoc();
$currentMoney = intval($userData['money']);

// Calculate new money amount
$newMoney = $currentMoney + $amount;

// Prevent negative money
if ($newMoney < 0) {
    echo "ERROR_INSUFFICIENT_FUNDS";
    exit;
}

// Update user's money
$updateQuery = $mysqli->prepare("UPDATE user SET money = ? WHERE uuid = ?");
$updateQuery->bind_param("is", $newMoney, $uuid);

if ($updateQuery->execute()) {
    echo "MONEY_UPDATED";
} else {
    echo "FAIL";
}
?>
