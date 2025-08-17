<?php
include_once("../database/config.php");
header("Content-Type: application/json");
try {
    // Récupérer toutes les zones depuis la base de données
    $sql = "SELECT id, nom_zone FROM zone_vehicules ORDER BY nom_zone ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Un seul json_encode
    echo json_encode($zones);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la récupération des zones"]);
}
?>