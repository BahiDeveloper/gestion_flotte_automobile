<?php
session_start();

// Inclure le fichier de configuration de la base de données
include_once("../../database/config.php");

// Vérifier l'authentification et les droits d'accès
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}

// Récupérer l'ID de l'utilisateur à supprimer
$id_utilisateur = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier que l'ID est valide
if ($id_utilisateur <= 0) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'message' => 'ID d\'utilisateur invalide']));
}

// Vérifier que l'utilisateur ne tente pas de se supprimer lui-même
if ($id_utilisateur === $_SESSION['id_utilisateur']) {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['success' => false, 'message' => 'Vous ne pouvez pas vous supprimer vous-même']));
}

// Commencer une transaction
$pdo->beginTransaction();

try {
    // Récupérer les informations de l'utilisateur avant suppression
    $stmt_user_info = $pdo->prepare("SELECT nom, prenom, email FROM utilisateurs WHERE id_utilisateur = ?");
    $stmt_user_info->execute([$id_utilisateur]);
    $utilisateur = $stmt_user_info->fetch(PDO::FETCH_ASSOC);

    if (!$utilisateur) {
        throw new Exception("Utilisateur non trouvé");
    }

    // Supprimer les enregistrements liés dans d'autres tables
    // Note : Ajustez ces requêtes selon votre schéma de base de données
    $tables_a_nettoyer = [
        'journal_activites',
        // Ajoutez d'autres tables si nécessaire
    ];

    foreach ($tables_a_nettoyer as $table) {
        $stmt_clean = $pdo->prepare("DELETE FROM {$table} WHERE id_utilisateur = ?");
        $stmt_clean->execute([$id_utilisateur]);
    }

    // Supprimer l'utilisateur
    $stmt_delete = $pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = ?");
    $stmt_delete->execute([$id_utilisateur]);

    // Vérifier si la suppression a réussi
    if ($stmt_delete->rowCount() === 0) {
        throw new Exception("Impossible de supprimer l'utilisateur");
    }

    // Journaliser l'activité
    $stmt_journal = $pdo->prepare("
        INSERT INTO journal_activites 
        (id_utilisateur, type_activite, description, date_activite, ip_address) 
        VALUES 
        (?, 'suppression_utilisateur', ?, NOW(), ?)
    ");
    $stmt_journal->execute([
        $_SESSION['id_utilisateur'],
        "Suppression de l'utilisateur {$utilisateur['nom']} {$utilisateur['prenom']} ({$utilisateur['email']})",
        $_SERVER['REMOTE_ADDR']
    ]);

    // Valider la transaction
    $pdo->commit();

    // Répondre avec succès
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur supprimé avec succès'
    ]);
    exit;

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();

    // Répondre avec l'erreur
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}