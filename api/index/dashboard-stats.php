<?php
// api/index/dashboard-stats.php
// API pour fournir les données du tableau de bord en temps réel

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Inclusion du fichier de configuration
require_once "../../database/config.php";

// Démarrage de la session pour vérifier l'authentification
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non authentifié'
    ]);
    exit;
}

try {
    // Statistiques des véhicules
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_vehicules,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as vehicules_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as vehicules_en_course,
            SUM(CASE WHEN statut = 'maintenance' THEN 1 ELSE 0 END) as vehicules_maintenance,
            SUM(CASE WHEN statut = 'hors_service' THEN 1 ELSE 0 END) as vehicules_hors_service
        FROM vehicules
    ");
    $stats_vehicules = $stmt->fetch(PDO::FETCH_ASSOC);

    // Statistiques des chauffeurs
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_chauffeurs,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as chauffeurs_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as chauffeurs_en_course,
            SUM(CASE WHEN statut = 'conge' THEN 1 ELSE 0 END) as chauffeurs_en_conge
        FROM chauffeurs
    ");
    $stats_chauffeurs = $stmt->fetch(PDO::FETCH_ASSOC);

    // Documents à renouveler bientôt
    $stmt = $pdo->query("
        SELECT d.*, 
            v.marque, v.modele, v.immatriculation,
            DATEDIFF(d.date_expiration, CURDATE()) as jours_restants
        FROM documents_administratifs d
        LEFT JOIN vehicules v ON d.id_vehicule = v.id_vehicule
        WHERE DATEDIFF(d.date_expiration, CURDATE()) <= 60
            AND d.statut != 'expire'
        ORDER BY jours_restants ASC
        LIMIT 5
    ");
    $documents_alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Réservations en attente
    $stmt = $pdo->query("
        SELECT r.*, 
            v.marque, v.modele, 
            CONCAT(c.nom, ' ', c.prenoms) as nom_chauffeur
        FROM reservations_vehicules r
        LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
        LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
        WHERE r.statut = 'en_attente'
        ORDER BY r.date_depart ASC
        LIMIT 5
    ");
    $reservations_attente = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Maintenances en cours
    $stmt = $pdo->query("
        SELECT m.*, 
            v.marque, v.modele, v.immatriculation
        FROM maintenances m
        JOIN vehicules v ON m.id_vehicule = v.id_vehicule
        WHERE m.statut = 'en_cours'
        ORDER BY m.date_debut ASC
        LIMIT 5
    ");
    $maintenances_en_cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner toutes les données en format JSON
    echo json_encode([
        'success' => true,
        'stats_vehicules' => $stats_vehicules,
        'stats_chauffeurs' => $stats_chauffeurs,
        'documents_alerts' => $documents_alerts,
        'reservations_attente' => $reservations_attente,
        'maintenances_en_cours' => $maintenances_en_cours,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    // Gérer les erreurs
    error_log("Erreur dans dashboard-stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
    ]);
}