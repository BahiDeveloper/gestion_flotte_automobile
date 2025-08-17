<?php
/**
 * Traitement des actions de maintenance des véhicules
 * 
 * Ce fichier gère les opérations liées aux maintenances:
 * - Ajout d'une maintenance
 * - Fin de maintenance
 * - Annulation de maintenance
 * - Mise à jour de maintenance
 */

// Inclure le fichier de configuration de la base de données
include_once("../../database" . DIRECTORY_SEPARATOR . "config.php");

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// // Vérifier que l'ID du véhicule est fourni
// if (!isset($_GET['id']) || empty($_GET['id'])) {
//     header('Location: ../../gestion_vehicules.php?error=missing_vehicle_id');
//     exit;
// }

// Près du début du fichier maintenance_vehicule.php, juste après la vérification de l'ID dans GET
// Vérifier que l'ID du véhicule est fourni, d'abord dans GET puis dans POST
if ((!isset($_GET['id']) || empty($_GET['id']))) {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Si l'ID est dans le POST, l'utiliser
        $vehiculeId = (int) $_POST['id'];
    } else if (isset($_POST['maintenance_id']) && !empty($_POST['maintenance_id'])) {
        // Si seul l'ID de maintenance est fourni, récupérer l'ID du véhicule
        $maintenanceId = (int) $_POST['maintenance_id'];
        try {
            $queryGetVehicle = "SELECT id_vehicule FROM maintenances WHERE id_maintenance = :maintenance_id";
            $stmtGetVehicle = $pdo->prepare($queryGetVehicle);
            $stmtGetVehicle->execute(['maintenance_id' => $maintenanceId]);
            $vehicleInfo = $stmtGetVehicle->fetch(PDO::FETCH_ASSOC);
            
            if ($vehicleInfo) {
                $vehiculeId = $vehicleInfo['id_vehicule'];
            } else {
                header('Location: ../../gestion_vehicules.php?error=vehicle_not_found_from_maintenance');
                exit;
            }
        } catch (PDOException $e) {
            header('Location: ../../gestion_vehicules.php?error=db_error&message=' . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header('Location: ../../gestion_vehicules.php?error=missing_vehicle_id');
        exit;
    }
} else {
    $vehiculeId = (int) $_GET['id'];
}

// $vehiculeId = (int) $_GET['id'];

// Vérifier que le véhicule existe
$queryCheckVehicule = "SELECT id_vehicule, statut FROM vehicules WHERE id_vehicule = :id_vehicule";
$stmtCheckVehicule = $pdo->prepare($queryCheckVehicule);
$stmtCheckVehicule->execute(['id_vehicule' => $vehiculeId]);
$vehicule = $stmtCheckVehicule->fetch(PDO::FETCH_ASSOC);

if (!$vehicule) {
    header('Location: ../../gestion_vehicules.php?error=vehicle_not_found');
    exit;
}

// var_dump( $_SERVER['REQUEST_METHOD'], $_GET['action']);
// exit;



// Traiter les différentes actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['method']) && $_GET['method'] === 'POST')) {
    // Vérifier si les données sont en session
    session_start();
    if (isset($_SESSION['maintenance_data'])) {
        $_POST = $_SESSION['maintenance_data'];
        unset($_SESSION['maintenance_data']);
    }

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'add_maintenance':
                addMaintenance($pdo, $vehiculeId, $vehicule);
                break;

            case 'update_maintenance':
                updateMaintenance($pdo, $vehiculeId);
                break;

            // Ajouter ce cas qui manque
            case 'terminer':
                if (isset($_POST['maintenance_id'])) {
                    terminerMaintenance($pdo, $vehiculeId, (int) $_POST['maintenance_id']);
                } else {
                    header('Location: ../../maintenance_vehicule.php?id=' . $vehiculeId . '&error=missing_maintenance_id');
                    exit;
                }
                break;

            default:
                // Pour le débogage
                error_log("Action POST non reconnue: " . $action);
                header('Location: ../../maintenance_vehicule.php?id=' . $vehiculeId . '&error=invalid_action&action=' . urlencode($action));
                exit;
        }
    } else {
        header('Location: ../../maintenance_vehicule.php?id=' . $vehiculeId . '&error=missing_action');
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'terminer':
            if (isset($_GET['maintenance_id'])) {
                terminerMaintenance($pdo, $vehiculeId, (int) $_GET['maintenance_id']);
            } else {
                header('Location: ../../maintenance_vehicule.php?id=' . $vehiculeId . '&error=missing_maintenance_id');
                exit;
            }
            break;

        case 'annuler':
            if (isset($_GET['maintenance_id'])) {
                annulerMaintenance($pdo, $vehiculeId, (int) $_GET['maintenance_id']);
            } else {
                header('Location: ../../maintenance_vehicule.php?id=' . $vehiculeId . '&error=missing_maintenance_id');
                exit;
            }
            break;

        case 'demarrer':
            if (isset($_GET['maintenance_id'])) {
                demarrerMaintenance($pdo, $vehiculeId, (int) $_GET['maintenance_id']);
            } else {
                header('Location: ../../maintenance_vehicule.php?id=' . $vehiculeId . '&error=missing_maintenance_id');
                exit;
            }
            break;

        default:
            header('Location: ../../maintenance_vehicule.php?id=' . $vehiculeId . '&error=invalid_action');
            exit;
    }
} else {
    header('Location: ../../maintenance_vehicule.php?id=' . $vehiculeId . '&error=invalid_request');
    exit;
}

