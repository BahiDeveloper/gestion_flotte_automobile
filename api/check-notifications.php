<?php
// api/check-notifications.php
header('Content-Type: application/json');
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

// Vérifier si l'utilisateur a un rôle qui nécessite des notifications
$allowedRoles = ['administrateur', 'gestionnaire', 'validateur'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    echo json_encode([
        'success' => false,
        'message' => 'Rôle non autorisé'
    ]);
    exit;
}

require_once "../database/config.php";

try {
    $notifications = [];
    $totalCount = 0;

    // 1. Réservations en attente
    $stmt = $pdo->prepare("
        SELECT 
            r.id_reservation,
            r.demandeur,
            r.date_demande,
            r.date_depart,
            r.date_retour_prevue,
            v.marque,
            v.modele,
            i.point_depart,
            i.point_arrivee
        FROM 
            reservations_vehicules r
        LEFT JOIN 
            vehicules v ON r.id_vehicule = v.id_vehicule
        LEFT JOIN 
            itineraires i ON r.id_reservation = i.id_reservation
        WHERE 
            r.statut = 'en_attente'
        ORDER BY 
            r.date_demande DESC
        LIMIT 10
    ");
    $stmt->execute();
    $reservationsAttente = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter les réservations en attente aux notifications
    foreach ($reservationsAttente as $reservation) {
        $dateDemande = new DateTime($reservation['date_demande']);
        $dateDepart = new DateTime($reservation['date_depart']);
        $dateRetour = new DateTime($reservation['date_retour_prevue']);

        $vehicleInfo = '';
        if (!empty($reservation['marque']) && !empty($reservation['modele'])) {
            $vehicleInfo = "{$reservation['marque']} {$reservation['modele']}";
        }

        $title = "Nouvelle demande de {$reservation['demandeur']}";
        $description = "Trajet: {$reservation['point_depart']} → {$reservation['point_arrivee']}";

        if (!empty($vehicleInfo)) {
            $description .= " | {$vehicleInfo}";
        }

        $description .= " | " . $dateDepart->format('d/m/Y H:i');

        $notifications[] = [
            'type' => 'reservation',
            'id' => $reservation['id_reservation'],
            'title' => $title,
            'description' => $description,
            'timestamp' => $dateDemande->getTimestamp(),
            'date' => $dateDemande->format('d/m/Y H:i')
        ];
    }

    // 2. Déplacements en cours
    $stmt = $pdo->prepare("
        SELECT 
            r.id_reservation,
            r.demandeur,
            r.date_depart,
            r.date_retour_prevue,
            v.marque,
            v.modele,
            v.immatriculation,
            c.nom as chauffeur_nom,
            i.point_depart,
            i.point_arrivee
        FROM 
            reservations_vehicules r
        LEFT JOIN 
            vehicules v ON r.id_vehicule = v.id_vehicule
        LEFT JOIN 
            chauffeurs c ON r.id_chauffeur = c.id_chauffeur
        LEFT JOIN 
            itineraires i ON r.id_reservation = i.id_reservation
        WHERE 
            r.statut = 'en_cours'
        ORDER BY 
            r.date_depart DESC
        LIMIT 5
    ");
    $stmt->execute();
    $deplacementsEnCours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter les déplacements en cours aux notifications
    foreach ($deplacementsEnCours as $deplacement) {
        $dateDepart = new DateTime($deplacement['date_depart']);
        $dateRetour = new DateTime($deplacement['date_retour_prevue']);

        $title = "Déplacement en cours";
        $description = "Véhicule : {$deplacement['marque']} {$deplacement['modele']} ({$deplacement['immatriculation']})";

        if (!empty($deplacement['chauffeur_nom'])) {
            $description .= " | Chauffeur : {$deplacement['chauffeur_nom']}";
        }

        $description .= " | Trajet : {$deplacement['point_depart']} → {$deplacement['point_arrivee']}";

        $notifications[] = [
            'type' => 'deplacement_en_cours',
            'id' => $deplacement['id_reservation'],
            'title' => $title,
            'description' => $description,
            'timestamp' => $dateDepart->getTimestamp(),
            'date' => $dateDepart->format('d/m/Y H:i')
        ];
    }

    // 3. Maintenances en cours
    $stmt = $pdo->prepare("
        SELECT 
            m.id_maintenance,
            m.date_debut,
            m.date_fin_prevue,
            v.marque,
            v.modele,
            v.immatriculation,
            m.type_maintenance,
            m.description
        FROM 
            maintenances m
        JOIN 
            vehicules v ON m.id_vehicule = v.id_vehicule
        WHERE 
            m.statut = 'en_cours'
        ORDER BY 
            m.date_debut DESC
        LIMIT 5
    ");
    $stmt->execute();
    $maintenancesEnCours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter les maintenances en cours aux notifications
    foreach ($maintenancesEnCours as $maintenance) {
        $dateDebut = new DateTime($maintenance['date_debut']);
        $dateFinPrevue = new DateTime($maintenance['date_fin_prevue']);

        $title = "Maintenance en cours";
        $description = "Véhicule : {$maintenance['marque']} {$maintenance['modele']} ({$maintenance['immatriculation']})";
        $description .= " | Type : " . ucfirst($maintenance['type_maintenance']);

        if (!empty($maintenance['description'])) {
            $description .= " | Détails : " . substr($maintenance['description'], 0, 50) . (strlen($maintenance['description']) > 50 ? '...' : '');
        }

        $notifications[] = [
            'type' => 'maintenance_en_cours',
            'id' => $maintenance['id_maintenance'],
            'title' => $title,
            'description' => $description,
            'timestamp' => $dateDebut->getTimestamp(),
            'date' => $dateDebut->format('d/m/Y H:i')
        ];
    }

    // 4. Documents à renouveler
    $stmt = $pdo->prepare("
        SELECT 
            d.id_document,
            d.type_document,
            d.date_emission,
            d.date_expiration,
            v.marque,
            v.modele,
            v.immatriculation
        FROM 
            documents_administratifs d
        LEFT JOIN 
            vehicules v ON d.id_vehicule = v.id_vehicule
        WHERE 
            DATEDIFF(d.date_expiration, CURDATE()) <= 60
            AND d.statut != 'expire'
        ORDER BY 
            d.date_expiration ASC
        LIMIT 5
    ");
    $stmt->execute();
    $documentsARenouveler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter les documents à renouveler aux notifications
    foreach ($documentsARenouveler as $document) {
        $dateExpiration = new DateTime($document['date_expiration']);
        $joursRestants = date_diff(new DateTime(), $dateExpiration)->days;

        $title = "Document à renouveler";
        $description = "Type : " . ucfirst(str_replace('_', ' ', $document['type_document']));

        if (!empty($document['marque'])) {
            $description .= " | Véhicule : {$document['marque']} {$document['modele']} ({$document['immatriculation']})";
        }

        $description .= " | Expire dans {$joursRestants} jours";

        $notifications[] = [
            'type' => 'document_a_renouveler',
            'id' => $document['id_document'],
            'title' => $title,
            'description' => $description,
            'timestamp' => $dateExpiration->getTimestamp(),
            'date' => $dateExpiration->format('d/m/Y')
        ];
    }

    // Trier les notifications par timestamp (du plus récent au plus ancien)
    usort($notifications, function ($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });

    // Limiter à 15 notifications maximum pour éviter de surcharger l'interface
    $notifications = array_slice($notifications, 0, 15);

    // Compter le nombre total de notifications
    $totalCount = count($notifications);

    // Ajouter des champs uniques pour chaque notification
    foreach ($notifications as &$notification) {
        // Générer un ID unique pour chaque notification si nécessaire
        if (!isset($notification['unique_id'])) {
            $notification['unique_id'] = $notification['type'] . '_' . $notification['id'];
        }
    }

    echo json_encode([
        'success' => true,
        'count' => $totalCount,
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des notifications: ' . $e->getMessage()
    ]);
}