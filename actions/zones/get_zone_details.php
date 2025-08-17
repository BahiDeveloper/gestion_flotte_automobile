<?php
// Démarrer la session
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['id_utilisateur'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentification requise']);
    exit;
}

// Vérifier les permissions
require_once '../../includes/RoleAccess.php';
$roleAccess = new RoleAccess($_SESSION['role']);
if (!$roleAccess->hasPermission('tracking')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Permissions insuffisantes']);
    exit;
}

// Inclure le fichier de configuration de la base de données
require_once '../../database/config.php';

// Vérifier qu'un ID a été fourni
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de zone invalide']);
    exit;
}

try {
    // Récupérer les informations de la zone
    $stmt = $pdo->prepare("SELECT * FROM zone_vehicules WHERE id = ?");
    $stmt->execute([$id]);
    $zone = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$zone) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Zone non trouvée']);
        exit;
    }

    // Récupérer les statistiques de véhicules pour cette zone
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_vehicules,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as vehicules_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as vehicules_en_course,
            SUM(CASE WHEN statut = 'maintenance' THEN 1 ELSE 0 END) as vehicules_maintenance,
            SUM(CASE WHEN statut = 'hors_service' THEN 1 ELSE 0 END) as vehicules_hors_service
        FROM vehicules
        WHERE id_zone = ?
    ");
    $stmt->execute([$id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer la liste des véhicules dans cette zone
    $stmt = $pdo->prepare("
        SELECT id_vehicule, marque, modele, immatriculation, type_vehicule, statut, kilometrage_actuel
        FROM vehicules
        WHERE id_zone = ?
        ORDER BY marque, modele
    ");
    $stmt->execute([$id]);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Préparer la réponse
    $response = [
        'success' => true,
        'zone' => $zone,
        'stats' => $stats,
        'vehicles' => $vehicles
    ];

    // Envoyer la réponse au format JSON
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}