/**
 * Ajouter une nouvelle maintenance
 */
function addMaintenance($pdo, $vehiculeId, $vehicule)
{
    // Récupérer les données du formulaire
    $type_maintenance = $_POST['type_maintenance'];
    $description = $_POST['description'];
    $date_debut = $_POST['date_debut'];
    $date_fin_prevue = $_POST['date_fin_prevue'];
    // $cout_estime = !empty($_POST['cout_estime']) ? $_POST['cout_estime'] : null;
    $kilometrage = !empty($_POST['kilometrage']) ? $_POST['kilometrage'] : null;
    $prestataire = !empty($_POST['prestataire']) ? $_POST['prestataire'] : null;
    $statut = $_POST['statut'];

    try {
        // Démarrer la transaction
        $pdo->beginTransaction();

        // Insérer la nouvelle maintenance
        $query = "INSERT INTO maintenances (id_vehicule, type_maintenance, description, date_debut, date_fin_prevue, 
                  kilometrage, prestataire, statut) 
                 VALUES (:id_vehicule, :type_maintenance, :description, :date_debut, :date_fin_prevue, 
                  :kilometrage, :prestataire, :statut)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'id_vehicule' => $vehiculeId,
            'type_maintenance' => $type_maintenance,
            'description' => $description,
            'date_debut' => $date_debut,
            'date_fin_prevue' => $date_fin_prevue,
            // 'cout' => $cout_estime,
            'kilometrage' => $kilometrage,
            'prestataire' => $prestataire,
            'statut' => $statut
        ]);

        $maintenanceId = $pdo->lastInsertId();

        // Si la maintenance commence immédiatement, mettre à jour le statut du véhicule
        if ($statut == 'en_cours' && $vehicule['statut'] !== 'maintenance') {
            $updateVehicleQuery = "UPDATE vehicules SET statut = 'maintenance', updated_at = NOW() WHERE id_vehicule = :id_vehicule";
            $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
            $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);
        }

        // Enregistrer dans le journal d'activités
        if (isset($_SESSION['id_utilisateur'])) {
            $userId = $_SESSION['id_utilisateur']; // À remplacer par l'ID de l'utilisateur connecté via $_SESSION
            $queryLog = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                    VALUES (:id_utilisateur, 'maintenance', :description, :ip_address)";
            $stmtLog = $pdo->prepare($queryLog);
            $stmtLog->execute([
                'id_utilisateur' => $userId,
                'description' => "Ajout d'une maintenance {$type_maintenance} pour le véhicule ID:{$vehiculeId}",
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        }


        // Valider la transaction
        $pdo->commit();

        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&success=1&message=maintenance_added");
        exit;

    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=db_error&message=" . urlencode($e->getMessage()));
        exit;
    }
}

/**
 * Demarrer une maintenance planifiée 
 */
