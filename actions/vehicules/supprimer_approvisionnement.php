<?php
// Inclure le fichier de configuration de la base de données
include_once("../../database" . DIRECTORY_SEPARATOR . "config.php");

// Vérification de la session et des autorisations
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'administrateur' && $_SESSION['role'] !== 'gestionnaire')) {
    // Rediriger si l'utilisateur n'est pas autorisé
    header('Location: ../../index.php');
    exit();
}

// Vérifier si l'ID de l'approvisionnement est fourni
if (isset($_POST['id_approvisionnement']) && !empty($_POST['id_approvisionnement'])) {
    $id_approvisionnement = intval($_POST['id_approvisionnement']);
    
    try {
        // Préparer la requête de suppression
        $stmt = $pdo->prepare("DELETE FROM approvisionnements_carburant WHERE id_approvisionnement = :id");
        $stmt->bindParam(':id', $id_approvisionnement, PDO::PARAM_INT);
        
        // Exécuter la requête
        if ($stmt->execute()) {
            // Suppression réussie
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'L\'approvisionnement a été supprimé avec succès.'
            ];
        } else {
            // Échec de la suppression
            $_SESSION['alert'] = [
                'type' => 'danger',
                'message' => 'Erreur lors de la suppression de l\'approvisionnement.'
            ];
        }
    } catch (PDOException $e) {
        // Erreur de base de données
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'Erreur de base de données: ' . $e->getMessage()
        ];
    }
} else {
    // ID d'approvisionnement non fourni
    $_SESSION['alert'] = [
        'type' => 'warning',
        'message' => 'Aucun approvisionnement spécifié pour la suppression.'
    ];
}

// Rediriger vers la page de gestion des véhicules
header('Location: ../../gestion_vehicules.php?tab=approvisionnements');
exit();