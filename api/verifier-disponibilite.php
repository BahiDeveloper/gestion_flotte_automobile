<?php
// api/verifier-disponibilite.php 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once('../database/config.php');

try {
    // Activer le mode débogage
    $debug = true;
    $debugInfo = [];
    
    $idReservation = $_GET['id_reservation'] ?? null;

    if (!$idReservation) {
        throw new Exception('ID de réservation manquant');
    }

    // Stocker les logs de débogage
    $debugInfo['id_reservation'] = $idReservation;

    // 1. Récupérer les détails de la réservation 
    $queryReservation = "SELECT 
        r.id_reservation, 
        r.nombre_passagers, 
        COALESCE(v.type_vehicule, 'berline') as type_vehicule, 
        r.date_depart,
        r.date_retour_prevue,
        r.id_vehicule,
        r.id_chauffeur
    FROM 
        reservations_vehicules r
    LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
    WHERE 
        r.id_reservation = :id";
    
    $stmtReservation = $pdo->prepare($queryReservation);
    $stmtReservation->execute([':id' => $idReservation]);
    $reservation = $stmtReservation->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        throw new Exception('Réservation non trouvée');
    }

    // Stocker les détails de la réservation pour débogage
    $debugInfo['reservation'] = $reservation;

    // Paramètres de vérification
    $dateDepart = $reservation['date_depart'];
    $dateRetour = $reservation['date_retour_prevue'];
    $passagers = $reservation['nombre_passagers'];
    $typeVehicule = $reservation['type_vehicule'];
    $vehiculeActuel = $reservation['id_vehicule'];
    $chauffeurActuel = $reservation['id_chauffeur'];
    
    $debugInfo['params'] = [
        'dateDepart' => $dateDepart,
        'dateRetour' => $dateRetour,
        'passagers' => $passagers,
        'typeVehicule' => $typeVehicule,
        'vehiculeActuel' => $vehiculeActuel,
        'chauffeurActuel' => $chauffeurActuel
    ];

    // 2. Vérifier les véhicules disponibles - Utiliser la même requête que vehicules-disponibles.php
    $queryVehicules = "SELECT 
        v.id_vehicule, 
        v.marque, 
        v.modele, 
        v.immatriculation,
        v.type_vehicule,
        v.capacite_passagers,
        v.statut,
        v.logo_marque_vehicule,
        v.kilometrage_actuel
    FROM 
        vehicules v
    WHERE 
        v.type_vehicule = :type_vehicule
        AND v.capacite_passagers >= :passagers
        AND v.statut != 'hors_service'
        AND (
            v.id_vehicule = :vehicule_actuel
            OR (
                NOT EXISTS (
                    SELECT 1 
                    FROM reservations_vehicules r 
                    WHERE r.id_vehicule = v.id_vehicule 
                    AND r.id_reservation != :id_reservation
                    AND r.statut IN ('validee', 'en_cours', 'en_attente')
                    AND (
                        (:date_depart BETWEEN r.date_depart AND r.date_retour_prevue)
                        OR (:date_retour BETWEEN r.date_depart AND r.date_retour_prevue)
                        OR (r.date_depart BETWEEN :date_depart AND :date_retour)
                    )
                )
                AND NOT EXISTS (
                    SELECT 1 
                    FROM maintenances m
                    WHERE m.id_vehicule = v.id_vehicule
                    AND m.statut IN ('planifiee', 'en_cours')
                    AND (
                        (:date_depart BETWEEN m.date_debut AND m.date_fin_prevue)
                        OR (:date_retour BETWEEN m.date_debut AND m.date_fin_prevue)
                        OR (m.date_debut BETWEEN :date_depart AND :date_retour)
                    )
                )
            )
        )
    ORDER BY 
        CASE WHEN v.id_vehicule = :vehicule_actuel THEN 0 ELSE 1 END,
        v.kilometrage_actuel ASC
    LIMIT 10";

    $stmtVehicules = $pdo->prepare($queryVehicules);
    $stmtVehicules->execute([
        ':type_vehicule' => $typeVehicule,
        ':passagers' => $passagers,
        ':date_depart' => $dateDepart,
        ':date_retour' => $dateRetour,
        ':id_reservation' => $idReservation,
        ':vehicule_actuel' => $vehiculeActuel
    ]);
    $vehicules = $stmtVehicules->fetchAll(PDO::FETCH_ASSOC);
    
    // Stocker les véhicules disponibles pour débogage
    $debugInfo['vehicules'] = [
        'count' => count($vehicules),
        'data' => $vehicules
    ];

    // Vérifier si des véhicules existent mais ne correspondent pas aux critères (pour message d'erreur spécifique)
    $queryVehiculesExistent = "SELECT COUNT(*) as total FROM vehicules WHERE type_vehicule = :type_vehicule";
    $stmtVehiculesExistent = $pdo->prepare($queryVehiculesExistent);
    $stmtVehiculesExistent->execute([':type_vehicule' => $typeVehicule]);
    $vehiculesExistentCount = $stmtVehiculesExistent->fetch(PDO::FETCH_ASSOC)['total'];
    
    $queryVehiculesCapacite = "SELECT COUNT(*) as total FROM vehicules WHERE type_vehicule = :type_vehicule AND capacite_passagers >= :passagers";
    $stmtVehiculesCapacite = $pdo->prepare($queryVehiculesCapacite);
    $stmtVehiculesCapacite->execute([
        ':type_vehicule' => $typeVehicule,
        ':passagers' => $passagers
    ]);
    $vehiculesCapaciteCount = $stmtVehiculesCapacite->fetch(PDO::FETCH_ASSOC)['total'];
    
    $debugInfo['vehicules_stats'] = [
        'total_par_type' => $vehiculesExistentCount,
        'total_capacite_ok' => $vehiculesCapaciteCount
    ];

    // 3. Vérifier si des chauffeurs sont occupés sur cette période (débogage)
    $queryChauffeursOccupes = "
    SELECT 
        c.id_chauffeur, 
        c.nom, 
        c.prenoms, 
        c.statut,
        r.id_reservation,
        r.date_depart,
        r.date_retour_prevue,
        r.statut as statut_reservation
    FROM 
        chauffeurs c
    JOIN 
        reservations_vehicules r ON r.id_chauffeur = c.id_chauffeur
    WHERE 
        r.id_reservation != :id_reservation
        AND r.statut IN ('validee', 'en_cours')
        AND (
            (:date_depart BETWEEN r.date_depart AND r.date_retour_prevue)
            OR (:date_retour BETWEEN r.date_depart AND r.date_retour_prevue)
            OR (r.date_depart BETWEEN :date_depart AND :date_retour)
        )
    ORDER BY 
        c.id_chauffeur, r.date_depart";

    $stmtChauffeursOccupes = $pdo->prepare($queryChauffeursOccupes);
    $stmtChauffeursOccupes->execute([
        ':id_reservation' => $idReservation,
        ':date_depart' => $dateDepart,
        ':date_retour' => $dateRetour
    ]);
    $chauffeursOccupes = $stmtChauffeursOccupes->fetchAll(PDO::FETCH_ASSOC);
    
    // Stocker les chauffeurs occupés pour débogage
    $debugInfo['chauffeurs_occupes'] = $chauffeursOccupes;
    
    // 4. Liste de tous les chauffeurs avec leur statut (pour débogage)
    $queryTousChauffeurs = "SELECT id_chauffeur, nom, prenoms, statut FROM chauffeurs";
    $stmtTousChauffeurs = $pdo->query($queryTousChauffeurs);
    $tousChauffeurs = $stmtTousChauffeurs->fetchAll(PDO::FETCH_ASSOC);
    
    $debugInfo['tous_chauffeurs'] = $tousChauffeurs;

    // 5. Requête améliorée pour les chauffeurs disponibles
    $queryChauffeurs = "
    SELECT DISTINCT
        c.id_chauffeur, 
        c.nom, 
        c.prenoms, 
        c.statut,
        c.specialisation
    FROM 
        chauffeurs c
    WHERE 
        c.statut NOT IN ('conge', 'indisponible')
        AND (
            c.id_chauffeur = :chauffeur_actuel
            OR (
                (c.statut = 'disponible' OR c.statut = 'en_course')
                AND NOT EXISTS (
                    SELECT 1 
                    FROM reservations_vehicules r 
                    WHERE r.id_chauffeur = c.id_chauffeur 
                    AND r.id_reservation != :id_reservation
                    AND r.statut IN ('validee', 'en_cours')
                    AND (
                        (:date_depart BETWEEN r.date_depart AND r.date_retour_prevue)
                        OR (:date_retour BETWEEN r.date_depart AND r.date_retour_prevue)
                        OR (r.date_depart BETWEEN :date_depart AND :date_retour)
                    )
                )
            )
        )
    ORDER BY 
        CASE WHEN c.id_chauffeur = :chauffeur_actuel THEN 0 ELSE 1 END,
        CASE 
            WHEN c.statut = 'disponible' THEN 1
            WHEN c.statut = 'en_course' THEN 2
            ELSE 3
        END,
        c.id_chauffeur ASC
    LIMIT 10";

    $stmtChauffeurs = $pdo->prepare($queryChauffeurs);
    $stmtChauffeurs->execute([
        ':id_reservation' => $idReservation,
        ':date_depart' => $dateDepart,
        ':date_retour' => $dateRetour,
        ':chauffeur_actuel' => $chauffeurActuel
    ]);
    $chauffeurs = $stmtChauffeurs->fetchAll(PDO::FETCH_ASSOC);
    
    // Stocker les chauffeurs disponibles pour débogage
    $debugInfo['chauffeurs'] = [
        'count' => count($chauffeurs),
        'data' => $chauffeurs
    ];

    // Vérifier les statistiques sur les chauffeurs pour messages d'erreur précis
    $queryChauffeursTotal = "SELECT COUNT(*) as total FROM chauffeurs";
    $stmtChauffeursTotal = $pdo->query($queryChauffeursTotal);
    $chauffeursTotal = $stmtChauffeursTotal->fetch(PDO::FETCH_ASSOC)['total'];
    
    $queryChauffeursActifs = "SELECT COUNT(*) as total FROM chauffeurs WHERE statut IN ('disponible', 'en_course')";
    $stmtChauffeursActifs = $pdo->query($queryChauffeursActifs);
    $chauffeursActifs = $stmtChauffeursActifs->fetch(PDO::FETCH_ASSOC)['total'];
    
    $debugInfo['chauffeurs_stats'] = [
        'total' => $chauffeursTotal,
        'actifs' => $chauffeursActifs
    ];

    // Débogage: vérifier pourquoi certains chauffeurs ne sont pas dans la liste des disponibles
    if ($debug && !empty($tousChauffeurs)) {
        $chauffeursDispo = array_column($chauffeurs, 'id_chauffeur');
        $chauffeursNonDispo = [];
        
        foreach ($tousChauffeurs as $chauffeur) {
            if (!in_array($chauffeur['id_chauffeur'], $chauffeursDispo)) {
                // Vérifier pourquoi ce chauffeur n'est pas disponible
                $raisons = [];
                
                if ($chauffeur['statut'] == 'conge' || $chauffeur['statut'] == 'indisponible') {
                    $raisons[] = "Statut: " . $chauffeur['statut'];
                }
                
                foreach ($chauffeursOccupes as $occupe) {
                    if ($occupe['id_chauffeur'] == $chauffeur['id_chauffeur']) {
                        $raisons[] = "Réservation #" . $occupe['id_reservation'] . " (" . 
                                      date('d/m/Y H:i', strtotime($occupe['date_depart'])) . " - " . 
                                      date('d/m/Y H:i', strtotime($occupe['date_retour_prevue'])) . ")";
                    }
                }
                
                if (empty($raisons)) {
                    $raisons[] = "Raison inconnue";
                }
                
                $chauffeursNonDispo[] = [
                    'id_chauffeur' => $chauffeur['id_chauffeur'],
                    'nom' => $chauffeur['nom'] . ' ' . $chauffeur['prenoms'],
                    'statut' => $chauffeur['statut'],
                    'raisons' => $raisons
                ];
            }
        }
        
        $debugInfo['chauffeurs_non_disponibles'] = $chauffeursNonDispo;
    }

    // Sélectionner les premiers véhicules et chauffeurs disponibles comme défaut
    $vehiculeDefaut = !empty($vehicules) ? $vehicules[0] : null;
    $chauffeurDefaut = !empty($chauffeurs) ? $chauffeurs[0] : null;

    // Réponse JSON
    $reponse = [
        'success' => !empty($vehicules) && !empty($chauffeurs),
        'vehicules' => $vehicules,
        'chauffeurs' => $chauffeurs,
        'vehicule' => $vehiculeDefaut,
        'chauffeur' => $chauffeurDefaut
    ];

    // Déterminer des messages d'erreur précis en fonction des problèmes
    if (empty($vehicules) && empty($chauffeurs)) {
        $reponse['message'] = 'Aucune ressource disponible pour cette période';
        
        // Messages d'erreur plus spécifiques
        $messagesErreur = [];
        
        if ($vehiculesExistentCount == 0) {
            $messagesErreur[] = "Aucun véhicule de type '$typeVehicule' n'existe dans la flotte.";
        } elseif ($vehiculesCapaciteCount == 0) {
            $messagesErreur[] = "Aucun véhicule de type '$typeVehicule' n'a une capacité suffisante pour $passagers passagers.";
        } else {
            $messagesErreur[] = "Tous les véhicules de type '$typeVehicule' sont déjà réservés pour cette période.";
        }
        
        if ($chauffeursTotal == 0) {
            $messagesErreur[] = "Aucun chauffeur n'est enregistré dans le système.";
        } elseif ($chauffeursActifs == 0) {
            $messagesErreur[] = "Tous les chauffeurs sont actuellement en congé ou indisponibles.";
        } else {
            $messagesErreur[] = "Tous les chauffeurs sont déjà assignés à d'autres courses pour cette période.";
        }
        
        $reponse['details_erreur'] = $messagesErreur;
    } 
    elseif (empty($vehicules)) {
        if ($vehiculesExistentCount == 0) {
            $reponse['message'] = "Aucun véhicule de type '$typeVehicule' n'existe dans la flotte.";
        } elseif ($vehiculesCapaciteCount == 0) {
            $reponse['message'] = "Aucun véhicule de type '$typeVehicule' n'a une capacité suffisante pour $passagers passagers.";
        } else {
            $reponse['message'] = "Tous les véhicules de type '$typeVehicule' sont déjà réservés pour cette période.";
        }
    } 
    elseif (empty($chauffeurs)) {
        if ($chauffeursTotal == 0) {
            $reponse['message'] = "Aucun chauffeur n'est enregistré dans le système.";
        } elseif ($chauffeursActifs == 0) {
            $reponse['message'] = "Tous les chauffeurs sont actuellement en congé ou indisponibles.";
        } else {
            $reponse['message'] = "Tous les chauffeurs sont déjà assignés à d'autres courses pour cette période.";
        }
    }

    // Ajouter les informations de débogage si activé
    if ($debug) {
        $reponse['debug'] = $debugInfo;
    }

    echo json_encode($reponse);

} catch (Exception $e) {
    error_log("Erreur dans verifier-disponibilite.php : " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>