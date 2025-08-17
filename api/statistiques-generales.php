<?php
header('Content-Type: application/json');

// Sécurisation de l'accès
session_start();
include_once('../database/config.php');

// Vérification des permissions
if (!isset($_SESSION['id_utilisateur']) || 
    !in_array($_SESSION['role'], ['administrateur', 'gestionnaire'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

try {
    // Statistiques des véhicules
    $vehicules_query = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as disponibles,
            SUM(CASE WHEN statut = 'maintenance' THEN 1 ELSE 0 END) as maintenance
        FROM vehicules
    ");
    $vehicules = $vehicules_query->fetch(PDO::FETCH_ASSOC);

    // Statistiques des chauffeurs
    $chauffeurs_query = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as en_course
        FROM chauffeurs
    ");
    $chauffeurs = $chauffeurs_query->fetch(PDO::FETCH_ASSOC);

    // Statistiques des documents
    $documents_query = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN statut = 'valide' THEN 1 ELSE 0 END) as valides,
            SUM(CASE WHEN statut = 'a_renouveler' THEN 1 ELSE 0 END) as a_renouveler
        FROM documents_administratifs
    ");
    $documents = $documents_query->fetch(PDO::FETCH_ASSOC);

    // Statistiques financières
    $finances_query = $pdo->query("
        SELECT 
            COALESCE(SUM(prix_total), 0) as carburant,
            COALESCE((SELECT SUM(cout) FROM maintenances WHERE statut = 'terminee'), 0) as maintenance
        FROM approvisionnements_carburant
    ");
    $finances = $finances_query->fetch(PDO::FETCH_ASSOC);

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'vehicules' => $vehicules,
        'chauffeurs' => $chauffeurs,
        'documents' => $documents,
        'finances' => $finances
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}