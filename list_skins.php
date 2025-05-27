<?php
// Debug: afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'DB.php';

$resDir = __DIR__ . '/res/';
$skinFiles = scandir($resDir);
$skinIdsWithFiles = [];
foreach ($skinFiles as $file) {
    if (preg_match('/^(\d+)_name\\.txt$/', $file, $matches)) {
        $skinIdsWithFiles[] = intval($matches[1]);
    }
}

// Récupérer tous les ids de la BDD
$allIds = [];
$res = $mysqli->query('SELECT id FROM skins');
while ($row = $res->fetch_assoc()) {
    $allIds[] = intval($row['id']);
}

// Séparer les deux listes
$withFiles = array_intersect($allIds, $skinIdsWithFiles);
$withoutFiles = array_diff($allIds, $skinIdsWithFiles);

function getSkinData($id, $mysqli, $resDir) {
    $nameFile = $resDir . $id . '_name.txt';
    $shopImg = 'res/' . $id . '_shop.png';
    $name = file_exists($nameFile) ? trim(file_get_contents($nameFile)) : 'Inconnu';
    $stmt = $mysqli->prepare('SELECT price, unlockingScore FROM skins WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $price = $unlockingScore = null;
    if ($row = $result->fetch_assoc()) {
        $price = $row['price'];
        $unlockingScore = $row['unlockingScore'];
    }
    return [
        'id' => $id,
        'name' => $name,
        'shopImg' => $shopImg,
        'price' => $price,
        'unlockingScore' => $unlockingScore
    ];
}

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    // Supprimer de la BDD
    $stmt = $mysqli->prepare('DELETE FROM skins WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    // Supprimer les fichiers s'ils existent
    $pattern = [
        '_name.txt', '_shop.png', '_texture.png', '_obj.obj'
    ];
    foreach ($pattern as $suffix) {
        $f = $resDir . $deleteId . $suffix;
        if (file_exists($f)) unlink($f);
    }
    // Supprimer aussi dans userSkins
    $stmt2 = $mysqli->prepare('DELETE FROM userSkins WHERE skin_id = ?');
    $stmt2->bind_param('i', $deleteId);
    $stmt2->execute();
    echo '<div style="color:green">Skin ' . $deleteId . ' supprimé.</div>';
    // Forcer un vrai refresh pour éviter le repost
    echo '<script>window.location.href=window.location.href;</script>';
    exit;
}

$skinsWithFiles = [];
foreach ($withFiles as $id) {
    $skinsWithFiles[] = getSkinData($id, $mysqli, $resDir);
}
$skinsWithoutFiles = [];
foreach ($withoutFiles as $id) {
    $skinsWithoutFiles[] = getSkinData($id, $mysqli, $resDir);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des skins serveur</title>
    <style>
        .skin-card {
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin: 10px;
            padding: 10px;
            width: 200px;
            text-align: center;
            box-shadow: 2px 2px 8px #eee;
        }
        .skin-card img {
            max-width: 100%;
            max-height: 120px;
            margin-bottom: 8px;
        }
        .skin-title {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 6px;
        }
        .skin-info {
            color: #555;
            font-size: 0.95em;
        }
        .delete-btn {
            margin-top: 8px;
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<h2>Skins avec fichiers présents sur le serveur</h2>
<div>
<?php foreach ($skinsWithFiles as $skin): ?>
    <div class="skin-card">
        <div class="skin-title"><?= htmlspecialchars($skin['name']) ?></div>
        <img src="<?= htmlspecialchars($skin['shopImg']) ?>" alt="shop image">
        <div class="skin-info">ID : <?= $skin['id'] ?></div>
        <div class="skin-info">Prix : <?= $skin['price'] !== null ? $skin['price'] : 'N/A' ?></div>
        <div class="skin-info">Score déblocage : <?= $skin['unlockingScore'] !== null ? $skin['unlockingScore'] : 'N/A' ?></div>
        <form method="post" style="margin:0"><input type="hidden" name="delete_id" value="<?= $skin['id'] ?>"><button class="delete-btn" type="submit">Supprimer</button></form>
    </div>
<?php endforeach; ?>
</div>

<h2>Skins sans fichiers sur le serveur</h2>
<div>
<?php foreach ($skinsWithoutFiles as $skin): ?>
    <div class="skin-card">
        <div class="skin-title"><?= htmlspecialchars($skin['name']) ?></div>
        <div class="skin-info">ID : <?= $skin['id'] ?></div>
        <div class="skin-info">Prix : <?= $skin['price'] !== null ? $skin['price'] : 'N/A' ?></div>
        <div class="skin-info">Score déblocage : <?= $skin['unlockingScore'] !== null ? $skin['unlockingScore'] : 'N/A' ?></div>
        <form method="post" style="margin:0"><input type="hidden" name="delete_id" value="<?= $skin['id'] ?>"><button class="delete-btn" type="submit">Supprimer</button></form>
    </div>
<?php endforeach; ?>
</div>
</body>
</html>
