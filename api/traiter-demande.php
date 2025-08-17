<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../database/config.php');

try {
    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    $idReservation = $input['id_reservation'] ?? null;
    $action = $input['action'] ?? null;

    if (!$idReservation || !$action) {
        throw new Exception('Paramètres invalides');
    }

    // Début de la transaction
    $pdo->beginTransaction();

    // Actions spécifiques selon le type de validation
    switch ($action) {
        case 'valider':
            // Vérifier la disponibilité du véhicule
            $vehiculeDisponible = $pdo->query("SELECT id_vehicule FROM vehicules WHERE statut = 'disponible' LIMIT 1")->fetch(PDO::FETCH_COLUMN);
            
            // Vérifier la disponibilité du chauffeur
            $chauffeurDisponible = $pdo->query("SELECT id_chauffeur FROM chauffeurs WHERE statut = 'disponible' LIMIT 1")->fetch(PDO::FETCH_COLUMN);

            // Préparer la requête de mise à jour
            $queryUpdate = "UPDATE reservations_vehicules 
                            SET statut = :statut, 
                                id_vehicule = :vehicule, 
                                id_chauffeur = :chauffeur 
                            WHERE id_reservation = :id";
            
            $stmtUpdate = $pdo->prepare($queryUpdate);

            // Gestion des cas de non-disponibilité
            if (!$vehiculeDisponible && !$chauffeurDisponible) {
                // Aucun véhicule et chauffeur disponible
                $stmtUpdate->execute([
                    ':statut' => 'en_attente',
                    ':vehicule' => null,
                    ':chauffeur' => null,
                    ':id' => $idReservation
                ]);

                // Créer une alerte pour le gestionnaire
                $queryAlerte = "INSERT INTO alertes_systeme 
                                (type_alerte, description, date_creation, priorite) 
                                VALUES 
                                ('reservation', 
                                 'Aucun véhicule ni chauffeur disponible pour la réservation #$idReservation', 
                                 NOW(), 
                                 2)";
                $pdo->exec($queryAlerte);

                // Notification ou email au gestionnaire (à implémenter)
                throw new Exception('Aucun véhicule ni chauffeur disponible. La demande reste en attente.');
            } elseif (!$vehiculeDisponible) {
                // Pas de véhicule disponible
                $stmtUpdate->execute([
                    ':statut' => 'en_attente',
                    ':vehicule' => null,
                    ':chauffeur' => $chauffeurDisponible,
                    ':id' => $idReservation
                ]);

                // Créer une alerte pour le gestionnaire
                $queryAlerte = "INSERT INTO alertes_systeme 
                                (type_alerte, description, date_creation, priorite) 
                                VALUES 
                                ('reservation', 
                                 'Aucun véhicule disponible pour la réservation #$idReservation', 
                                 NOW(), 
                                 1)";
                $pdo->exec($queryAlerte);

                throw new Exception('Aucun véhicule disponible. La demande reste en attente.');
            } elseif (!$chauffeurDisponible) {
                // Pas de chauffeur disponible
                $stmtUpdate->execute([
                    ':statut' => 'en_attente',
                    ':vehicule' => $vehiculeDisponible,
                    ':chauffeur' => null,
                    ':id' => $idReservation
                ]);

                // Créer une alerte pour le gestionnaire
                $queryAlerte = "INSERT INTO alertes_systeme 
                                (type_alerte, description, date_creation, priorite) 
                                VALUES 
                                ('reservation', 
                                 'Aucun chauffeur disponible pour la réservation #$idReservation', 
                                 NOW(), 
                                 1)";
                $pdo->exec($queryAlerte);

                throw new Exception('Aucun chauffeur disponible. La demande reste en attente.');
            } else {
                // Véhicule et chauffeur disponibles
                $stmtUpdate->execute([
                    ':statut' => 'validee',
                    ':vehicule' => $vehiculeDisponible,
                    ':chauffeur' => $chauffeurDisponible,
                    ':id' => $idReservation
                ]);

                // Mettre à jour le statut du véhicule
                $pdo->prepare("UPDATE vehicules SET statut = 'en_course' WHERE id_vehicule = :id")
                    ->execute([':id' => $vehiculeDisponible]);

                // Mettre à jour le statut du chauffeur
                $pdo->prepare("UPDATE chauffeurs SET statut = 'en_course' WHERE id_chauffeur = :id")
                    ->execute([':id' => $chauffeurDisponible]);
            }
            break;

        case 'refuser':
            $queryUpdate = "UPDATE reservations_vehicules SET statut = 'annulee' WHERE id_reservation = :id";
            $stmtUpdate = $pdo->prepare($queryUpdate);
            $stmtUpdate->execute([':id' => $idReservation]);
            break;

        default:
            throw new Exception('Action invalide');
    }

    // Journal des activités
    $queryJournal = "INSERT INTO journal_activites 
                     (id_utilisateur, type_activite, description) 
                     VALUES (:id_user, :type, :desc)";
    $stmtJournal = $pdo->prepare($queryJournal);
    $stmtJournal->execute([
        ':id_user' => 1, // TODO: Remplacer par l'ID de l'utilisateur connecté
        ':type' => 'reservation_' . $action,
        ':desc' => "Réservation #$idReservation " . 
                   ($action === 'valider' ? 'traitée' : 'annulée')
    ]);

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Demande traitée avec succès',
        'details' => [
            'vehicule_attribue' => $vehiculeDisponible ?? null,
            'chauffeur_attribue' => $chauffeurDisponible ?? null
        ]
    ]);

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données : ' . $e->getMessage(),
        'errorCode' => $e->getCode()
    ]);
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}