<?php
/**
 * API pour récupérer les détails d'une course (terminée ou annulée)
 * Utilisé pour afficher les détails complets d'une réservation dans l'historique
 */

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Inclusion du fichier de configuration
require_once "../database/config.php";

// Vérifier si l'ID de réservation est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de réservation non fourni'
    ]);
    exit;
}

$id_reservation = intval($_GET['id']);

try {
    // Récupérer les détails complets de la réservation
    $query = "SELECT 
                r.id_reservation,
                r.date_demande,
                r.date_depart,
                r.date_retour_prevue,
                r.date_retour_effective,
                r.nombre_passagers,
                r.statut,
                r.km_depart,
                r.km_retour,
                r.note,
                r.priorite,
                v.id_vehicule,
                v.immatriculation,
                v.marque,
                v.modele,
                v.logo_marque_vehicule,
                v.kilometrage_actuel,
                v.type_vehicule,
                v.type_carburant,
                c.id_chauffeur,
                c.nom as chauffeur_nom,
                c.prenoms as chauffeur_prenoms,
                c.photo_profil,
                c.numero_permis,
                c.telephone as chauffeur_telephone,
                u.id_utilisateur,
                u.nom as demandeur_nom,
                u.prenom as demandeur_prenom,
                u.email as demandeur_email,
                u.telephone as demandeur_telephone,
                i.point_depart,
                i.point_arrivee,
                i.distance_prevue,
                i.temps_trajet_prevu,
                i.points_intermediaires,
                TIMESTAMPDIFF(MINUTE, r.date_depart, COALESCE(r.date_retour_effective, r.date_retour_prevue)) as duree_totale,
                (r.km_retour - r.km_depart) as distance_parcourue
            FROM 
                reservations_vehicules r
            LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
            LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
            LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
            LEFT JOIN itineraires i ON r.id_reservation = i.id_itineraire
            WHERE 
                r.id_reservation = :id_reservation
                AND r.statut IN ('terminee', 'annulee')";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();
    
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        echo json_encode([
            'success' => false,
            'message' => 'Réservation non trouvée ou non terminée/annulée'
        ]);
        exit;
    }
    
    // Formatage des données
    // Traitement des chemins d'image
    $reservation['logo_marque_vehicule'] = !empty($reservation['logo_marque_vehicule']) ? 
        $reservation['logo_marque_vehicule'] : 'default.png';
    
    $reservation['photo_profil'] = !empty($reservation['photo_profil']) ? 
        $reservation['photo_profil'] : 'default_profile.jpg';
    
    // Formatage du statut pour l'affichage
    $reservation['statut_libelle'] = $reservation['statut'] === 'terminee' ? 'Terminée' : 'Annulée';
    
    // Formatage des dates
    if (!empty($reservation['date_demande'])) {
        $date = new DateTime($reservation['date_demande']);
        $reservation['date_demande_formatee'] = $date->format('d/m/Y H:i');
    }
    
    if (!empty($reservation['date_depart'])) {
        $date = new DateTime($reservation['date_depart']);
        $reservation['date_depart_formatee'] = $date->format('d/m/Y H:i');
    }
    
    if (!empty($reservation['date_retour_prevue'])) {
        $date = new DateTime($reservation['date_retour_prevue']);
        $reservation['date_retour_prevue_formatee'] = $date->format('d/m/Y H:i');
    }
    
    if (!empty($reservation['date_retour_effective'])) {
        $date = new DateTime($reservation['date_retour_effective']);
        $reservation['date_retour_effective_formatee'] = $date->format('d/m/Y H:i');
    }
    
    // Calcul des métriques
    if ($reservation['statut'] === 'terminee') {
        // Pourcentage d'écart de temps
        $tempsPrevuMinutes = $reservation['temps_trajet_prevu'] * 60; // Conversion en minutes
        $tempsReel = $reservation['duree_totale'];
        
        if ($tempsPrevuMinutes > 0) {
            $reservation['ecart_temps_pourcentage'] = round(($tempsReel - $tempsPrevuMinutes) / $tempsPrevuMinutes * 100, 2);
        }
        
        // Pourcentage d'écart de distance
        $distancePrevue = $reservation['distance_prevue'];
        $distanceReelle = $reservation['distance_parcourue'];
        
        if ($distancePrevue > 0) {
            $reservation['ecart_distance_pourcentage'] = round(($distanceReelle - $distancePrevue) / $distancePrevue * 100, 2);
        }
        
        // Consommation de carburant estimée (L/100km selon le type de véhicule)
        if ($distanceReelle > 0) {
            switch ($reservation['type_vehicule']) {
                case 'berline':
                    $consommationMoyenne = 7.5;
                    break;
                case 'utilitaire':
                    $consommationMoyenne = 9.5;
                    break;
                case 'camion':
                    $consommationMoyenne = 20;
                    break;
                case 'bus':
                    $consommationMoyenne = 15;
                    break;
                default:
                    $consommationMoyenne = 8;
            }
            
            $reservation['carburant_estime'] = round($distanceReelle * $consommationMoyenne / 100, 2);
        }
    }
    
    // Récupérer l'historique des activités liées à cette réservation
    $queryActivites = "SELECT 
                          type_activite, 
                          description, 
                          date_activite,
                          CONCAT(u.nom, ' ', u.prenom) as utilisateur
                       FROM 
                          journal_activites ja
                       LEFT JOIN 
                          utilisateurs u ON ja.id_utilisateur = u.id_utilisateur
                       WHERE 
                          description LIKE :id_reservation_pattern
                       ORDER BY 
                          date_activite DESC";
    
    $stmt = $pdo->prepare($queryActivites);
    $pattern = "%#" . $id_reservation . "%";
    $stmt->bindParam(':id_reservation_pattern', $pattern);
    $stmt->execute();
    
    $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatage des dates dans les activités
    foreach ($activites as &$activite) {
        if (!empty($activite['date_activite'])) {
            $date = new DateTime($activite['date_activite']);
            $activite['date_formatee'] = $date->format('d/m/Y H:i');
        }
    }
    
    // Retourner la réponse avec tous les détails
    echo json_encode([
        'success' => true,
        'reservation' => $reservation,
        'activites' => $activites
    ]);
    
} catch (PDOException $e) {
    // Gérer les erreurs
    error_log("Erreur dans detail-course.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des détails: ' . $e->getMessage()
    ]);
}