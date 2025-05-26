<?php
require 'DB.php';


function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        return false;
    }
    
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

// Gestion de l'upload et de l'ajout en BDD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password !== getenv('MDP_ADDSKIN')) {
        echo '<div style="color:red">Mot de passe incorrect.</div>';
    } else {
        $type = intval($_POST['type'] ?? -1); // 0: flèche, 1: planète, 2: lune
        $name = trim($_POST['name'] ?? '');
        $price = intval($_POST['price'] ?? 0);
        $unlockingScore = intval($_POST['unlockingScore'] ?? 0);

        if ($type < 0 || $type > 2 || $name === '') {
            echo '<div style="color:red">Erreur : champs manquants ou invalides.</div>';
        } else {
            // Récupérer le dernier id
            $res = $mysqli->query('SELECT MAX(id) as maxid FROM skins');
            $row = $res->fetch_assoc();
            $newId = intval($row['maxid']) + 1;

            // Gestion des fichiers
            $errors = [];
            $uploadDir = __DIR__ . '/res/';
            $pattern = [
                0 => ['shop' => '_shop.png', 'texture' => '_texture.png', 'obj' => '_obj.obj'], // flèche
                1 => ['shop' => '_shop.png', 'texture' => '_texture.png'], // planète
                2 => ['shop' => '_shop.png', 'texture' => '_texture.png']  // lune
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
                // Fichier nom
                file_put_contents($uploadDir . $newId . '_name.txt', $name);
                // Ajout BDD
                $stmt = $mysqli->prepare('INSERT INTO skins (id, price, unlockingScore, id_type) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('iiii', $newId, $price, $unlockingScore, $type);
                if ($stmt->execute()) {
                    echo '<div style="color:green">Skin ajouté avec succès !</div>';
                } else {
                    echo '<div style="color:red">Erreur BDD : ' . $mysqli->error . '</div>';
                }
            } else {
                foreach ($errors as $e) echo '<div style="color:red">' . $e . '</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un skin</title>
    <style>
        .dropzone { border: 2px dashed #aaa; padding: 20px; margin: 10px 0; }
    </style>
    <script>
    function updateFields() {
        var type = document.getElementById('type').value;
        document.getElementById('objField').style.display = (type == 0) ? 'block' : 'none';
    }
    </script>
</head>
<body>
<h2>Ajouter un skin</h2>
<form method="post" enctype="multipart/form-data">
    <label>Mot de passe admin : <input type="password" name="password" required></label><br><br>
    <label>Type de skin :
        <select name="type" id="type" onchange="updateFields()">
            <option value="0">Flèche</option>
            <option value="1">Planète</option>
            <option value="2">Lune</option>
        </select>
    </label><br><br>
    <label>Nom du skin : <input type="text" name="name" required></label><br><br>
    <label>Prix : <input type="number" name="price" min="0" value="0"></label><br><br>
    <label>Score de déblocage : <input type="number" name="unlockingScore" min="0" value="0"></label><br><br>
    <div class="dropzone">
        <label>Image shop (.png) : <input type="file" name="shop" accept="image/png" required></label>
    </div>
    <div class="dropzone">
        <label>Image texture (.png) : <input type="file" name="texture" accept="image/png" required></label>
    </div>
    <div class="dropzone" id="objField" style="display:block">
        <label>Fichier OBJ (.obj) : <input type="file" name="obj" accept=".obj"></label>
    </div>
    <button type="submit">Ajouter</button>
</form>
<script>updateFields();</script>
</body>
</html>
