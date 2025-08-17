<?php
// delete_document.php

// Inclure la configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID du document est passé en paramètre
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Préparer la requête de suppression
    $sql = "DELETE FROM documents WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Exécuter la requête
    if ($stmt->execute()) {
        // Rediriger vers la page des documents avec un message de succès
        header("Location:../gestion_documents.php?success_delete=1");
        exit();
    } else {
        // Rediriger avec un message d'erreur
        header("Location:../gestion_documents.php?error_delete=1");
        exit();
    }
} else {
    // Rediriger si l'ID n'est pas fourni
    // error=ID du document manquant 
    header("Location: gestion_documents.php?error=1");
    exit();
}
?>