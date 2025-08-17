<?php
include_once("../database/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintenance_id = $_POST['maintenance_id'];
    $date_fin = $_POST['date_fin'];
    $cout = $_POST['cout'];

    try {
        if (empty($maintenance_id) || empty($date_fin) || empty($cout)) {
            throw new Exception("Tous les champs doivent être remplis.");
        }

        $vehiculeQuery = "SELECT id_vehicule FROM maintenance WHERE id = :maintenance_id";
        $stmt = $pdo->prepare($vehiculeQuery);
        $stmt->execute(['maintenance_id' => $maintenance_id]);
        $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicule) {
            throw new Exception("Aucune maintenance trouvée avec cet ID.");
        }

        $updateQuery = "UPDATE maintenance SET date_fin = :date_fin, cout = :cout WHERE id = :maintenance_id";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([
            'date_fin' => $date_fin,
            'cout' => $cout,
            'maintenance_id' => $maintenance_id
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Aucune mise à jour effectuée pour cette maintenance.");
        }

        $updateVehiculeQuery = "UPDATE vehicules SET etat = 'Disponible' WHERE id = :id_vehicule";
        $stmt = $pdo->prepare($updateVehiculeQuery);
        $stmt->execute(['id_vehicule' => $vehicule['id_vehicule']]);

        // Retourner une réponse JSON en cas de succès
        echo json_encode(['success' => true, 'message' => 'Maintenance terminée avec succès.']);
        exit();

    } catch (Exception $e) {
        // Retourner une réponse JSON en cas d'erreur
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}
?>