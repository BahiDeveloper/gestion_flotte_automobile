<?php
// Inclure la configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id_assignation = $_GET['id'];

    try {
        // Commencer une transaction pour assurer la cohérence
        $pdo->beginTransaction();

        // 1. Récupérer l'ID du véhicule affecté à l'assignation AVANT la suppression
        $sql_get_vehicule = "SELECT id_vehicule FROM deplacements WHERE id = :id_assignation";
        $stmt_get_vehicule = $pdo->prepare($sql_get_vehicule);
        $stmt_get_vehicule->execute([':id_assignation' => $id_assignation]);
        $vehicule = $stmt_get_vehicule->fetch(PDO::FETCH_ASSOC);

        // 2. Supprimer l'assignation
        $sql_delete = "DELETE FROM deplacements WHERE id = :id_assignation";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute([':id_assignation' => $id_assignation]);

        if ($vehicule) {
            // 3. Mettre à jour l'état du véhicule en "Disponible"
            $sql_update_vehicule = "UPDATE vehicules SET etat = 'Disponible' WHERE id = :id_vehicule";
            $stmt_update_vehicule = $pdo->prepare($sql_update_vehicule);
            $stmt_update_vehicule->execute([':id_vehicule' => $vehicule['id_vehicule']]);
        }

        // 4. Valider la transaction
        $pdo->commit();

        // Retourner une réponse JSON de succès
        echo json_encode([
            'success' => true,
            'message' => 'Demande supprimée et état du véhicule mis à jour avec succès.'
        ]);
    } catch (PDOException $e) {
        // Si une erreur se produit, annuler la transaction
        $pdo->rollBack();

        // Retourner une réponse JSON d'erreur
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la suppression de la demande ou de la mise à jour du véhicule : ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Aucune demande à supprimer.'
    ]);
}
?>