<?php
// annuler_course.php
include_once("../database/config.php");

header('Content-Type: application/json'); // Indiquer que la réponse est en JSON

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $assignation_id = $_GET['id'];

    try {
        // Récupérer l'ID du véhicule et du chauffeur associé à l'assignation
        $assignationQuery = "SELECT id_vehicule, id_chauffeur FROM deplacements WHERE id = :assignation_id";
        $stmt = $pdo->prepare($assignationQuery);
        $stmt->execute(['assignation_id' => $assignation_id]);
        $assignation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignation) {
            throw new Exception("Aucune assignation trouvée avec cet ID.");
        }

        // Supprimer l'assignation
        $deleteQuery = "DELETE FROM deplacements WHERE id = :assignation_id";
        $stmt = $pdo->prepare($deleteQuery);
        $stmt->execute(['assignation_id' => $assignation_id]);

        // Mettre à jour l'état du véhicule (ex: 'Disponible')
        $sql = "UPDATE vehicules SET etat = 'Disponible' WHERE id = :id_vehicule";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_vehicule', $assignation['id_vehicule'], PDO::PARAM_INT);
        $stmt->execute();

        // Mettre à jour la disponibilité du chauffeur (ex: 'Disponible')
        $sql = "UPDATE chauffeurs SET disponibilite = 'Disponible' WHERE id = :id_chauffeur";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_chauffeur', $assignation['id_chauffeur'], PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Course supprimée avec succès.']);
        exit();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}
?>