<?php
require 'DB.php';

header('Content-Type: application/json');

function calculate_money($levels_passed, $base = 1, $scale = 1.5, $offset = 1) {
    $total_money = 0;

    for ($level = 1; $level <= $levels_passed; $level++) {
        echo "Level $level:";
        // Calcule l'argent gagné pour ce niveau, avec une formule logarithmique
        $money_for_level = (int) floor($base + log($level + $offset, $scale));
        echo " Money = $money_for_level</br>";
        $total_money += $money_for_level;
    }

    return $total_money;
}

// TODO: Check if uuid is valid
$uuid = $_GET['user_id'] ?? $_GET['uuid'] ?? '';
$score = intval($_GET['score'] ?? 0);
$arrows_thrown = intval($_GET['arrows_thrown'] ?? 0);
$planets_hit = intval($_GET['planets_hit'] ?? 0);
$levels_passed = intval($_GET['levels_passed'] ?? 0);


// Insert score into database
$q = $mysqli->prepare("INSERT INTO score (uuid, score, arrows_thrown, planets_hit, levels_passed) VALUES (?, ?, ?, ?, ?)");
$q->bind_param("siiii", $uuid, $score, $arrows_thrown, $planets_hit, $levels_passed);
$q->execute();

// Update bestscore if necessary
$update = $mysqli->prepare("UPDATE user SET bestscore = ? WHERE uuid = ? AND bestscore < ?");
$update->bind_param("isi", $score, $uuid, $score);
$update->execute();

// Update money thanks to levels_passed
$money = calculate_money($levels_passed);
$updateMoney = $mysqli->prepare("UPDATE user SET money = money + ? WHERE uuid = ?");
$updateMoney->bind_param("is", $money, $uuid);
$updateMoney->execute();

if ($q->affected_rows > 0 && $update->affected_rows > 0 && $updateMoney->affected_rows > 0)
{
    echo json_encode([
        "success" => true,
        "money" => $money,
        "message" => "SUCCESS"
    ]);
}
else
{
    echo json_encode([
        "success" => false,
        "money" => 0,
        "message" => "ERROR"
    ]);
}
?>
