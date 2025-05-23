<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uuid'])) {
    $uuid = $_POST['uuid'];

    if (!preg_match('/^[a-f0-9\-]{36}$/i', $uuid)) {
        echo "<p>Invalid UUID format.</p>";
        exit;
    }

    $db = new mysqli('localhost', 'ovar_user', 'ovar_password', 'ovar_db');
    if ($db->connect_error) {
        echo "<p>Database connection failed.</p>";
        exit;
    }

    $db->begin_transaction();

    try {
        // Delete from userSkins
        $stmt = $db->prepare("DELETE FROM userSkins WHERE user_id = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $stmt->close();

        // Delete from score
        $stmt = $db->prepare("DELETE FROM score WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $stmt->close();

        // Delete from user
        $stmt = $db->prepare("DELETE FROM user WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $stmt->close();

        $db->commit();
        echo "<p>Your data has been deleted successfully.</p>";
    } catch (Exception $e) {
        $db->rollback();
        echo "<p>An error occurred while deleting your data.</p>";
    }

    $db->close();
} else {
    echo "<p>No UUID provided.</p>";
}
?>