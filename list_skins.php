<?php
// Debug: afficher les erreurs PHP
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require 'DB.php';

function loadEnv($path = '.env') {
    if (!file_exists($path)) return false;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignore les commentaires
        if (strpos(trim($line), '//') === 0) {
            continue;
        }
        
        // Extraire "key = value" ou "key=value"
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Supprimer les guillemets éventuels
            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    return true;
}

// --- AJOUT DE SKIN ---
$addSkinMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skin'])) {
    loadEnv();
    $password = $_POST['add_password'] ?? '';
    $envPassword = getenv('MDP_ADDSKIN');
    $type = intval($_POST['type'] ?? -1);
    $name = trim($_POST['name'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $unlockingScore = intval($_POST['unlockingScore'] ?? 0);
    if (!$envPassword) {
        $addSkinMsg = '<div style="color:red">Erreur : mot de passe non défini côté serveur (MDP_ADDSKIN dans .env).</div>';
    } elseif ($password !== $envPassword) {
        $addSkinMsg = '<div style="color:red">Mot de passe incorrect.</div>';
    } elseif ($type < 0 || $type > 2 || $name === '') {
        $addSkinMsg = '<div style="color:red">Erreur : champs manquants ou invalides.</div>';
    } else {
        $res = $mysqli->query('SELECT MAX(id) as maxid FROM skins');
        $row = $res->fetch_assoc();
        $newId = intval($row['maxid']) + 1;
        $errors = [];
        $uploadDir = __DIR__ . '/res/';
        $pattern = [
            0 => ['shop' => '_shop.png', 'texture' => '_texture.png', 'obj' => '_obj.obj'],
            1 => ['shop' => '_shop.png', 'texture' => '_texture.png'],
            2 => ['shop' => '_shop.png', 'texture' => '_texture.png']
        ];
        $filesNeeded = $pattern[$type];
        foreach ($filesNeeded as $key => $suffix) {
            if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Fichier $key manquant ou invalide.";
            }
        }
        if (count($errors) === 0) {
            foreach ($filesNeeded as $key => $suffix) {
                $dest = $uploadDir . $newId . $suffix;
                move_uploaded_file($_FILES[$key]['tmp_name'], $dest);
            }
            file_put_contents($uploadDir . $newId . '_name.txt', $name);
            $stmt = $mysqli->prepare('INSERT INTO skins (id, price, unlockingScore, id_type) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('iiii', $newId, $price, $unlockingScore, $type);
            if ($stmt->execute()) {
                $addSkinMsg = '<div style="color:green">Skin ajouté avec succès !</div>';
            } else {
                $addSkinMsg = '<div style="color:red">Erreur BDD : ' . $mysqli->error . '</div>';
            }
        } else {
            foreach ($errors as $e) $addSkinMsg .= '<div style="color:red">' . $e . '</div>';
        }
    }
}

// --- LISTE ET SUPPRESSION DES SKINS ---
$resDir = __DIR__ . '/res/';
$skinFiles = scandir($resDir);
$skinIdsWithFiles = [];
foreach ($skinFiles as $file) {
    if (preg_match('/^(\d+)_name\\.txt$/', $file, $matches)) {
        $skinIdsWithFiles[] = intval($matches[1]);
    }
}
$allIds = [];
$res = $mysqli->query('SELECT id FROM skins');
while ($row = $res->fetch_assoc()) {
    $allIds[] = intval($row['id']);
}
$withFiles = array_intersect($allIds, $skinIdsWithFiles);
$withoutFiles = array_diff($allIds, $skinIdsWithFiles);
function getSkinData($id, $mysqli, $resDir) {
    $nameFile = $resDir . $id . '_name.txt';
    $shopImg = 'res/' . $id . '_shop.png';
    $name = file_exists($nameFile) ? trim(file_get_contents($nameFile)) : 'Inconnu';
    $stmt = $mysqli->prepare('SELECT price, unlockingScore, id_type FROM skins WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $price = $unlockingScore = $type = null;
    if ($row = $result->fetch_assoc()) {
        $price = $row['price'];
        $unlockingScore = $row['unlockingScore'];
        $type = $row['id_type'];
    }
    return [
        'id' => $id,
        'name' => $name,
        'shopImg' => $shopImg,
        'price' => $price,
        'unlockingScore' => $unlockingScore,
        'type' => $type
    ];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (function_exists('loadEnv')) loadEnv();
    $password = $_POST['password'] ?? '';
    $envPassword = getenv('MDP_DELETESKIN');
    if (!$envPassword) {
        echo '<div style="color:red">Erreur : mot de passe non défini côté serveur (MDP_DELETESKIN dans .env).</div>';
    } elseif ($password !== $envPassword) {
        echo '<div style="color:red">Mot de passe incorrect.</div>';
    } else {
        $deleteId = intval($_POST['delete_id']);
        $stmt = $mysqli->prepare('DELETE FROM skins WHERE id = ?');
        $stmt->bind_param('i', $deleteId);
        $stmt->execute();
        $pattern = ['_name.txt', '_shop.png', '_texture.png', '_obj.obj'];
        foreach ($pattern as $suffix) {
            $f = $resDir . $deleteId . $suffix;
            if (file_exists($f)) unlink($f);
        }
        $stmt2 = $mysqli->prepare('DELETE FROM userSkins WHERE skin_id = ?');
        $stmt2->bind_param('i', $deleteId);
        $stmt2->execute();
        echo '<div style="color:green">Skin ' . $deleteId . ' supprimé.</div>';
        echo '<script>window.location.href=window.location.href;</script>';
        exit;
    }
}
$skinsWithFiles = [];
foreach ($withFiles as $id) {
    $skinsWithFiles[] = getSkinData($id, $mysqli, $resDir);
}
$skinsWithoutFiles = [];
foreach ($withoutFiles as $id) {
    $skinsWithoutFiles[] = getSkinData($id, $mysqli, $resDir);
}
function sortSkinsByType($skins) {
    $sorted = [0 => [], 1 => [], 2 => []];
    foreach ($skins as $skin) {
        if (isset($skin['type']) && in_array($skin['type'], [0,1,2])) {
            $sorted[$skin['type']][] = $skin;
        } else {
            $sorted[0][] = $skin; // fallback
        }
    }
    return $sorted;
}
$skinsWithFilesSorted = sortSkinsByType($skinsWithFiles);
$skinsWithoutFilesSorted = sortSkinsByType($skinsWithoutFiles);
$typeLabels = [0 => 'Flèches', 1 => 'Planètes', 2 => 'Lunes'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des skins serveur</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7fa;
            margin: 0;
            padding: 0;
        }
        .main-flex-container {
            display: flex;
            align-items: flex-start;
            width: 100%;
            min-height: 100vh;
        }
        .add-skin-form {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #e0e0e0;
            width: 420px;
            min-width: 320px;
            max-width: 420px;
            margin: 30px 0 30px 30px;
            padding: 24px 30px 18px 30px;
            border: 1px solid #d1d5db;
            flex-shrink: 0;
        }
        .main-content {
            flex: 1;
            padding: 0 30px;
        }
        h1 {
            color: #2c3e50;
            margin-top: 30px;
            margin-bottom: 10px;
            text-align: center;
        }
        h2 {
            color: #2c3e50;
            margin-top: 30px;
            margin-bottom: 10px;
            text-align: center;
        }
        h3 {
            color: #34495e;
            margin-left: 20px;
            margin-bottom: 5px;
        }
        .skin-list-section {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            margin-bottom: 30px;
        }
        .skin-card {
            display: inline-block;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            margin: 12px 10px;
            padding: 16px 12px 12px 12px;
            width: 220px;
            background: #fff;
            text-align: center;
            box-shadow: 0 2px 8px #e0e0e0;
            transition: box-shadow 0.2s;
            position: relative;
        }
        .skin-card:hover {
            box-shadow: 0 4px 16px #b0b0b0;
        }
        .skin-card img {
            max-width: 100%;
            max-height: 120px;
            margin-bottom: 10px;
            border-radius: 6px;
            background: #f0f0f0;
        }
        .skin-title {
            font-weight: bold;
            font-size: 1.15em;
            margin-bottom: 8px;
            color: #222;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .skin-title span.emoji {
            font-size: 1.3em;
        }
        .skin-info {
            color: #555;
            font-size: 0.98em;
            margin-bottom: 2px;
        }
        .delete-btn {
            margin-top: 10px;
            background: linear-gradient(90deg, #e74c3c 60%, #c0392b 100%);
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1em;
            transition: background 0.2s;
        }
        .delete-btn:hover {
            background: linear-gradient(90deg, #c0392b 60%, #e74c3c 100%);
        }
        form input[type="password"] {
            margin-bottom: 6px;
            width: 90%;
            padding: 5px 8px;
            border: 1px solid #bbb;
            border-radius: 4px;
            font-size: 1em;
        }
        @media (max-width: 900px) {
            .main-flex-container {
                flex-direction: column;
            }
            .add-skin-form {
                margin: 30px auto 0 auto;
                width: 95vw;
                max-width: 98vw;
            }
            .main-content {
                padding: 0 5vw;
            }
        }
        @media (max-width: 700px) {
            .skin-card { width: 95vw; }
        }
        .add-skin-form {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #e0e0e0;
            max-width: 420px;
            margin: 30px auto 30px auto;
            padding: 24px 30px 18px 30px;
            border: 1px solid #d1d5db;
        }
        .add-skin-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .add-skin-form input[type="text"],
        .add-skin-form input[type="number"],
        .add-skin-form input[type="password"],
        .add-skin-form select {
            width: 100%;
            padding: 7px 10px;
            margin-top: 2px;
            margin-bottom: 12px;
            border: 1px solid #bbb;
            border-radius: 4px;
            font-size: 1em;
        }
        .add-skin-form .dropzone {
            border: 2px dashed #aaa;
            padding: 16px;
            margin: 10px 0 18px 0;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .add-skin-form button[type="submit"] {
            background: linear-gradient(90deg, #27ae60 60%, #219150 100%);
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1.08em;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .add-skin-form button[type="submit"]:hover {
            background: linear-gradient(90deg, #219150 60%, #27ae60 100%);
        }
    </style>
    <script>
    function updateFields() {
        var type = document.getElementById('type').value;
        document.getElementById('objField').style.display = (type == 0) ? 'block' : 'none';
    }
    </script>
</head>
<body>
<h1>Gestion des skins serveur</h1>
<div class="main-flex-container">
    <div class="add-skin-form">
        <h2>Ajouter un skin</h2>
        <?= $addSkinMsg ?>
        <form method="post" enctype="multipart/form-data">
            <label>Mot de passe admin : <input type="password" name="add_password" required></label>
            <label>Type de skin :
                <select name="type" id="type" onchange="updateFields()">
                    <option value="0">Flèche</option>
                    <option value="1">Planète</option>
                    <option value="2">Lune</option>
                </select>
            </label>
            <label>Nom du skin : <input type="text" name="name" required></label>
            <label>Rareté :
                <select name="rarity" id="rarity" onchange="document.getElementById('price').value=this.value;">
                    <option value="2500">Commun (2500)</option>
                    <option value="5000">Rare (5000)</option>
                    <option value="10000">Epique (10000)</option>
                </select>
            </label>
            <input type="hidden" name="price" id="price" value="2500">
            <label>Score de déblocage : <input type="number" name="unlockingScore" min="0" value="0"></label>
            <div class="dropzone">
                <label>Image shop (.png) : <input type="file" name="shop" accept="image/png" required></label>
            </div>
            <div class="dropzone">
                <label>Image texture (.png) : <input type="file" name="texture" accept="image/png" required></label>
            </div>
            <div class="dropzone" id="objField" style="display:block">
                <label>Fichier OBJ (.obj) : <input type="file" name="obj" accept=".obj"></label>
            </div>
            <button type="submit" name="add_skin" value="1">Ajouter</button>
        </form>
        <script>updateFields();</script>
    </div>
    <div class="main-content">
        <h2>Skins avec fichiers présents sur le serveur</h2>
        <?php foreach ($typeLabels as $type => $label): ?>
            <h3><?= $label ?></h3>
            <div class="skin-list-section">
            <?php foreach ($skinsWithFilesSorted[$type] as $skin): ?>
                <div class="skin-card">
                    <div class="skin-title">
                        <span class="emoji"><?php
                        if ($skin['type'] === 0) echo '🏹';
                        elseif ($skin['type'] === 1) echo '🪐';
                        elseif ($skin['type'] === 2) echo '🌙';
                        ?></span>
                        <?= htmlspecialchars($skin['name']) ?>
                    </div>
                    <img src="<?= htmlspecialchars($skin['shopImg']) ?>" alt="shop image">
                    <div class="skin-info">ID : <?= $skin['id'] ?></div>
                    <div class="skin-info">Prix : <?= $skin['price'] !== null ? $skin['price'] : 'N/A' ?></div>
                    <div class="skin-info">Score déblocage : <?= $skin['unlockingScore'] !== null ? $skin['unlockingScore'] : 'N/A' ?></div>
                    <form method="post" style="margin:0">
                        <input type="hidden" name="delete_id" value="<?= $skin['id'] ?>">
                        <input type="password" name="password" placeholder="Mot de passe" required><br>
                        <button class="delete-btn" type="submit">Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <h2>Skins sans fichiers sur le serveur</h2>
        <?php foreach ($typeLabels as $type => $label): ?>
            <h3><?= $label ?></h3>
            <div class="skin-list-section">
            <?php foreach ($skinsWithoutFilesSorted[$type] as $skin): ?>
                <div class="skin-card">
                    <div class="skin-title">
                        <span class="emoji"><?php
                        if ($skin['type'] === 0) echo '🏹';
                        elseif ($skin['type'] === 1) echo '🪐';
                        elseif ($skin['type'] === 2) echo '🌙';
                        ?></span>
                        <?= htmlspecialchars($skin['name']) ?>
                    </div>
                    <div class="skin-info">ID : <?= $skin['id'] ?></div>
                    <div class="skin-info">Prix : <?= $skin['price'] !== null ? $skin['price'] : 'N/A' ?></div>
                    <div class="skin-info">Score déblocage : <?= $skin['unlockingScore'] !== null ? $skin['unlockingScore'] : 'N/A' ?></div>
                    <form method="post" style="margin:0">
                        <input type="hidden" name="delete_id" value="<?= $skin['id'] ?>">
                        <input type="password" name="password" placeholder="Mot de passe" required><br>
                        <button class="delete-btn" type="submit">Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
