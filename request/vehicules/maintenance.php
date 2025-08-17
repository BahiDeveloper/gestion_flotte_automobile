<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID du véhicule est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: gestion_vehicules.php');
    exit;
}

$vehiculeId = (int) $_GET['id'];

// Récupérer les informations du véhicule
$queryVehicule = "SELECT v.*, z.nom_zone 
                 FROM vehicules v 
                 LEFT JOIN zone_vehicules z ON v.id_zone = z.id 
                 WHERE v.id_vehicule = :id_vehicule";
$stmtVehicule = $pdo->prepare($queryVehicule);
$stmtVehicule->execute(['id_vehicule' => $vehiculeId]);
$vehicule = $stmtVehicule->fetch(PDO::FETCH_ASSOC);

if (!$vehicule) {
    header('Location: gestion_vehicules.php');
    exit;
}

// Récupérer les maintenances en cours pour ce véhicule
$queryMaintenancesEnCours = "SELECT * FROM maintenances 
                            WHERE id_vehicule = :id_vehicule 
                            AND (statut = 'planifiee' OR statut = 'en_cours')
                            ORDER BY date_debut DESC";
$stmtMaintenancesEnCours = $pdo->prepare($queryMaintenancesEnCours);
$stmtMaintenancesEnCours->execute(['id_vehicule' => $vehiculeId]);
$maintenancesEnCours = $stmtMaintenancesEnCours->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'historique des maintenances terminées
$queryMaintenancesTerminees = "SELECT * FROM maintenances 
                              WHERE id_vehicule = :id_vehicule 
                              AND statut = 'terminee'
                              ORDER BY date_fin_effective DESC";
$stmtMaintenancesTerminees = $pdo->prepare($queryMaintenancesTerminees);
$stmtMaintenancesTerminees->execute(['id_vehicule' => $vehiculeId]);
$maintenancesTerminees = $stmtMaintenancesTerminees->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'historique des maintenances annulées
$queryMaintenancesAnnulees = "SELECT * FROM maintenances 
                             WHERE id_vehicule = :id_vehicule 
                             AND statut = 'annulee'
                             ORDER BY date_debut DESC";
$stmtMaintenancesAnnulees = $pdo->prepare($queryMaintenancesAnnulees);
$stmtMaintenancesAnnulees->execute(['id_vehicule' => $vehiculeId]);
$maintenancesAnnulees = $stmtMaintenancesAnnulees->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour obtenir la classe de badge en fonction du statut
function getMaintenanceStatusClass($status)
{
    switch ($status) {
        case 'planifiee':
            return 'bg-secondary';
        case 'en_cours':
            return 'bg-warning';
        case 'terminee':
            return 'bg-success';
        case 'annulee':
            return 'bg-danger';
        default:
            return 'bg-light';
    }
}

// Fonction pour formater le type de maintenance
function formatMaintenanceType($type)
{
    switch ($type) {
        case 'preventive':
            return '<i class="fas fa-shield-alt me-1"></i> Préventive';
        case 'corrective':
            return '<i class="fas fa-wrench me-1"></i> Corrective';
        case 'revision':
            return '<i class="fas fa-sync-alt me-1"></i> Révision';
        default:
            return $type;
    }
}

// Traitement du formulaire d'ajout de maintenance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_maintenance') {
    $type_maintenance = $_POST['type_maintenance'];
    $description = $_POST['description'];
    $date_debut = $_POST['date_debut'];
    $date_fin_prevue = $_POST['date_fin_prevue'];
    $cout_estime = !empty($_POST['cout_estime']) ? $_POST['cout_estime'] : null;
    $kilometrage = !empty($_POST['kilometrage']) ? $_POST['kilometrage'] : null;
    $prestataire = !empty($_POST['prestataire']) ? $_POST['prestataire'] : null;
    $statut = $_POST['statut'];

    try {
        $query = "INSERT INTO maintenances (id_vehicule, type_maintenance, description, date_debut, date_fin_prevue, 
                 cout, kilometrage, prestataire, statut) 
                 VALUES (:id_vehicule, :type_maintenance, :description, :date_debut, :date_fin_prevue, 
                 :cout, :kilometrage, :prestataire, :statut)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'id_vehicule' => $vehiculeId,
            'type_maintenance' => $type_maintenance,
            'description' => $description,
            'date_debut' => $date_debut,
            'date_fin_prevue' => $date_fin_prevue,
            'cout' => $cout_estime,
            'kilometrage' => $kilometrage,
            'prestataire' => $prestataire,
            'statut' => $statut
        ]);

        // Si la maintenance commence immédiatement, mettre à jour le statut du véhicule
        if ($statut == 'en_cours') {
            $updateVehicleQuery = "UPDATE vehicules SET statut = 'maintenance' WHERE id_vehicule = :id_vehicule";
            $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
            $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);
        }

        // Enregistrer dans le journal d'activités
        $queryLog = "INSERT INTO journal_activites (id_utilisateur, type_activite, description) 
                    VALUES (:id_utilisateur, 'maintenance', :description)";
        $stmtLog = $pdo->prepare($queryLog);
        $stmtLog->execute([
            'id_utilisateur' => 1, // À remplacer par l'ID de l'utilisateur connecté
            'description' => "Ajout d'une maintenance {$type_maintenance} pour le véhicule " . $vehicule['immatriculation']
        ]);

        $successMessage = "La maintenance a été ajoutée avec succès.";
        header("Location: maintenance_vehicule.php?id=" . $vehiculeId . "&success=1");
        exit;

    } catch (PDOException $e) {
        $errorMessage = "Erreur lors de l'ajout de la maintenance : " . $e->getMessage();
    }
}

// Traitement pour terminer une maintenance
if (isset($_GET['action']) && $_GET['action'] === 'terminer' && isset($_GET['maintenance_id'])) {
    $maintenanceId = (int) $_GET['maintenance_id'];

    try {
        // Mettre à jour la maintenance
        $query = "UPDATE maintenances 
                 SET statut = 'terminee', date_fin_effective = NOW() 
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
            $updateVehicleQuery = "UPDATE vehicules SET statut = 'disponible' WHERE id_vehicule = :id_vehicule";
            $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
            $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);
        }

        $successMessage = "La maintenance a été marquée comme terminée.";
        header("Location: maintenance_vehicule.php?id=" . $vehiculeId . "&success=1");
        exit;

    } catch (PDOException $e) {
        $errorMessage = "Erreur lors de la mise à jour de la maintenance : " . $e->getMessage();
    }
}

// Traitement pour annuler une maintenance
if (isset($_GET['action']) && $_GET['action'] === 'annuler' && isset($_GET['maintenance_id'])) {
    $maintenanceId = (int) $_GET['maintenance_id'];

    try {
        // Mettre à jour la maintenance
        $query = "UPDATE maintenances 
                 SET statut = 'annulee' 
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
            $updateVehicleQuery = "UPDATE vehicules SET statut = 'disponible' WHERE id_vehicule = :id_vehicule";
            $updateVehicleStmt = $pdo->prepare($updateVehicleQuery);
            $updateVehicleStmt->execute(['id_vehicule' => $vehiculeId]);
        }

        $successMessage = "La maintenance a été annulée.";
        header("Location: maintenance_vehicule.php?id=" . $vehiculeId . "&success=1");
        exit;

    } catch (PDOException $e) {
        $errorMessage = "Erreur lors de l'annulation de la maintenance : " . $e->getMessage();
    }
}

?>