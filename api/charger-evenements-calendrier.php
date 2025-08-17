<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once "../database/config.php";

try {
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;
    $statuts = isset($_GET['statuts']) ? explode(',', $_GET['statuts']) : [];
    $chauffeur = $_GET['chauffeur'] ?? null;

    // Requête améliorée pour inclure les informations de trajet et de demandeur
    $query = "SELECT r.*, 
                     v.marque, 
                     v.modele, 
                     v.immatriculation, 
                     c.nom as chauffeur_nom, 
                     c.prenoms as chauffeur_prenoms,
                     u.nom as demandeur_nom,
                     u.prenom as demandeur_prenom,
                     i.point_depart,
                     i.point_arrivee 
              FROM reservations_vehicules r
              LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
              LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
              LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
              LEFT JOIN itineraires i ON r.id_reservation = i.id_reservation
              WHERE 1=1";

    $conditions = [];
    $params = [];

    if (!empty($statuts)) {
        $conditions[] = "r.statut IN (" . implode(',', array_fill(0, count($statuts), '?')) . ")";
        $params = array_merge($params, $statuts);
    }

    if ($chauffeur) {
        $conditions[] = "r.id_chauffeur = ?";
        $params[] = $chauffeur;
    }

    if ($start && $end) {
        $conditions[] = "(r.date_depart BETWEEN ? AND ?) OR (r.date_retour_prevue BETWEEN ? AND ?)";
        $params = array_merge($params, [$start, $end, $start, $end]);
    }

    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = array_map(function ($reservation) {
        $backgroundColor = match ($reservation['statut']) {
            'en_attente' => '#FFC107',
            'validee' => '#28A745',
            'en_cours' => '#17A2B8',
            'terminee' => '#6C757D',
            'annulee' => '#DC3545',
            default => '#007BFF'
        };

        // Préparation du nom du demandeur
        $demandeur = "Non spécifié";
        if (!empty($reservation['demandeur'])) {
            $demandeur = $reservation['demandeur'];
        } else if (!empty($reservation['demandeur_nom']) && !empty($reservation['demandeur_prenom'])) {
            $demandeur = $reservation['demandeur_nom'] . ' ' . $reservation['demandeur_prenom'];
        }

        // Préparation des informations de trajet
        $trajet = "Non défini";
        if (!empty($reservation['point_depart']) && !empty($reservation['point_arrivee'])) {
            $trajet = $reservation['point_depart'] . ' - ' . $reservation['point_arrivee'];
        }

        // Formatage du statut pour l'affichage
        $statutLibelle = match ($reservation['statut']) {
            'en_attente' => 'En attente',
            'validee' => 'Validée',
            'en_cours' => 'En cours',
            'terminee' => 'Terminée',
            'annulee' => 'Annulée',
            default => $reservation['statut']
        };

        // Préparation des informations de chauffeur
        $chauffeur = "Non assigné";
        if (!empty($reservation['chauffeur_nom'])) {
            $chauffeur = $reservation['chauffeur_nom'];
            if (!empty($reservation['chauffeur_prenoms'])) {
                $chauffeur .= ' ' . $reservation['chauffeur_prenoms'];
            }
        }

        // Construction de l'événement
        return [
            'id' => $reservation['id_reservation'],
            'title' => (!empty($reservation['marque']) && !empty($reservation['modele'])) ?
                $reservation['marque'] . ' ' . $reservation['modele'] : 'Véhicule non assigné',
            'start' => $reservation['date_depart'],
            'end' => $reservation['date_retour_prevue'],
            'backgroundColor' => $backgroundColor,
            'borderColor' => $backgroundColor,
            'textColor' => 'white',
            'extendedProps' => [
                'vehicule' => (!empty($reservation['marque']) && !empty($reservation['modele']) && !empty($reservation['immatriculation'])) ?
                    $reservation['marque'] . ' ' . $reservation['modele'] . ' (' . $reservation['immatriculation'] . ')' :
                    'Non assigné',
                'chauffeur' => $chauffeur,
                'trajet' => $trajet,
                'demandeur' => $demandeur,
                'statut' => $statutLibelle,
                'objetDemande' => $reservation['objet_demande'] ?? 'Non spécifié'
            ]
        ];
    }, $reservations);

    echo json_encode($events);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Erreur de base de données : ' . $e->getMessage()
    ]);
}