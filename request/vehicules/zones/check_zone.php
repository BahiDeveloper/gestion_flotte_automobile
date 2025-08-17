<?php
include_once("../../../database/config.php");

header("Content-Type: application/json");

if (!isset($_GET['zone']) || empty(trim($_GET['zone']))) {
    http_response_code(400);
    echo json_encode(["error" => "Nom de zone requis"]);
    exit;
}

$zone = trim($_GET['zone']);

try {
    $sql = "SELECT id FROM zone_vehicules WHERE nom_zone = :zone";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':zone' => $zone]);
    $zoneData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($zoneData) {
        echo json_encode(["exists" => true, "id_zone" => $zoneData['id_zone']]);
    } else {
        echo json_encode(["exists" => false]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la vérification de la zone"]);
}
?>
