<?php
require_once '../database/config.php'; // Inclusion de la connexion à la base de données

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $vehicleId = $_GET['id'];

    // Vérifier si le véhicule existe
    $stmt = $pdo->prepare("SELECT type_carburant FROM vehicules WHERE id = ?");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        echo json_encode(['status' => 'success', 'fuelType' => $vehicle['type_carburant']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Véhicule introuvable.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID du véhicule manquant.']);
}
?>