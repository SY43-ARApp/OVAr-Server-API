<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require 'DB.php';
header('Content-Type: application/json');

$money = $_GET['money'] ?? -1;
$userUuid = $_GET['userUuid'] ?? '';
$adminUuid = $_GET['adminUuid'] ?? '';

// Validate inputs
if ($money < 0) {
    echo json_encode(["success" => false, "error" => "INVALID_MONEY"]);
    exit;
}
if (empty($userUuid)) {
    echo json_encode(["success" => false, "error" => "MISSING_USER_UUID"]);
    exit;
}
if (empty($adminUuid)) {
    echo json_encode(["success" => false, "error" => "MISSING_ADMIN_UUID"]);
    exit;
}

// Check if user exists
$userCheck = $mysqli->prepare("SELECT * FROM user WHERE uuid = ?");
$userCheck->bind_param("s", $userUuid);
$userCheck->execute();
$userResult = $userCheck->get_result();
if ($userResult->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "USER_NOT_FOUND"]);
    exit;
}

// Check if UUID is an admin
$adminCheck = $mysqli->prepare("SELECT role FROM user WHERE uuid = ?");
$adminCheck->bind_param("s", $adminUuid);
$adminCheck->execute();
$adminResult = $adminCheck->get_result();
if ($adminResult->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "ADMIN_NOT_FOUND"]);
    exit;
}
$role = $adminResult->fetch_assoc()['role'];

if ($role != "A") {
    echo json_encode(["success" => false, "error" => "INSUFFICIENT_PERMISSIONS"]);
    exit;
}

// Update user's money
$updateMoney = $mysqli->prepare("UPDATE user SET money = ? WHERE uuid = ?");
$updateMoney->bind_param("is", $money, $userUuid);
$updateMoney->execute();

if ($updateMoney->affected_rows === 0) {
    echo json_encode(["success" => false, "error" => "UPDATE_FAILED"]);
    exit;
}

// Return success
echo json_encode(["success" => true, "money" => $money]);
?>
