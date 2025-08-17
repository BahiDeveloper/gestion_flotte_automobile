<?php
// api/vehicules-disponibles.php
header('Content-Type: application/json');
require_once '../database/config.php';

// Gestion de deux cas d'utilisation:
// 1. Recherche de véhicules disponibles pour une nouvelle réservation
// 2. Recherche de véhicules disponibles pour la modification d'une réservation existante

// Vérifier si on est dans le cas d'une modification de réservation
$id_reservation = isset($_GET['id_reservation']) ? intval($_GET['id_reservation']) : 0;
$zoneVehicule = isset($_GET['zone']) ? intval($_GET['zone']) : 0;

if ($id_reservation > 0) {
    // CAS DE MODIFICATION D'UNE RÉSERVATION EXISTANTE
    try {
        // Récupérer les informations de la réservation existante
        $stmt = $pdo->prepare("
            SELECT r.date_depart, r.date_retour_prevue, r.nombre_passagers, r.id_vehicule, r.zone_vehicule_id,
                v.type_vehicule
            FROM reservations_vehicules r
            LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
            WHERE r.id_reservation = ?
        ");
        $stmt->execute([$id_reservation]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            echo json_encode(['success' => false, 'message' => 'Réservation introuvable']);
            exit;
        }

        $date_depart = $reservation['date_depart'];
        $date_retour = $reservation['date_retour_prevue'];
        $type_vehicule = $reservation['type_vehicule'];
        $nombre_passagers = $reservation['nombre_passagers'];
        $vehicule_actuel = $reservation['id_vehicule'];
        $zone_vehicule_actuelle = $reservation['zone_vehicule_id'];

        // Utiliser la zone fournie ou la zone actuelle de la réservation
        $zone_vehicule_id = $zoneVehicule > 0 ? $zoneVehicule : $zone_vehicule_actuelle;

        // Requête pour trouver les véhicules disponibles pour cette modification
        $sql = "SELECT 
        v.id_vehicule, 
        v.marque, 
        v.modele, 
        v.immatriculation,
        v.type_vehicule,
        v.capacite_passagers,
        v.statut,
        v.logo_marque_vehicule,
        v.kilometrage_actuel,
        v.id_zone
    FROM 
        vehicules v
    WHERE 
        v.statut != 'hors_service'
        AND v.capacite_passagers >= :passagers
        AND v.id_zone = :zone_vehicule
        " . ($type_vehicule ? "AND v.type_vehicule = :type_vehicule" : "") . "
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
        v.kilometrage_actuel ASC";
        
        $stmt = $pdo->prepare($sql);
        
        // Paramètres de la requête
        $params = [
            ':type_vehicule' => $type_vehicule,
            ':passagers' => $nombre_passagers,
            ':id_reservation' => $id_reservation,
            ':vehicule_actuel' => $vehicule_actuel,
            ':date_depart' => $date_depart,
            ':date_retour' => $date_retour,
            ':zone_vehicule' => $zone_vehicule_id
        ];
        
        $stmt->execute($params);
        $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formater la réponse
        if (count($vehicules) > 0) {
            echo json_encode([
                'success' => true, 
                'vehicules' => $vehicules,
                'count' => count($vehicules)
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Aucun véhicule disponible pour cette période et cette zone',
                'vehicules' => []
            ]);
        }
    } catch (PDOException $e) {
        error_log('Erreur PDO : ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Erreur lors de la recherche de véhicules disponibles: ' . $e->getMessage()
        ]);
    }
} else {
    // CAS D'UNE NOUVELLE RÉSERVATION (code existant similaire)
    $dateDepart = isset($_GET['dateDepart']) ? $_GET['dateDepart'] : '';
    $dateArrivee = isset($_GET['dateArrivee']) ? $_GET['dateArrivee'] : '';
    $typeVehicule = isset($_GET['type']) ? $_GET['type'] : '';
    $nbPassagers = isset($_GET['passagers']) ? intval($_GET['passagers']) : 0;
    $zoneVehicule = isset($_GET['zone']) ? intval($_GET['zone']) : 0;

    // Validation des entrées (code existant)
    if (empty($dateDepart) || empty($dateArrivee) || empty($typeVehicule) || $nbPassagers <= 0 || $zoneVehicule <= 0) {
        echo json_encode(['success' => false, 'message' => 'Paramètres manquants ou invalides']);
        exit;
    }

    try {
        // Formater les dates pour MySQL
        $dateDepartFormatted = date('Y-m-d H:i:s', strtotime($dateDepart));
        $dateArriveeFormatted = date('Y-m-d H:i:s', strtotime($dateArrivee));

        // Requête pour trouver les véhicules disponibles
        $sql = "SELECT 
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
    v.type_vehicule = :typeVehicule
    AND v.capacite_passagers >= :nbPassagers
    AND v.statut != 'hors_service'
    AND v.id_zone = :zoneVehicule
    AND NOT EXISTS (
        SELECT 1 
        FROM reservations_vehicules r 
        WHERE r.id_vehicule = v.id_vehicule 
        AND r.statut IN ('validee', 'en_cours', 'en_attente')
        AND (
            (:dateDepart BETWEEN r.date_depart AND r.date_retour_prevue)
            OR (:dateArrivee BETWEEN r.date_depart AND r.date_retour_prevue)
            OR (r.date_depart BETWEEN :dateDepart AND :dateArrivee)
        )
    )
    AND NOT EXISTS (
        SELECT 1 
        FROM maintenances m
        WHERE m.id_vehicule = v.id_vehicule
        AND m.statut IN ('planifiee', 'en_cours')
        AND (
            (:dateDepart BETWEEN m.date_debut AND m.date_fin_prevue)
            OR (:dateArrivee BETWEEN m.date_debut AND m.date_fin_prevue)
            OR (m.date_debut BETWEEN :dateDepart AND :dateArrivee)
        )
    )
ORDER BY 
    v.kilometrage_actuel ASC";
                
        $stmt = $pdo->prepare($sql);

        $params = [
            ':typeVehicule' => $typeVehicule,
            ':nbPassagers' => $nbPassagers,
            ':dateDepart' => $dateDepartFormatted,
            ':dateArrivee' => $dateArriveeFormatted,
            ':zoneVehicule' => $zoneVehicule
        ];

        $stmt->execute($params);
        
        $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($vehicules) > 0) {
            echo json_encode([
                'success' => true, 
                'vehicules' => $vehicules,
                'count' => count($vehicules)
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Aucun véhicule disponible pour cette période et cette zone',
                'vehicules' => []
            ]);
        }
        
    } catch (PDOException $e) {
        error_log('Erreur PDO : ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la recherche des véhicules disponibles']);
    }
}
?>