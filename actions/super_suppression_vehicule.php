<?php
// annuler_maintenance.php
include_once("../database/config.php");

header('Content-Type: application/json'); // Indiquer que la réponse est en JSON

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $maintenance_id = $_GET['id'];

    try {
        // Récupérer l'ID du véhicule associé à la maintenance
        $vehiculeQuery = "SELECT id_vehicule FROM maintenance WHERE id = :maintenance_id";
        $stmt = $pdo->prepare($vehiculeQuery);
        $stmt->execute(['maintenance_id' => $maintenance_id]);
        $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicule) {
            throw new Exception("Aucune maintenance trouvée avec cet ID.");
        }

        // Supprimer la maintenance
        $deleteQuery = "DELETE FROM maintenance WHERE id = :maintenance_id";
        $stmt = $pdo->prepare($deleteQuery);
        $stmt->execute(['maintenance_id' => $maintenance_id]);

        // Mettre à jour l'état du véhicule à "Disponible"
        $updateVehiculeQuery = "UPDATE vehicules SET etat = 'Disponible' WHERE id = :id_vehicule";
        $stmt = $pdo->prepare($updateVehiculeQuery);
        $stmt->execute(['id_vehicule' => $vehicule['id_vehicule']]);

        echo json_encode(['success' => true, 'message' => 'Maintenance annulée avec succès.']);
        exit();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}
?>