function demarrerMaintenance($pdo, $vehiculeId, $maintenanceId)
{
    try {
        // Démarrer la transaction
        $pdo->beginTransaction();

        // Vérifier si la maintenance existe, appartient au véhicule et est planifiée
        $checkQuery = "SELECT id_maintenance FROM maintenances 
                      WHERE id_maintenance = :id_maintenance 
                      AND id_vehicule = :id_vehicule 
                      AND statut = 'planifiee'";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([
            'id_maintenance' => $maintenanceId,
            'id_vehicule' => $vehiculeId
        ]);

        if ($checkStmt->rowCount() === 0) {
            $pdo->rollBack();
            header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=invalid_maintenance");
            exit;
        }

        // Mettre à jour la maintenance
        $query = "UPDATE maintenances 
                 SET statut = 'en_cours', date_demarrage = NOW(), updated_at = NOW() 
                 WHERE id_maintenance = :id_maintenance";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_maintenance' => $maintenanceId]);

        // Mettre le véhicule en maintenance
        $updateVehicleQuery = "UPDATE vehicules SET statut = 'maintenance', updated_at = NOW() WHERE id_vehicule = :id_vehicule";
        $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
        $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);

        // Valider la transaction
        $pdo->commit();

        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&success=1&message=maintenance_started");
        exit;

    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=db_error&message=" . urlencode($e->getMessage()));
        exit;
    }
}

/**
 * Terminer une maintenance avec saisie du coût final
 */
function terminerMaintenance($pdo, $vehiculeId, $maintenanceId)
{
    // Déboguer les données reçues
    error_log("terminerMaintenance appelée - vehiculeId: $vehiculeId, maintenanceId: $maintenanceId");
    error_log("POST data: " . print_r($_POST, true));

    // Si le maintenance_id est fourni dans le formulaire, utiliser celui-là
    if (isset($_POST['maintenance_id']) && !empty($_POST['maintenance_id'])) {
        $maintenanceId = (int) $_POST['maintenance_id'];
        error_log("Utilisation du maintenance_id depuis POST: $maintenanceId");
    }
    try {
        // Démarrer la transaction
        $pdo->beginTransaction();

        // Vérifier si la maintenance existe et appartient au véhicule
        $checkQuery = "SELECT * FROM maintenances 
                      WHERE id_maintenance = :id_maintenance 
                      AND id_vehicule = :id_vehicule 
                      AND (statut = 'planifiee' OR statut = 'en_cours')";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([
            'id_maintenance' => $maintenanceId,
            'id_vehicule' => $vehiculeId
        ]);

        $maintenance = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$maintenance) {
            $pdo->rollBack();
            header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=invalid_maintenance");
            exit;
        }

        // Récupérer le coût final si fourni dans le formulaire
        $cout = isset($_POST['cout_final']) && !empty($_POST['cout_final'])
            ? $_POST['cout_final']
            : $maintenance['cout']; // Garde la valeur actuelle si non fournie

        // Récupérer le prestataire s'il est fourni
        $prestataire = isset($_POST['prestataire']) && !empty($_POST['prestataire'])
            ? $_POST['prestataire']
            : $maintenance['prestataire']; // Garde la valeur actuelle si non fournie

        // Notes ou observations optionnelles
        $notes = isset($_POST['notes']) ? $_POST['notes'] : null;

        // Mettre à jour la description si des notes ont été ajoutées
        $description = $maintenance['description'];
        if (!empty($notes)) {
            $description .= "\n\nNotes finales (" . date('d/m/Y') . "): " . $notes;
        }

        // Mettre à jour la maintenance
        $query = "UPDATE maintenances 
                 SET statut = 'terminee', 
                     date_fin_effective = CURDATE(), 
                     cout = :cout,
                     prestataire = :prestataire,
                     description = :description,
                     updated_at = NOW() 
                 WHERE id_maintenance = :id_maintenance";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'id_maintenance' => $maintenanceId,
            'cout' => $cout,
            'prestataire' => $prestataire,
            'description' => $description
        ]);

        // Vérifier s'il reste d'autres maintenances en cours pour ce véhicule
        $checkQuery = "SELECT COUNT(*) FROM maintenances 
                      WHERE id_vehicule = :id_vehicule 
                      AND (statut = 'planifiee' OR statut = 'en_cours')";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute(['id_vehicule' => $vehiculeId]);
        $remainingMaintenances = $checkStmt->fetchColumn();

        // Si aucune autre maintenance en cours, remettre le véhicule comme disponible
        if ($remainingMaintenances == 0) {
            $updateVehicleQuery = "UPDATE vehicules SET statut = 'disponible', updated_at = NOW() WHERE id_vehicule = :id_vehicule";
            $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
            $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);
        }

        // Enregistrer dans le journal d'activités
        if (isset($_SESSION['id_utilisateur'])) {
            $userId = $_SESSION['id_utilisateur']; // À remplacer par l'ID de l'utilisateur connecté via $_SESSION
            $queryLog = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                    VALUES (:id_utilisateur, 'maintenance', :description, :ip_address)";
            $stmtLog = $pdo->prepare($queryLog);
            $stmtLog->execute([
                'id_utilisateur' => $userId,
                'description' => "Fin de maintenance ID:{$maintenanceId} pour le véhicule ID:{$vehiculeId} - Coût final: {$cout} FCFA",
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        }

        // Valider la transaction
        $pdo->commit();

        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&success=1&message=maintenance_completed");
        exit;

    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=db_error&message=" . urlencode($e->getMessage()));
        exit;
    }
}

