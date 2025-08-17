<?php
// Fichier AJAX pour récupérer les statistiques du tableau de bord

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier l'authentification
if (!isset($_SESSION['id_utilisateur'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Utilisateur non authentifié']);
    exit;
}

// Inclure le fichier de configuration de la base de données
require_once('../database/config.php');

try {
    // Statistiques des véhicules
    $stmt_vehicules = $pdo->query("
        SELECT 
            COUNT(*) as total_vehicules,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as vehicules_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as vehicules_en_course,
            SUM(CASE WHEN statut = 'maintenance' THEN 1 ELSE 0 END) as vehicules_maintenance,
            SUM(CASE WHEN statut = 'hors_service' THEN 1 ELSE 0 END) as vehicules_hors_service
        FROM vehicules
    ");
    $stats_vehicules = $stmt_vehicules->fetch(PDO::FETCH_ASSOC);

    // Statistiques des chauffeurs
    $stmt_chauffeurs = $pdo->query("
        SELECT 
            COUNT(*) as total_chauffeurs,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as chauffeurs_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as chauffeurs_en_course,
            SUM(CASE WHEN statut = 'conge' THEN 1 ELSE 0 END) as chauffeurs_en_conge,
            SUM(CASE WHEN statut = 'indisponible' THEN 1 ELSE 0 END) as chauffeurs_indisponibles
        FROM chauffeurs
    ");
    $stats_chauffeurs = $stmt_chauffeurs->fetch(PDO::FETCH_ASSOC);

    // Statistiques des maintenances
    $stmt_maintenance = $pdo->query("
        SELECT 
            COUNT(*) as total_maintenances,
            SUM(CASE WHEN statut = 'planifiee' THEN 1 ELSE 0 END) as maintenances_planifiees,
            SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as maintenances_en_cours,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as maintenances_terminees,
            SUM(CASE WHEN statut = 'annulee' THEN 1 ELSE 0 END) as maintenances_annulees
        FROM maintenances
        WHERE date_debut >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stats_maintenance = $stmt_maintenance->fetch(PDO::FETCH_ASSOC);

    // Statistiques des réservations
    $stmt_reservations = $pdo->query("
        SELECT 
            COUNT(*) as total_reservations,
            SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as reservations_en_attente,
            SUM(CASE WHEN statut = 'validee' THEN 1 ELSE 0 END) as reservations_validees,
            SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as reservations_en_cours,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as reservations_terminees,
            SUM(CASE WHEN statut = 'annulee' THEN 1 ELSE 0 END) as reservations_annulees
        FROM reservations_vehicules
        WHERE date_demande >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stats_reservations = $stmt_reservations->fetch(PDO::FETCH_ASSOC);

    // Préparer les données de réponse
    $response = [
        'vehicules' => [
            'total' => (int)$stats_vehicules['total_vehicules'],
            'disponibles' => (int)$stats_vehicules['vehicules_disponibles'],
            'en_course' => (int)$stats_vehicules['vehicules_en_course'],
            'maintenance' => (int)$stats_vehicules['vehicules_maintenance'],
            'hors_service' => (int)$stats_vehicules['vehicules_hors_service']
        ],
        'chauffeurs' => [
            'total' => (int)$stats_chauffeurs['total_chauffeurs'],
            'disponibles' => (int)$stats_chauffeurs['chauffeurs_disponibles'],
            'en_course' => (int)$stats_chauffeurs['chauffeurs_en_course'],
            'conge' => (int)$stats_chauffeurs['chauffeurs_en_conge'],
            'indisponibles' => (int)$stats_chauffeurs['chauffeurs_indisponibles']
        ],
        'maintenance' => [
            'total' => (int)$stats_maintenance['total_maintenances'],
            'planifiees' => (int)$stats_maintenance['maintenances_planifiees'],
            'en_cours' => (int)$stats_maintenance['maintenances_en_cours'],
            'terminees' => (int)$stats_maintenance['maintenances_terminees'],
            'annulees' => (int)$stats_maintenance['maintenances_annulees']
        ],
        'reservations' => [
            'total' => (int)$stats_reservations['total_reservations'],
            'en_attente' => (int)$stats_reservations['reservations_en_attente'],
            'validees' => (int)$stats_reservations['reservations_validees'],
            'en_cours' => (int)$stats_reservations['reservations_en_cours'],
            'terminees' => (int)$stats_reservations['reservations_terminees'],
            'annulees' => (int)$stats_reservations['reservations_annulees']
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    // En cas d'erreur, renvoyer un message d'erreur
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}