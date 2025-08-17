<?php
include_once("../../../database/config.php");

header("Content-Type: application/json");

if (!isset($_POST['zone']) || empty(trim($_POST['zone']))) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Nom de zone requis"]);
    exit;
}

$zone = trim($_POST['zone']);

try {
    // Vérifier si la zone existe déjà
    $sql = "SELECT COUNT(*) FROM zone_vehicules WHERE nom_zone = :zone";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':zone' => $zone]);
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cette zone existe déjà."]);
        exit;
    }

    // Insérer la nouvelle zone
    $sql = "INSERT INTO zone_vehicules (nom_zone) VALUES (:zone)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':zone' => $zone]);

    echo json_encode(["success" => true, "message" => "Zone créée avec succès"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de l'insertion"]);
}
?>
