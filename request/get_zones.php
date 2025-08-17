<?php
include_once("../database/config.php");

header("Content-Type: application/json");

if (!isset($_GET['term']) || empty(trim($_GET['term']))) {
    echo json_encode([]);
    exit;
}

$term = trim($_GET['term']);

try {
    $sql = "SELECT id, nom_zone FROM zone_vehicules WHERE nom_zone LIKE :term LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':term' => "%$term%"]);
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($zones as $zone) {
        $result[] = ["value" => $zone['id'], "label" => $zone['nom_zone']];
    }
    error_log(print_r($result, true)); // Log des résultats
    
    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la récupération des zones"]);
}
?>
