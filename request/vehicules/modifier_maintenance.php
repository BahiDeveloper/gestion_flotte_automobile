<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID de maintenance est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: gestion_vehicules.php');
    exit;
}

$maintenanceId = (int)$_GET['id'];

// Récupérer les détails de la maintenance avec les informations du véhicule
$query = "SELECT m.*, v.id_vehicule, v.immatriculation, v.marque, v.modele, v.logo_marque_vehicule, 
         v.statut AS statut_vehicule, z.nom_zone
         FROM maintenances m
         LEFT JOIN vehicules v ON m.id_vehicule = v.id_vehicule
         LEFT JOIN zone_vehicules z ON v.id_zone = z.id
         WHERE m.id_maintenance = :id_maintenance";
$stmt = $pdo->prepare($query);
$stmt->execute(['id_maintenance' => $maintenanceId]);
$maintenance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$maintenance) {
    header('Location: gestion_vehicules.php?error=maintenance_not_found');
    exit;
}

$vehiculeId = $maintenance['id_vehicule'];

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // Rediriger vers le script d'action pour le traitement
        $_POST['id_maintenance'] = $maintenanceId;
        $_POST['action'] = 'update_maintenance';
        
        // Stocker les données en session pour les récupérer dans le script d'action
        session_start();
        $_SESSION['maintenance_data'] = $_POST;
        
        // Ajouter la méthode POST dans l'URL
        header("Location: actions/vehicules/maintenance_vehicule.php?id=" . $vehiculeId . "&method=POST");
        exit;
    } catch (Exception $e) {
        $errorMessage = "Erreur lors de la mise à jour: " . $e->getMessage();
    }
}

// Fonction pour formater les dates pour l'affichage dans les inputs
function formatDateForInput($date) {
    return $date ? date('Y-m-d', strtotime($date)) : '';
}

// Déterminer les états possibles en fonction du statut actuel
$statutsDisponibles = [];
switch ($maintenance['statut']) {
    case 'planifiee':
        $statutsDisponibles = ['planifiee', 'en_cours', 'annulee'];
        break;
    case 'en_cours':
        $statutsDisponibles = ['en_cours', 'terminee'];
        break;
    case 'terminee':
        $statutsDisponibles = ['terminee'];
        break;
    case 'annulee':
        $statutsDisponibles = ['annulee'];
        break;
    default:
        $statutsDisponibles = ['planifiee', 'en_cours', 'terminee', 'annulee'];
}

// Fonction pour obtenir la classe de badge en fonction du statut
function getMaintenanceStatusClass($status) {
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
function formatMaintenanceType($type) {
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
?>