/**
 * Annuler une maintenance
 */
function annulerMaintenance($pdo, $vehiculeId, $maintenanceId)
{
    try {
        // Démarrer la transaction
        $pdo->beginTransaction();

        // Vérifier si la maintenance existe et appartient au véhicule
        $checkQuery = "SELECT id_maintenance FROM maintenances 
                      WHERE id_maintenance = :id_maintenance 
                      AND id_vehicule = :id_vehicule 
                      AND (statut = 'planifiee' OR statut = 'en_cours')";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([
            'id_maintenance' => $maintenanceId,
            'id_vehicule' => $vehiculeId
        ]);

        if ($checkStmt->rowCount() === 0) {
            $pdo->rollBack();
            header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=invalid_maintenance");
            exit;
        }

        // Mettre à jour la maintenance
        $query = "UPDATE maintenances 
                 SET statut = 'annulee', updated_at = NOW() 
                 WHERE id_maintenance = :id_maintenance";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_maintenance' => $maintenanceId]);

        // Vérifier s'il reste d'autres maintenances en cours pour ce véhicule
        $checkQuery = "SELECT COUNT(*) FROM maintenances 
                      WHERE id_vehicule = :id_vehicule 
                      AND (statut = 'planifiee' OR statut = 'en_cours')";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute(['id_vehicule' => $vehiculeId]);
        $remainingMaintenances = $checkStmt->fetchColumn();

        // Si aucune autre maintenance en cours, remettre le véhicule comme disponible
        if ($remainingMaintenances == 0) {
            $updateVehicleQuery = "UPDATE vehicules SET statut = 'disponible', updated_at = NOW() WHERE id_vehicule = :id_vehicule";
            $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
            $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);
        }

        // Enregistrer dans le journal d'activités
        if (isset($_SESSION['id_utilisateur'])) {
            $userId = $_SESSION['id_utilisateur']; // À remplacer par l'ID de l'utilisateur connecté via $_SESSION
            $queryLog = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                    VALUES (:id_utilisateur, 'maintenance', :description, :ip_address)";
            $stmtLog = $pdo->prepare($queryLog);
            $stmtLog->execute([
                'id_utilisateur' => $userId,
                'description' => "Annulation de maintenance ID:{$maintenanceId} pour le véhicule ID:{$vehiculeId}",
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        }

        // Valider la transaction
        $pdo->commit();

        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&success=1&message=maintenance_canceled");
        exit;

    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=db_error&message=" . urlencode($e->getMessage()));
        exit;
    }
}

/**
 * Mettre à jour une maintenance
 */
