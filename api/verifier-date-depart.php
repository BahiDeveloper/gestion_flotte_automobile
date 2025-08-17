<?php
/**
 * API pour vérifier si la date de départ prévue est atteinte
 * Utilisé pour avertir l'utilisateur s'il démarre une course avant la date prévue
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
    // Récupérer la date de départ prévue
    $query = "SELECT date_depart, statut FROM reservations_vehicules WHERE id_reservation = :id_reservation";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();
    
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        echo json_encode([
            'success' => false,
            'message' => 'Réservation non trouvée'
        ]);
        exit;
    }
    
    // Vérifier le statut (seules les réservations validées peuvent être démarrées)
    if ($reservation['statut'] !== 'validee') {
        echo json_encode([
            'success' => false,
            'message' => 'Cette réservation ne peut pas être démarrée (statut actuel: ' . $reservation['statut'] . ')'
        ]);
        exit;
    }
    
    // Comparer la date actuelle avec la date de départ prévue
    $datePrevue = new DateTime($reservation['date_depart']);
    $dateActuelle = new DateTime();
    $interval = $dateActuelle->diff($datePrevue);
    
    // Considérer la date comme atteinte si on est dans un intervalle de 15 minutes avant
    $dateAtteinte = ($interval->invert === 1) || // Si la date prévue est dans le passé
                   ($interval->days === 0 && $interval->h === 0 && $interval->i <= 15); // Ou si on est à moins de 15 minutes
    
    echo json_encode([
        'success' => true,
        'date_atteinte' => $dateAtteinte,
        'date_prevue' => $reservation['date_depart'],
        'date_actuelle' => $dateActuelle->format('Y-m-d H:i:s'),
        'difference_minutes' => ($interval->invert === 0) ? 
            $interval->days * 24 * 60 + $interval->h * 60 + $interval->i :
            -($interval->days * 24 * 60 + $interval->h * 60 + $interval->i)
    ]);
    
} catch (PDOException $e) {
    // Gérer les erreurs
    error_log("Erreur dans verifier-date-depart.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la vérification de la date: ' . $e->getMessage()
    ]);
}