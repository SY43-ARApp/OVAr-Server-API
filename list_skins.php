<?php
require 'DB.php';

$resDir = __DIR__ . '/res/';
$skinFiles = scandir($resDir);
$skinIds = [];
foreach ($skinFiles as $file) {
    if (preg_match('/^(\d+)_name\\.txt$/', $file, $matches)) {
        $skinIds[] = intval($matches[1]);
    }
}

sort($skinIds);
$skins = [];
foreach ($skinIds as $id) {
    $nameFile = $resDir . $id . '_name.txt';
    $shopImg = 'res/' . $id . '_shop.png';
    $name = file_exists($nameFile) ? trim(file_get_contents($nameFile)) : 'Inconnu';
    // Récupérer prix et unlockingScore
    $stmt = $mysqli->prepare('SELECT price, unlockingScore FROM skins WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $price = $unlockingScore = null;
    if ($row = $result->fetch_assoc()) {
        $price = $row['price'];
        $unlockingScore = $row['unlockingScore'];
    }
    $skins[] = [
        'id' => $id,
        'name' => $name,
        'shopImg' => $shopImg,
        'price' => $price,
        'unlockingScore' => $unlockingScore
    ];
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
    </style>
</head>
<body>
<h2>Skins présents sur le serveur</h2>
<div>
<?php foreach ($skins as $skin): ?>
    <div class="skin-card">
        <div class="skin-title"><?= htmlspecialchars($skin['name']) ?></div>
        <img src="<?= htmlspecialchars($skin['shopImg']) ?>" alt="shop image">
        <div class="skin-info">ID : <?= $skin['id'] ?></div>
        <div class="skin-info">Prix : <?= $skin['price'] !== null ? $skin['price'] : 'N/A' ?></div>
        <div class="skin-info">Score déblocage : <?= $skin['unlockingScore'] !== null ? $skin['unlockingScore'] : 'N/A' ?></div>
    </div>
<?php endforeach; ?>
</div>
</body>
</html>
