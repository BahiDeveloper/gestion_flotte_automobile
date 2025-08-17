<?php
/**
 * API pour charger les déplacements en cours ou validés
 * Utilisé par l'onglet "Suivi" pour afficher les courses à gérer
 */

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Inclusion du fichier de configuration
require_once "../database/config.php";

try {
    // Requête pour récupérer les réservations validées ou en cours avec les détails
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
                r.materiel,
                r.objet_demande,
                v.id_vehicule,
                v.immatriculation,
                v.marque,
                v.modele,
                v.logo_marque_vehicule,
                v.kilometrage_actuel,
                v.type_vehicule,
                c.id_chauffeur,
                CONCAT(c.nom, ' ', c.prenoms) as chauffeur_nom,
                u.id_utilisateur,
                CONCAT(u.nom, ' ', u.prenom) as demandeur_nom,
                i.point_depart,
                i.point_arrivee,
                i.distance_prevue
            FROM 
                reservations_vehicules r
            LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
            LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
            LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
            LEFT JOIN itineraires i ON r.id_reservation = i.id_itineraire
            WHERE 
                r.statut IN ('validee', 'en_cours')
            ORDER BY 
                r.date_depart ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatage des données
    $deplacements = [];
    foreach ($results as $row) {
        // Formatage du statut pour l'affichage
        $statutMap = [
            'validee' => 'Validée',
            'en_cours' => 'En cours'
        ];

        $row['statut_libelle'] = isset($statutMap[$row['statut']]) ? $statutMap[$row['statut']] : $row['statut'];

        // Formatage des dates pour l'affichage (optionnel)
        if (!empty($row['date_debut_effective'])) {
            $row['date_debut_effective_formatee'] = date('d/m/Y H:i', strtotime($row['date_debut_effective']));
        }

        // Ajouter les données à la liste
        $deplacements[] = $row;
    }

    // Retourner la réponse
    echo json_encode([
        'success' => true,
        'deplacements' => $deplacements,
        'count' => count($deplacements)
    ]);

} catch (PDOException $e) {
    // Gérer les erreurs
    error_log("Erreur dans charger-deplacements-en-cours.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des déplacements: ' . $e->getMessage()
    ]);
}