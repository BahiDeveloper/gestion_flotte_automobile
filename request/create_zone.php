<?php
// request/create_zone.php
require_once("../database/config.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$zoneName = isset($data['zone_name']) ? $data['zone_name'] : '';

try {
    // Vérifier si la zone existe déjà
    $stmt = $connexion->prepare("SELECT id_zone FROM zones WHERE nom_zone = ?");
    $stmt->execute([$zoneName]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cette zone existe déjà'
        ]);
        exit;
    }
    
    // Créer la nouvelle zone
    $stmt = $connexion->prepare("INSERT INTO zones (nom_zone, statut) VALUES (?, 'active')");
    $stmt->execute([$zoneName]);
    
    echo json_encode([
        'success' => true,
        'id_zone' => $connexion->lastInsertId()
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}