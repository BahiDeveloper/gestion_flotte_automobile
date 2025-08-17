<?php
/**
 * API pour récupérer les informations d'un véhicule lié à une réservation
 * Utilisé pour préparer la finalisation d'une course
 */

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Inclusion du fichier de configuration
require_once "../database/config.php";

// Vérifier si l'ID de réservation est fourni
if (!isset($_GET['id_reservation']) || empty($_GET['id_reservation'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de réservation non fourni'
    ]);
    exit;
}

$id_reservation = intval($_GET['id_reservation']);

try {
    // Récupérer les informations du véhicule et de la réservation 
    // Sans faire référence à date_depart_effective
    $query = "SELECT 
                r.id_reservation,
                r.id_vehicule,
                r.statut,
                r.date_depart,
                r.km_depart,
                v.immatriculation,
                v.marque,
                v.modele,
                v.kilometrage_actuel,
                v.type_vehicule,
                v.type_carburant
            FROM 
                reservations_vehicules r
            JOIN vehicules v ON r.id_vehicule = v.id_vehicule
            WHERE 
                r.id_reservation = :id_reservation";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Réservation ou véhicule non trouvé'
        ]);
        exit;
    }
    
    // Vérifier si la réservation est en cours
    if ($result['statut'] !== 'en_cours') {
        echo json_encode([
            'success' => false,
            'message' => 'Cette réservation n\'est pas en cours (statut actuel: ' . $result['statut'] . ')'
        ]);
        exit;
    }
    
    // Calculer le temps depuis la date de départ prévue plutôt que effective
    $dateDepart = new DateTime($result['date_depart']);
    $now = new DateTime();
    $dureeCourse = $now->getTimestamp() - $dateDepart->getTimestamp();
    $dureeHeures = floor($dureeCourse / 3600);
    $dureeMinutes = floor(($dureeCourse % 3600) / 60);
    
    // Préparer les informations du véhicule pour la réponse
    $vehicule = [
        'id_vehicule' => $result['id_vehicule'],
        'immatriculation' => $result['immatriculation'],
        'marque' => $result['marque'],
        'modele' => $result['modele'],
        'type_vehicule' => $result['type_vehicule'],
        'type_carburant' => $result['type_carburant'] ?? 'Non spécifié',
        'km_depart' => $result['km_depart'],
        'kilometrage_actuel' => $result['kilometrage_actuel'],
        'duree_course' => [
            'heures' => $dureeHeures,
            'minutes' => $dureeMinutes,
            'secondes' => $dureeCourse % 60,
            'total_secondes' => $dureeCourse
        ],
        'date_depart_prevue' => $result['date_depart']
    ];
    
    echo json_encode([
        'success' => true,
        'vehicule' => $vehicule
    ]);
    
} catch (PDOException $e) {
    // Gérer les erreurs
    error_log("Erreur dans info-vehicule-reservation.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des informations: ' . $e->getMessage()
    ]);
}