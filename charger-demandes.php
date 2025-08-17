<?php
// Activation du rapport d'erreurs complet
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Inclure le fichier de configuration avec la connexion PDO
require_once('../database/config.php');

try {
    // Réception des données JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Vérification de la réception des données
    if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erreur de parsing JSON : ' . json_last_error_msg());
    }
    
    // Paramètres par défaut
    $statut = $input['statut'] ?? 'en_attente';
    $periode = $input['periode'] ?? 'week';
    $priorite = $input['priorite'] ?? null;
    $type_vehicule = $input['type_vehicule'] ?? null;

    // Construction de la requête SQL avec filtres
    $query = "SELECT r.id_reservation, 
                r.demandeur, 
                COALESCE(CONCAT(v.marque, ' ', v.modele, ' | ', v.immatriculation), 'Non attribué') AS vehicule, 
                COALESCE(CONCAT(c.nom, ' ', c.prenoms), 'Non attribué') AS chauffeur,
                COALESCE(CONCAT(i.point_depart, ' - ', i.point_arrivee), 'Non défini') AS trajet,
                r.date_demande, 
                r.date_depart, 
                r.date_retour_prevue, 
                r.statut,
                CASE 
                    WHEN r.statut = 'en_attente' THEN 'En attente'
                    WHEN r.statut = 'validee' THEN 'Validée'
                    WHEN r.statut = 'en_cours' THEN 'En cours'
                    WHEN r.statut = 'terminee' THEN 'Terminée'
                    WHEN r.statut = 'annulee' THEN 'Annulée'
                    ELSE r.statut
                END AS statut_libelle
          FROM reservations_vehicules r
          LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
          LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
          LEFT JOIN itineraires i ON r.id_reservation = i.id_reservation
          WHERE 1=1";

    // Construction dynamique des filtres
    $conditions = [];
    $params = [];

    if ($statut) {
        $conditions[] = "r.statut = :statut";
        $params[':statut'] = $statut;
    }

    // Filtrage par période
    switch ($periode) {
        case 'today':
            $conditions[] = "DATE(r.date_depart) = CURDATE()";
            break;
        case 'tomorrow':
            $conditions[] = "DATE(r.date_depart) = CURDATE() + INTERVAL 1 DAY";
            break;
        case 'week':
            $conditions[] = "r.date_depart BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 WEEK";
            break;
        case 'month':
            $conditions[] = "r.date_depart BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 MONTH";
            break;
    }

    // Filtrage par priorité
    if ($priorite !== null) {
        $conditions[] = "r.priorite = :priorite";
        $params[':priorite'] = $priorite;
    }

    // Filtrage par type de véhicule
    if ($type_vehicule) {
        $conditions[] = "v.type_vehicule = :type_vehicule";
        $params[':type_vehicule'] = $type_vehicule;
    }

    // Ajouter les conditions à la requête
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY r.date_demande DESC LIMIT 100"; // Limiter à 100 résultats

    // Préparer et exécuter la requête
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Réponse JSON
    $response = [
        'success' => true,
        'demandes' => $demandes,
        'total' => count($demandes),
        'query' => $query, // Pour débogage
        'params' => $params // Pour débogage
    ];

    // Vérifier que la réponse peut être encodée
    $jsonResponse = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if ($jsonResponse === false) {
        throw new Exception('Erreur lors de l\'encodage JSON : ' . json_last_error_msg());
    }

    echo $jsonResponse;

} catch (PDOException $e) {
    // Gestion des erreurs PDO
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'details' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    // Gestion des autres erreurs
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}