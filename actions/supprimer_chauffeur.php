<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID du chauffeur est passé en paramètre dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // Préparer la requête SQL pour supprimer le chauffeur
    $sql = "DELETE FROM chauffeurs WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    // Exécuter la requête avec l'ID du chauffeur
    if ($stmt->execute([$id])) {
        // Rediriger vers la page de gestion des chauffeurs avec un message de succès
        header("Location: ../gestion_chauffeurs.php?success_chauffeur_delet=1");
        exit();
    } else {
        // Rediriger avec un message d'erreur
        header("Location: ../gestion_chauffeurs.php?error_chauffeur_delet=1");
        exit();
    }
} else {
    // Rediriger si l'ID n'est pas fourni
    header("Location: ../gestion_chauffeurs.php");
    exit();
}
?>