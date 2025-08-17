<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si les données ont été envoyées via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer les données du formulaire
    $assignation_id = $_POST['assignation_id'];
    $chauffeur_id = $_POST['chauffeur'];

    // Valider les données
    if (empty($assignation_id) || empty($chauffeur_id)) {
        die("Erreur : L'assignation ou le chauffeur n'ont pas été sélectionnés.");
    }

    try {
        // Activer le mode transaction
        $pdo->beginTransaction();

        // Mettre à jour l'assignation avec l'ID du chauffeur
        $sql = "UPDATE deplacements 
                SET id_chauffeur = :chauffeur_id,
                    etat_course = 'en_cours'
                WHERE id = :assignation_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':chauffeur_id', $chauffeur_id, PDO::PARAM_INT);
        $stmt->bindParam(':assignation_id', $assignation_id, PDO::PARAM_INT);
        $stmt->execute();

        // Mettre à jour le statut du chauffeur (le rendre occupé)
        $sql = "UPDATE chauffeurs 
                SET disponibilite = 'Occupé'
                WHERE id = :chauffeur_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':chauffeur_id', $chauffeur_id, PDO::PARAM_INT);
        $stmt->execute();

        // Valider la transaction
        $pdo->commit();

        // Redirection après succès
        header("Location: ../gestion_vehicules.php?success_demande_assignation=1");
        exit();
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        die("Erreur lors de l'assignation : " . $e->getMessage());
    }
} else {
    // Redirection si la méthode n'est pas POST
    header("Location: ../gestion_vehicules.php?error_assignation=6");
    exit();
}
?>