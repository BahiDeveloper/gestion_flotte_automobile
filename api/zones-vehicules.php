<?php
// api/zones-vehicules.php
header('Content-Type: application/json');
require_once '../database/config.php';

try {
    // Requête pour récupérer toutes les zones
    $stmt = $pdo->prepare("
        SELECT id, nom_zone, description 
        FROM zone_vehicules 
        ORDER BY nom_zone ASC
    ");
    
    $stmt->execute();
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($zones);
    
} catch (PDOException $e) {
    error_log('Erreur PDO : ' . $e->getMessage());
    echo json_encode(['error' => 'Erreur lors de la récupération des zones de véhicules']);
}
?>