<?php
/**
 * API pour préparer le démarrage d'une course
 * Récupère les informations nécessaires pour la modal de kilométrage
 */

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Inclusion du fichier de configuration
require_once "../database/config.php";

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

// Récupérer les données envoyées
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si l'ID de réservation est fourni
if (!isset($data['id_reservation']) || empty($data['id_reservation'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de réservation non fourni'
    ]);
    exit;
}

$id_reservation = intval($data['id_reservation']);
$idUtilisateur = $_SESSION['id_utilisateur'];

try {
    // Récupérer les informations de la réservation et du véhicule
    $query = "SELECT 
                r.id_reservation,
                r.statut,
                r.id_vehicule,
                r.id_chauffeur,
                v.immatriculation,
                v.marque,
                v.modele,
                v.kilometrage_actuel,
                v.type_vehicule,
                c.nom as chauffeur_nom,
                c.prenoms as chauffeur_prenoms
            FROM 
                reservations_vehicules r
            JOIN vehicules v ON r.id_vehicule = v.id_vehicule
            LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
            WHERE 
                r.id_reservation = :id_reservation";
    
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
    
    // Vérifier si la réservation est au statut validée
    if ($reservation['statut'] !== 'validee') {
        echo json_encode([
            'success' => false,
            'message' => 'Cette réservation ne peut pas être démarrée (statut actuel: ' . $reservation['statut'] . ')'
        ]);
        exit;
    }
    
    // Vérifier si un véhicule est assigné
    if (empty($reservation['id_vehicule'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Aucun véhicule n\'est assigné à cette réservation'
        ]);
        exit;
    }
    
    // Préparer les informations du véhicule pour la réponse
    $vehicule = [
        'id_vehicule' => $reservation['id_vehicule'],
        'immatriculation' => $reservation['immatriculation'],
        'marque' => $reservation['marque'],
        'modele' => $reservation['modele'],
        'kilometrage_actuel' => $reservation['kilometrage_actuel'],
        'type_vehicule' => $reservation['type_vehicule']
    ];
    
    // Réponse avec les informations nécessaires pour l'étape suivante
    echo json_encode([
        'success' => true,
        'vehicule' => $vehicule,
        'chauffeur' => [
            'id_chauffeur' => $reservation['id_chauffeur'],
            'nom' => $reservation['chauffeur_nom'],
            'prenoms' => $reservation['chauffeur_prenoms']
        ],
        'message' => 'Prêt à démarrer la course'
    ]);
    
} catch (PDOException $e) {
    // Gérer les erreurs
    error_log("Erreur dans debuter-course.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la préparation du démarrage: ' . $e->getMessage()
    ]);
}