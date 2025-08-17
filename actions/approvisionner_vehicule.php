<?php
require_once '../database/config.php'; // Inclusion de la connexion à la base de données

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = $_POST['vehicleId'];
    $fuelAmount = $_POST['fuelAmount'];
    $fuelCost = $_POST['fuelCost'];

    if (!$vehicleId || !$fuelAmount || !$fuelCost) {
        echo json_encode(['status' => 'error', 'message' => 'Données invalides.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Vérifier si le véhicule existe
        $stmt = $pdo->prepare("SELECT * FROM vehicules WHERE id = ?");
        $stmt->execute([$vehicleId]);
        $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicule) {
            echo json_encode(['status' => 'error', 'message' => 'Véhicule introuvable.']);
            exit;
        }

        // Insérer l'approvisionnement
        $stmt = $pdo->prepare("INSERT INTO approvisionnements (id_vehicule, quantite_litres, cout_total, date_approvisionnement) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$vehicleId, $fuelAmount, $fuelCost]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Approvisionnement enregistré avec succès.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'approvisionnement: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée.']);
}
?>