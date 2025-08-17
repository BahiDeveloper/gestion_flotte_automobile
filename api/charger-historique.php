<?php
/**
 * API pour charger l'historique des déplacements (terminés et annulés)
 * Utilisé par l'onglet "Historique" pour afficher les courses passées
 */

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Inclusion du fichier de configuration
require_once "../database/config.php";

// Récupérer le type d'historique demandé (terminée/annulée)
$type = isset($_GET['type']) ? $_GET['type'] : 'terminee';
if (!in_array($type, ['terminee', 'annulee'])) {
    $type = 'terminee'; // Valeur par défaut
}

try {
    // Requête pour récupérer l'historique des déplacements
    $query = "SELECT 
                r.id_reservation,
                r.date_demande,
                r.date_depart,
                r.date_debut_effective,
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
                c.id_chauffeur,
                CONCAT(c.nom, ' ', c.prenoms) as chauffeur_nom,
                c.photo_profil,
                u.id_utilisateur,
                CONCAT(u.nom, ' ', u.prenom) as demandeur_nom,
                i.point_depart,
                i.point_arrivee,
                i.distance_prevue,
                TIMESTAMPDIFF(HOUR, r.date_depart, COALESCE(r.date_retour_effective, NOW())) as duree_reelle,
                (r.km_retour - r.km_depart) as distance_parcourue
            FROM 
                reservations_vehicules r
            LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
            LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
            LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
            LEFT JOIN itineraires i ON r.id_reservation = i.id_itineraire
            WHERE 
                r.statut = :statut
            ORDER BY 
                r.date_demande DESC";
    
    $stmt = $pdo->prepare($query);
    $statut = $type === 'terminee' ? 'terminee' : 'annulee';
    $stmt->bindParam(':statut', $statut);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatage des données
    $historique = [];
    foreach ($results as $row) {
        // Traitement des chemins d'image
        $row['logo_marque_vehicule'] = !empty($row['logo_marque_vehicule']) ? 
            $row['logo_marque_vehicule'] : 'default.png';
        
        $row['photo_profil'] = !empty($row['photo_profil']) ? 
            $row['photo_profil'] : 'default_profile.jpg';
        
        // Formatage du statut pour l'affichage
        $row['statut_libelle'] = $row['statut'] === 'terminee' ? 'Terminée' : 'Annulée';
        
        // Formatage des dates
        if (!empty($row['date_demande'])) {
            $date = new DateTime($row['date_demande']);
            $row['date_demande_formatee'] = $date->format('d/m/Y H:i');
        }
        
        if (!empty($row['date_depart'])) {
            $date = new DateTime($row['date_depart']);
            $row['date_depart_formatee'] = $date->format('d/m/Y H:i');
        }

        if (!empty($row['date_debut_effective'])) {
            $date = new DateTime($row['date_debut_effective']);
            $row['date_debut_effective_formatee'] = $date->format('d/m/Y H:i');
        }
        
        if (!empty($row['date_retour_prevue'])) {
            $date = new DateTime($row['date_retour_prevue']);
            $row['date_retour_prevue_formatee'] = $date->format('d/m/Y H:i');
        }
        
        if (!empty($row['date_retour_effective'])) {
            $date = new DateTime($row['date_retour_effective']);
            $row['date_retour_effective_formatee'] = $date->format('d/m/Y H:i');
        }
        
        $historique[] = $row;
    }
    
    // Retourner la réponse
    echo json_encode([
        'success' => true,
        'type' => $type,
        'historique' => $historique,
        'count' => count($historique)
    ]);
    
} catch (PDOException $e) {
    // Gérer les erreurs
    error_log("Erreur dans charger-historique.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement de l\'historique: ' . $e->getMessage()
    ]);
}