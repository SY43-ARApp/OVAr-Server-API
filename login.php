<?php
require 'DB.php';

$uuid = $_GET['uuid'] ?? '';

if (!$uuid) {
    echo "FAIL";
    exit;
}

$q = $mysqli->prepare("SELECT name FROM user WHERE uuid = ?");
$q->bind_param("s", $uuid);
$q->execute();
$result = $q->get_result();

if ($result->num_rows === 1) {
    echo "GOOD";
} else {
    echo "UNKNOWN_UUID";
}
?>
