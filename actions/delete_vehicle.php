<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID du véhicule est passé en paramètre
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $vehicleId = $_GET['id'];

    try {
        // Supprimer le véhicule de la base de données
        $query = "DELETE FROM vehicules WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $vehicleId]);

        // Rediriger vers la page de gestion des véhicules avec un message de succès
        header('Location: ../gestion_vehicules.php?success_delete=1');
        exit();
    } catch (PDOException $e) {
        // En cas d'erreur, rediriger avec un message d'erreur
        header('Location: ../gestion_vehicules.php?error_delete=1');
        exit();
    }
} else {
    // Si l'ID n'est pas valide, rediriger avec un message d'erreur
    header('Location: ../gestion_vehicules.php?error=1');
    exit();
}
?>