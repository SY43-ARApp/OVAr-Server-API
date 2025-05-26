<?php
// Charger les variables d'environnement depuis le fichier .env
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

// Fonction pour exécuter la mise à jour
function updateRepository($password) {
    // Vérifier le mot de passe
    if ($password !== getenv('MDP_UPDATE')) {
        return ["success" => false, "message" => "Mot de passe incorrect"];
    }

    $output = [];
    $return_var = 0;

    chdir(__DIR__);

    // 1. Récupérer les dernières modifications
    exec('git fetch origin main 2>&1', $output, $return_var);
    if ($return_var !== 0) {
        return [
            "success" => false,
            "message" => "Erreur lors du fetch",
            "details" => implode("\n", $output)
        ];
    }

    // 2. Forcer la copie exacte de la branche distante (attention, écrase les modifs locales)
    $reset_output = [];
    exec('git reset --hard origin/main 2>&1', $reset_output, $reset_return);
    if ($reset_return !== 0) {
        return [
            "success" => false,
            "message" => "Erreur lors du reset --hard",
            "details" => implode("\n", $reset_output)
        ];
    }

    // 3. Supprimer les fichiers non suivis (pour être identique à la branche)
    $clean_output = [];
    exec('git clean -fd 2>&1', $clean_output, $clean_return);
    if ($clean_return !== 0) {
        return [
            "success" => false,
            "message" => "Erreur lors du clean",
            "details" => implode("\n", $clean_output)
        ];
    }

    return [
        "success" => true,
        "message" => "Synchronisation complète réussie",
        "details" => implode("\n", array_merge($output, $reset_output, $clean_output))
    ];
}

// Initialiser la réponse
$response = null;

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    loadEnv();
    $response = updateRepository($_POST['password']);
}

// Si c'est une requête AJAX, renvoyer le résultat au format JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour du serveur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            display: none;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f8f8;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Mise à jour du serveur</h1>
    
    <form id="updateForm" method="post">
        <div class="form-group">
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Mettre à jour le serveur</button>
    </form>
    
    <div id="result" class="result">
        <div id="message"></div>
        <div id="details" class="details"></div>
    </div>
    
    <script>
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const resultDiv = document.getElementById('result');
            const messageDiv = document.getElementById('message');
            const detailsDiv = document.getElementById('details');
            
            // Envoyer la requête
            fetch('update_server.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'password=' + encodeURIComponent(password)
            })
            .then(response => response.json())
            .then(data => {
                // Afficher le résultat
                resultDiv.style.display = 'block';
                resultDiv.className = 'result ' + (data.success ? 'success' : 'error');
                messageDiv.textContent = data.message;
                
                if (data.details) {
                    detailsDiv.textContent = data.details;
                    detailsDiv.style.display = 'block';
                } else {
                    detailsDiv.style.display = 'none';
                }
            })
            .catch(error => {
                resultDiv.style.display = 'block';
                resultDiv.className = 'result error';
                messageDiv.textContent = 'Erreur de communication avec le serveur';
                console.error('Error:', error);
            });
        });
        
        <?php if ($response): ?>
        // Afficher la réponse si le formulaire a été soumis
        document.addEventListener('DOMContentLoaded', function() {
            const resultDiv = document.getElementById('result');
            const messageDiv = document.getElementById('message');
            const detailsDiv = document.getElementById('details');
            
            resultDiv.style.display = 'block';
            resultDiv.className = 'result <?php echo $response['success'] ? 'success' : 'error'; ?>';
            messageDiv.textContent = '<?php echo addslashes($response['message']); ?>';
            
            <?php if (isset($response['details'])): ?>
            detailsDiv.textContent = '<?php echo addslashes($response['details']); ?>';
            detailsDiv.style.display = 'block';
            <?php else: ?>
            detailsDiv.style.display = 'none';
            <?php endif; ?>
        });
        <?php endif; ?>
    </script>
</body>
</html>