function updateMaintenance($pdo, $vehiculeId)
{
    // Vérifier que l'ID de maintenance est fourni
    if (!isset($_POST['id_maintenance']) || empty($_POST['id_maintenance'])) {
        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=missing_maintenance_id");
        exit;
    }

    $maintenanceId = (int) $_POST['id_maintenance'];
    $type_maintenance = $_POST['type_maintenance'];
    $description = $_POST['description'];
    $date_debut = $_POST['date_debut'];
    $date_fin_prevue = $_POST['date_fin_prevue'];
    $cout = !empty($_POST['cout']) ? $_POST['cout'] : null;
    $kilometrage = !empty($_POST['kilometrage']) ? $_POST['kilometrage'] : null;
    $prestataire = !empty($_POST['prestataire']) ? $_POST['prestataire'] : null;
    $statut = $_POST['statut'];
    $date_fin_effective = !empty($_POST['date_fin_effective']) ? $_POST['date_fin_effective'] : null;

    try {
        // Démarrer la transaction
        $pdo->beginTransaction();

        // Vérifier si la maintenance existe et appartient au véhicule
        $checkQuery = "SELECT statut FROM maintenances 
                      WHERE id_maintenance = :id_maintenance 
                      AND id_vehicule = :id_vehicule";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([
            'id_maintenance' => $maintenanceId,
            'id_vehicule' => $vehiculeId
        ]);

        $currentMaintenance = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentMaintenance) {
            $pdo->rollBack();
            header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=maintenance_not_found");
            exit;
        }

        // Préparer la requête de mise à jour selon le statut
        if ($statut === 'terminee' && $currentMaintenance['statut'] !== 'terminee') {
            $query = "UPDATE maintenances 
                     SET type_maintenance = :type_maintenance, 
                         description = :description, 
                         date_debut = :date_debut, 
                         date_fin_prevue = :date_fin_prevue, 
                         date_fin_effective = CURDATE(), 
                         cout = :cout, 
                         kilometrage = :kilometrage, 
                         prestataire = :prestataire, 
                         statut = :statut,
                         updated_at = NOW()
                     WHERE id_maintenance = :id_maintenance";
        } else {
            $query = "UPDATE maintenances 
                     SET type_maintenance = :type_maintenance, 
                         description = :description, 
                         date_debut = :date_debut, 
                         date_fin_prevue = :date_fin_prevue, 
                         date_fin_effective = :date_fin_effective, 
                         cout = :cout, 
                         kilometrage = :kilometrage, 
                         prestataire = :prestataire, 
                         statut = :statut,
                         updated_at = NOW()
                     WHERE id_maintenance = :id_maintenance";
        }

        $stmt = $pdo->prepare($query);
        $params = [
            'id_maintenance' => $maintenanceId,
            'type_maintenance' => $type_maintenance,
            'description' => $description,
            'date_debut' => $date_debut,
            'date_fin_prevue' => $date_fin_prevue,
            'cout' => $cout,
            'kilometrage' => $kilometrage,
            'prestataire' => $prestataire,
            'statut' => $statut
        ];

        if ($statut !== 'terminee' || $currentMaintenance['statut'] === 'terminee') {
            $params['date_fin_effective'] = $date_fin_effective;
        }

        $stmt->execute($params);

        // Mettre à jour le statut du véhicule si nécessaire
        if ($statut === 'en_cours' && $currentMaintenance['statut'] !== 'en_cours') {
            // Mettre le véhicule en maintenance
            $updateVehicleQuery = "UPDATE vehicules SET statut = 'maintenance', updated_at = NOW() WHERE id_vehicule = :id_vehicule";
            $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
            $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);
        } elseif (
            ($statut === 'terminee' || $statut === 'annulee') &&
            ($currentMaintenance['statut'] === 'en_cours' || $currentMaintenance['statut'] === 'planifiee')
        ) {
            // Vérifier s'il reste d'autres maintenances en cours
            $checkQuery = "SELECT COUNT(*) FROM maintenances 
                          WHERE id_vehicule = :id_vehicule 
                          AND id_maintenance != :id_maintenance
                          AND (statut = 'planifiee' OR statut = 'en_cours')";
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->execute([
                'id_vehicule' => $vehiculeId,
                'id_maintenance' => $maintenanceId
            ]);
            $remainingMaintenances = $checkStmt->fetchColumn();

            // Si aucune autre maintenance en cours, remettre le véhicule comme disponible
            if ($remainingMaintenances == 0) {
                $updateVehicleQuery = "UPDATE vehicules SET statut = 'disponible', updated_at = NOW() WHERE id_vehicule = :id_vehicule";
                $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
                $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);
            }
        }

        // Enregistrer dans le journal d'activités
        if (isset($_SESSION['id_utilisateur'])) {
            $userId = $_SESSION['id_utilisateur']; // À remplacer par l'ID de l'utilisateur connecté via $_SESSION
            $queryLog = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                     VALUES (:id_utilisateur, 'maintenance', :description, :ip_address)";
            $stmtLog = $pdo->prepare($queryLog);
            $stmtLog->execute([
                'id_utilisateur' => $userId,
                'description' => "Mise à jour de la maintenance ID:{$maintenanceId} pour le véhicule ID:{$vehiculeId}",
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        }

        // Valider la transaction
        $pdo->commit();

        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&success=1&message=maintenance_updated");
        exit;

    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        header("Location: ../../maintenance_vehicule.php?id=" . $vehiculeId . "&error=db_error&message=" . urlencode($e->getMessage()));
        exit;
    }
}
?>