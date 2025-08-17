<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier l'authentification
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: auth/views/login.php');
    exit;
}

// Vérifier la période sélectionnée (jour, semaine, mois, année ou personnalisé)
$periode = isset($_GET['periode']) ? $_GET['periode'] : 'mois';
$date_debut = null;
$date_fin = null;

// Définir les dates de début et de fin en fonction de la période sélectionnée
switch ($periode) {
    case 'jour':
        $date_debut = date('Y-m-d 00:00:00');
        $date_fin = date('Y-m-d 23:59:59');
        $titre_periode = "aujourd'hui";
        break;
    case 'semaine':
        $date_debut = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $date_fin = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $titre_periode = "cette semaine";
        break;
    case 'mois':
        $date_debut = date('Y-m-01 00:00:00');
        $date_fin = date('Y-m-t 23:59:59');
        $titre_periode = "ce mois";
        break;
    case 'annee':
        $date_debut = date('Y-01-01 00:00:00');
        $date_fin = date('Y-12-31 23:59:59');
        $titre_periode = "cette année";
        break;
    case 'personnalise':
        if (isset($_GET['date_debut']) && isset($_GET['date_fin'])) {
            $date_debut = $_GET['date_debut'] . ' 00:00:00';
            $date_fin = $_GET['date_fin'] . ' 23:59:59';
            $titre_periode = "du " . date('d/m/Y', strtotime($_GET['date_debut'])) . " au " . date('d/m/Y', strtotime($_GET['date_fin']));
        } else {
            $date_debut = date('Y-m-01 00:00:00');
            $date_fin = date('Y-m-t 23:59:59');
            $titre_periode = "ce mois";
        }
        break;
}

// Récupérer les statistiques globales des véhicules
try {
    $stmt_vehicules = $pdo->prepare("
        SELECT 
            COUNT(*) as total_vehicules,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as vehicules_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as vehicules_en_course,
            SUM(CASE WHEN statut = 'maintenance' THEN 1 ELSE 0 END) as vehicules_maintenance,
            SUM(CASE WHEN statut = 'hors_service' THEN 1 ELSE 0 END) as vehicules_hors_service
        FROM vehicules
    ");
    $stmt_vehicules->execute();
    $stats_vehicules = $stmt_vehicules->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_vehicules = [
        'total_vehicules' => 0,
        'vehicules_disponibles' => 0,
        'vehicules_en_course' => 0,
        'vehicules_maintenance' => 0,
        'vehicules_hors_service' => 0
    ];
}

// Récupérer les statistiques des chauffeurs
try {
    $stmt_chauffeurs = $pdo->prepare("
        SELECT 
            COUNT(*) as total_chauffeurs,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as chauffeurs_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as chauffeurs_en_course,
            SUM(CASE WHEN statut = 'conge' THEN 1 ELSE 0 END) as chauffeurs_en_conge,
            SUM(CASE WHEN statut = 'indisponible' THEN 1 ELSE 0 END) as chauffeurs_indisponibles
        FROM chauffeurs
    ");
    $stmt_chauffeurs->execute();
    $stats_chauffeurs = $stmt_chauffeurs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_chauffeurs = [
        'total_chauffeurs' => 0,
        'chauffeurs_disponibles' => 0,
        'chauffeurs_en_course' => 0,
        'chauffeurs_en_conge' => 0,
        'chauffeurs_indisponibles' => 0
    ];
}

// Récupérer les statistiques des maintenances pour la période
try {
    $stmt_maintenance = $pdo->prepare("
        SELECT 
            COUNT(*) as nombre_maintenances,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as nombre_terminees,
            SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as nombre_en_cours,
            SUM(CASE WHEN statut = 'planifiee' THEN 1 ELSE 0 END) as nombre_planifiees,
            SUM(CASE WHEN statut = 'annulee' THEN 1 ELSE 0 END) as nombre_annulees,
            SUM(cout) as cout_total,
            AVG(cout) as cout_moyen,
            AVG(DATEDIFF(date_fin_effective, date_debut)) as duree_moyenne
        FROM maintenances
        WHERE date_debut BETWEEN :date_debut AND :date_fin
    ");
    $stmt_maintenance->bindParam(':date_debut', $date_debut);
    $stmt_maintenance->bindParam(':date_fin', $date_fin);
    $stmt_maintenance->execute();
    $stats_maintenance = $stmt_maintenance->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_maintenance = [
        'nombre_maintenances' => 0,
        'nombre_terminees' => 0,
        'nombre_en_cours' => 0,
        'nombre_planifiees' => 0,
        'nombre_annulees' => 0,
        'cout_total' => 0,
        'cout_moyen' => 0,
        'duree_moyenne' => 0
    ];
}

// Récupérer les statistiques par type de maintenance
try {
    $stmt_types = $pdo->prepare("
        SELECT 
            type_maintenance,
            COUNT(*) as nombre_maintenances,
            SUM(cout) as cout_total
        FROM maintenances
        WHERE date_debut BETWEEN :date_debut AND :date_fin
        GROUP BY type_maintenance
    ");
    $stmt_types->bindParam(':date_debut', $date_debut);
    $stmt_types->bindParam(':date_fin', $date_fin);
    $stmt_types->execute();
    $stats_types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_types = [];
}

// Récupérer les statistiques d'approvisionnement
try {
    // Cette requête récupère toujours les autres statistiques
    $stmt_carburant = $pdo->prepare("
        SELECT 
            COUNT(*) as nombre_approvisionnements,
            SUM(prix_total) as cout_total,
            AVG(prix_unitaire) as prix_moyen_unitaire
        FROM approvisionnements_carburant
        WHERE date_approvisionnement BETWEEN :date_debut AND :date_fin
    ");
    $stmt_carburant->bindParam(':date_debut', $date_debut);
    $stmt_carburant->bindParam(':date_fin', $date_fin);
    $stmt_carburant->execute();
    $stats_carburant = $stmt_carburant->fetch(PDO::FETCH_ASSOC);

    // Initialiser la quantité totale (sera remplacée plus tard)
    $stats_carburant['quantite_totale'] = 0;
} catch (PDOException $e) {
    $stats_carburant = [
        'nombre_approvisionnements' => 0,
        'quantite_totale' => 0,
        'cout_total' => 0,
        'prix_moyen_unitaire' => 0
    ];
}

// Récupérer les statistiques par type de carburant
try {
    $stmt_type_carburant = $pdo->prepare("
        SELECT 
            type_carburant,
            COUNT(*) as nombre_approvisionnements,
            SUM(quantite_litres) as quantite_totale,
            SUM(prix_total) as cout_total
        FROM approvisionnements_carburant
        WHERE date_approvisionnement BETWEEN :date_debut AND :date_fin
        GROUP BY type_carburant
    ");
    $stmt_type_carburant->bindParam(':date_debut', $date_debut);
    $stmt_type_carburant->bindParam(':date_fin', $date_fin);
    $stmt_type_carburant->execute();
    $stats_type_carburant = $stmt_type_carburant->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcul du total à partir des types de carburant
    $quantite_totale_par_types = 0;
    foreach ($stats_type_carburant as $type) {
        $quantite_totale_par_types += $type['quantite_totale'];
    }
    
    // Utiliser cette valeur comme quantité totale
    $stats_carburant['quantite_totale'] = $quantite_totale_par_types / 100;
    
} catch (PDOException $e) {
    $stats_type_carburant = [];
}

// Récupérer les statistiques de réservations
try {
    $stmt_reservations = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reservations,
            SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN statut = 'validee' THEN 1 ELSE 0 END) as validees,
            SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as terminees,
            SUM(CASE WHEN statut = 'annulee' THEN 1 ELSE 0 END) as annulees
        FROM reservations_vehicules
        WHERE date_demande BETWEEN :date_debut AND :date_fin
    ");
    $stmt_reservations->bindParam(':date_debut', $date_debut);
    $stmt_reservations->bindParam(':date_fin', $date_fin);
    $stmt_reservations->execute();
    $stats_reservations = $stmt_reservations->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_reservations = [
        'total_reservations' => 0,
        'en_attente' => 0,
        'validees' => 0,
        'en_cours' => 0,
        'terminees' => 0,
        'annulees' => 0
    ];
}

// Récupérer les données pour l'évolution mensuelle (12 derniers mois)
try {
    $stmt_evolution = $pdo->prepare("
        SELECT 
            DATE_FORMAT(date_approvisionnement, '%Y-%m') as mois,
            SUM(quantite_litres) as quantite_totale,
            SUM(prix_total) as cout_total
        FROM approvisionnements_carburant
        WHERE date_approvisionnement BETWEEN 
            DATE_SUB(:date_fin, INTERVAL 11 MONTH) AND :date_fin
        GROUP BY DATE_FORMAT(date_approvisionnement, '%Y-%m')
        ORDER BY mois ASC
    ");
    $stmt_evolution->bindParam(':date_fin', $date_fin);
    $stmt_evolution->execute();
    $evolution_carburant = $stmt_evolution->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $evolution_carburant = [];
}

// Convertir les données mensuelles en JSON pour les graphiques
$labels_mois = [];
$data_quantite = [];
$data_cout = [];

foreach ($evolution_carburant as $mois) {
    $labels_mois[] = date('M Y', strtotime($mois['mois'] . '-01'));
    $data_quantite[] = floatval($mois['quantite_totale']);
    $data_cout[] = intval($mois['cout_total']);
}

$json_labels = json_encode($labels_mois);
$json_quantite = json_encode($data_quantite);
$json_cout = json_encode($data_cout);

// Récupérer les données pour l'évolution des maintenances
try {
    $stmt_maint_evolution = $pdo->prepare("
        SELECT 
            DATE_FORMAT(date_debut, '%Y-%m') as mois,
            COUNT(*) as nombre_maintenances,
            SUM(cout) as cout_total
        FROM maintenances
        WHERE date_debut BETWEEN 
            DATE_SUB(:date_fin, INTERVAL 11 MONTH) AND :date_fin
        GROUP BY DATE_FORMAT(date_debut, '%Y-%m')
        ORDER BY mois ASC
    ");
    $stmt_maint_evolution->bindParam(':date_fin', $date_fin);
    $stmt_maint_evolution->execute();
    $evolution_maintenance = $stmt_maint_evolution->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $evolution_maintenance = [];
}

// Convertir les données de maintenance mensuelles en JSON pour les graphiques
$maint_labels = [];
$maint_nombre = [];
$maint_cout = [];

foreach ($evolution_maintenance as $mois) {
    $maint_labels[] = date('M Y', strtotime($mois['mois'] . '-01'));
    $maint_nombre[] = intval($mois['nombre_maintenances']);
    $maint_cout[] = intval($mois['cout_total']);
}

$json_maint_labels = json_encode($maint_labels);
$json_maint_nombre = json_encode($maint_nombre);
$json_maint_cout = json_encode($maint_cout);

// Récupérer les données pour les réservations mensuelles
try {
    $stmt_reserv_evolution = $pdo->prepare("
        SELECT 
            DATE_FORMAT(date_demande, '%Y-%m') as mois,
            COUNT(*) as nombre_reservations,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as terminees
        FROM reservations_vehicules
        WHERE date_demande BETWEEN 
            DATE_SUB(:date_fin, INTERVAL 11 MONTH) AND :date_fin
        GROUP BY DATE_FORMAT(date_demande, '%Y-%m')
        ORDER BY mois ASC
    ");
    $stmt_reserv_evolution->bindParam(':date_fin', $date_fin);
    $stmt_reserv_evolution->execute();
    $evolution_reservations = $stmt_reserv_evolution->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $evolution_reservations = [];
}

// Convertir les données de réservation mensuelles en JSON pour les graphiques
$reserv_labels = [];
$reserv_nombre = [];
$reserv_terminees = [];

foreach ($evolution_reservations as $mois) {
    $reserv_labels[] = date('M Y', strtotime($mois['mois'] . '-01'));
    $reserv_nombre[] = intval($mois['nombre_reservations']);
    $reserv_terminees[] = intval($mois['terminees']);
}

$json_reserv_labels = json_encode($reserv_labels);
$json_reserv_nombre = json_encode($reserv_nombre);
$json_reserv_terminees = json_encode($reserv_terminees);

// Inclure le header
include_once("includes" . DIRECTORY_SEPARATOR . "header.php");

// On vérifie que l'objet $roleAccess est bien défini (il devrait l'être dans header.php)
if (!isset($roleAccess)) {
    require_once 'includes/RoleAccess.php';
    $roleAccess = new RoleAccess($_SESSION['role']);
}

// Vérifier si l'utilisateur a les permissions nécessaires
if (!$roleAccess->hasPermission('tracking')) {
    echo '<div class="alert alert-danger">Vous n\'avez pas les permissions nécessaires pour accéder à cette page.</div>';
    include_once("includes" . DIRECTORY_SEPARATOR . "footer.php");
    exit;
}
?>

<!-- Inclusion des styles spécifiques pour les visualisations -->
<link rel="stylesheet" href="assets/css/dashboard-stats.css">
<link rel="stylesheet" href="assets/css/statistiques_globales.css">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-pie me-2"></i>Statistiques globales</h1>
        <div>
            <button id="printBtn" class="btn btn-outline-secondary me-2">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
            <div class="btn-group me-2">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="#" id="exportPDF"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                    <li><a class="dropdown-item" href="#" id="exportExcel"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="#" id="exportCSV"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                </ul>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Filtres de période -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrer par période</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET" class="row g-3 align-items-end">
                <div class="col-md-auto">
                    <div class="btn-group" role="group">
                        <a href="?periode=jour" class="btn btn-outline-primary <?= $periode == 'jour' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-day me-1"></i>Jour
                        </a>
                        <a href="?periode=semaine" class="btn btn-outline-primary <?= $periode == 'semaine' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-week me-1"></i>Semaine
                        </a>
                        <a href="?periode=mois" class="btn btn-outline-primary <?= $periode == 'mois' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-alt me-1"></i>Mois
                        </a>
                        <a href="?periode=annee" class="btn btn-outline-primary <?= $periode == 'annee' ? 'active' : '' ?>">
                            <i class="fas fa-calendar me-1"></i>Année
                        </a>
                    </div>
                </div>
                <div class="col-md-auto">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#collapsePersonnalise">
                        <i class="fas fa-cog me-1"></i>Personnaliser
                    </button>
                </div>
                
                <div class="col-12 collapse <?= $periode == 'personnalise' ? 'show' : '' ?>" id="collapsePersonnalise">
                    <div class="card card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                      value="<?= isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                      value="<?= isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <input type="hidden" name="periode" value="personnalise">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Appliquer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- SECTION TABLEAU DE BORD STATISTIQUE -->
    <div class="dashboard-stats-container mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Tableau de bord statistique <?= $titre_periode ?></h5>
            </div>
            <div class="card-body py-4">
                <!-- Première ligne - Statistiques principales -->
                <div class="row mb-4">
                    <!-- Véhicules -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card vehicles">
                            <div class="stat-card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="stat-card-title">Véhicules</h6>
                                        <div class="d-flex align-items-baseline">
                                            <h2 class="stat-value-main"><?= $stats_vehicules['total_vehicules'] ?></h2>
                                            <span class="badge bg-success ms-2"><?= $stats_vehicules['total_vehicules'] > 0 ? round(($stats_vehicules['vehicules_disponibles'] / $stats_vehicules['total_vehicules']) * 100) : 0 ?>%</span>
                                        </div>
                                        <div class="stat-trend"><i class="fas fa-car me-1"></i> <?= $stats_vehicules['vehicules_disponibles'] ?> disponibles</div>
                                    </div>
                                    <div class="stat-icon-container">
                                        <div class="stat-icon bg-primary-soft">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-progress mt-3">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $stats_vehicules['total_vehicules'] > 0 ? ($stats_vehicules['vehicules_disponibles'] / $stats_vehicules['total_vehicules']) * 100 : 0 ?>%"></div>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $stats_vehicules['total_vehicules'] > 0 ? ($stats_vehicules['vehicules_en_course'] / $stats_vehicules['total_vehicules']) * 100 : 0 ?>%"></div>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $stats_vehicules['total_vehicules'] > 0 ? ($stats_vehicules['vehicules_maintenance'] / $stats_vehicules['total_vehicules']) * 100 : 0 ?>%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 small">
                                        <span>En service: <?= $stats_vehicules['total_vehicules'] > 0 ? round((($stats_vehicules['vehicules_disponibles'] + $stats_vehicules['vehicules_en_course']) / $stats_vehicules['total_vehicules']) * 100) : 0 ?>%</span>
                                        <span>En maintenance: <?= $stats_vehicules['total_vehicules'] > 0 ? round(($stats_vehicules['vehicules_maintenance'] / $stats_vehicules['total_vehicules']) * 100) : 0 ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chauffeurs -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card drivers">
                            <div class="stat-card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="stat-card-title">Chauffeurs</h6>
                                        <div class="d-flex align-items-baseline">
                                            <h2 class="stat-value-main"><?= $stats_chauffeurs['total_chauffeurs'] ?></h2>
                                            <span class="badge bg-success ms-2"><?= $stats_chauffeurs['total_chauffeurs'] > 0 ? round(($stats_chauffeurs['chauffeurs_disponibles'] / $stats_chauffeurs['total_chauffeurs']) * 100) : 0 ?>%</span>
                                        </div>
                                        <div class="stat-trend"><i class="fas fa-user-check me-1"></i> <?= $stats_chauffeurs['chauffeurs_disponibles'] ?> actifs</div>
                                    </div>
                                    <div class="stat-icon-container">
                                        <div class="stat-icon bg-info-soft">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-progress mt-3">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $stats_chauffeurs['total_chauffeurs'] > 0 ? ($stats_chauffeurs['chauffeurs_disponibles'] / $stats_chauffeurs['total_chauffeurs']) * 100 : 0 ?>%"></div>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $stats_chauffeurs['total_chauffeurs'] > 0 ? ($stats_chauffeurs['chauffeurs_en_course'] / $stats_chauffeurs['total_chauffeurs']) * 100 : 0 ?>%"></div>
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $stats_chauffeurs['total_chauffeurs'] > 0 ? ($stats_chauffeurs['chauffeurs_en_conge'] / $stats_chauffeurs['total_chauffeurs']) * 100 : 0 ?>%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 small">
                                        <span>En service: <?= $stats_chauffeurs['total_chauffeurs'] > 0 ? round((($stats_chauffeurs['chauffeurs_disponibles'] + $stats_chauffeurs['chauffeurs_en_course']) / $stats_chauffeurs['total_chauffeurs']) * 100) : 0 ?>%</span>
                                        <span>En congé: <?= $stats_chauffeurs['total_chauffeurs'] > 0 ? round(($stats_chauffeurs['chauffeurs_en_conge'] / $stats_chauffeurs['total_chauffeurs']) * 100) : 0 ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Réservations -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card trips">
                            <div class="stat-card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="stat-card-title">Réservations</h6>
                                        <div class="d-flex align-items-baseline">
                                            <h2 class="stat-value-main"><?= $stats_reservations['total_reservations'] ?></h2>
                                            <span class="badge bg-primary ms-2"><?= $stats_reservations['total_reservations'] > 0 ? round((($stats_reservations['en_attente'] + $stats_reservations['en_cours']) / $stats_reservations['total_reservations']) * 100) : 0 ?>%</span>
                                        </div>
                                        <div class="stat-trend"><i class="fas fa-calendar-check me-1"></i> <?= $stats_reservations['terminees'] ?> terminées</div>
                                    </div>
                                    <div class="stat-icon-container">
                                        <div class="stat-icon bg-warning-soft">
                                            <i class="fas fa-route"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-card-body">                                    <div class="stat-progress mt-3">
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $stats_reservations['total_reservations'] > 0 ? ($stats_reservations['terminees'] / $stats_reservations['total_reservations']) * 100 : 0 ?>%"></div>
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $stats_reservations['total_reservations'] > 0 ? ($stats_reservations['en_cours'] / $stats_reservations['total_reservations']) * 100 : 0 ?>%"></div>
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $stats_reservations['total_reservations'] > 0 ? ($stats_reservations['en_attente'] / $stats_reservations['total_reservations']) * 100 : 0 ?>%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1 small">
                                            <span>Terminées: <?= $stats_reservations['total_reservations'] > 0 ? round(($stats_reservations['terminees'] / $stats_reservations['total_reservations']) * 100) : 0 ?>%</span>
                                            <span>En attente: <?= $stats_reservations['total_reservations'] > 0 ? round(($stats_reservations['en_attente'] / $stats_reservations['total_reservations']) * 100) : 0 ?>%</span>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Maintenances -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card maintenance">
                            <div class="stat-card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="stat-card-title">Maintenances</h6>
                                        <div class="d-flex align-items-baseline">
                                            <h2 class="stat-value-main"><?= $stats_maintenance['nombre_maintenances'] ?></h2>
                                            <span class="badge bg-warning ms-2"><?= $stats_maintenance['nombre_maintenances'] > 0 ? round((($stats_maintenance['nombre_en_cours'] + $stats_maintenance['nombre_planifiees']) / $stats_maintenance['nombre_maintenances']) * 100) : 0 ?>%</span>
                                        </div>
                                        <div class="stat-trend"><i class="fas fa-wrench me-1"></i> <?= $stats_maintenance['nombre_en_cours'] ?> en cours</div>
                                    </div>
                                    <div class="stat-icon-container">
                                        <div class="stat-icon bg-danger-soft">
                                            <i class="fas fa-tools"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-progress mt-3">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $stats_maintenance['nombre_maintenances'] > 0 ? ($stats_maintenance['nombre_terminees'] / $stats_maintenance['nombre_maintenances']) * 100 : 0 ?>%"></div>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $stats_maintenance['nombre_maintenances'] > 0 ? ($stats_maintenance['nombre_en_cours'] / $stats_maintenance['nombre_maintenances']) * 100 : 0 ?>%"></div>
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $stats_maintenance['nombre_maintenances'] > 0 ? ($stats_maintenance['nombre_planifiees'] / $stats_maintenance['nombre_maintenances']) * 100 : 0 ?>%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 small">
                                        <span>Terminées: <?= $stats_maintenance['nombre_maintenances'] > 0 ? round(($stats_maintenance['nombre_terminees'] / $stats_maintenance['nombre_maintenances']) * 100) : 0 ?>%</span>
                                        <span>Planifiées: <?= $stats_maintenance['nombre_maintenances'] > 0 ? round(($stats_maintenance['nombre_planifiees'] / $stats_maintenance['nombre_maintenances']) * 100) : 0 ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Deuxième ligne - Graphiques -->
                <div class="row">
                    <!-- Graphique des approvisionnements -->
                    <div class="col-lg-6 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-gas-pump me-2"></i>Consommation carburant (12 derniers mois)</h6>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-fuel-chart">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="fuelConsumptionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Graphique des maintenances -->
                    <div class="col-lg-6 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Coûts de maintenance (12 derniers mois)</h6>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-maintenance-chart">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="maintenanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Globales Détaillées -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-gas-pump me-2"></i>Approvisionnements de carburant <?= $titre_periode ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Approvisionnements</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_carburant['nombre_approvisionnements'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">opérations</p>
                                </div>
                            </div>
                        </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Quantité totale</h6>
                                        <h2 class="display-5 fw-bold"><?= number_format($stats_carburant['quantite_totale'], 2, ',', ' ') ?></h2>
                                        <p class="mb-0">litres</p>
                                    </div>
                                </div>
                            </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Coût total</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_carburant['cout_total'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">FCFA</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Prix moyen</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_carburant['prix_moyen_unitaire'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">FCFA/L</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition par type de carburant -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">Répartition par type de carburant</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-end">Quantité (L)</th>
                                        <th class="text-end">Coût (FCFA)</th>
                                        <th class="text-end">% du total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats_type_carburant as $type): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $badge_class = '';
                                                if ($type['type_carburant'] == 'essence') {
                                                    $badge_class = 'bg-success';
                                                } elseif ($type['type_carburant'] == 'diesel') {
                                                    $badge_class = 'bg-primary';
                                                } else {
                                                    $badge_class = 'bg-warning';
                                                }
                                                ?>
                                                <span class="badge <?= $badge_class ?>"><?= ucfirst($type['type_carburant']) ?></span>
                                            </td>
                                            <td class="text-end"><?= number_format($type['quantite_totale'], 2, ',', ' ') ?></td>
                                            <td class="text-end"><?= number_format($type['cout_total'], 0, ',', ' ') ?></td>
                                            <td class="text-end">
                                                <?= $stats_carburant['cout_total'] > 0 ? number_format(($type['cout_total'] / $stats_carburant['cout_total']) * 100, 1, ',', ' ') : 0 ?>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Maintenances <?= $titre_periode ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Maintenances</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_maintenance['nombre_maintenances'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">interventions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Coût total</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_maintenance['cout_total'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">FCFA</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Coût moyen</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_maintenance['cout_moyen'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">FCFA</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Durée moyenne</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_maintenance['duree_moyenne'], 1, ',', ' ') ?></h2>
                                    <p class="mb-0">jours</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition par type de maintenance -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">Répartition par type de maintenance</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-end">Nombre</th>
                                        <th class="text-end">Coût (FCFA)</th>
                                        <th class="text-end">% du total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats_types as $type): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $badge_class = '';
                                                if ($type['type_maintenance'] == 'preventive') {
                                                    $badge_class = 'bg-success';
                                                } elseif ($type['type_maintenance'] == 'corrective') {
                                                    $badge_class = 'bg-danger';
                                                } else {
                                                    $badge_class = 'bg-primary';
                                                }
                                                ?>
                                                <span class="badge <?= $badge_class ?>"><?= ucfirst($type['type_maintenance']) ?></span>
                                            </td>
                                            <td class="text-end"><?= number_format($type['nombre_maintenances'], 0, ',', ' ') ?></td>
                                            <td class="text-end"><?= number_format($type['cout_total'], 0, ',', ' ') ?></td>
                                            <td class="text-end">
                                                <?= $stats_maintenance['nombre_maintenances'] > 0 ? number_format(($type['nombre_maintenances'] / $stats_maintenance['nombre_maintenances']) * 100, 1, ',', ' ') : 0 ?>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des réservations -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Statistiques des réservations <?= $titre_periode ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Total</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_reservations['total_reservations'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">réservations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-primary bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">En attente</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_reservations['en_attente'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">réservations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-info bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Validées</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_reservations['validees'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">réservations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-warning bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">En cours</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_reservations['en_cours'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">réservations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Terminées</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_reservations['terminees'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">réservations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-danger bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Annulées</h6>
                                    <h2 class="display-5 fw-bold"><?= number_format($stats_reservations['annulees'], 0, ',', ' ') ?></h2>
                                    <p class="mb-0">réservations</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique d'évolution des réservations -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">Évolution des réservations (12 derniers mois)</h6>
                            <div class="chart-container">
                                <canvas id="reservationsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liens rapides vers d'autres statistiques -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Statistiques détaillées</h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-md-4 mb-3">
                            <a href="statistiques_approvisionnements.php?periode=<?= $periode ?>" class="btn btn-outline-success w-100 p-4">
                                <i class="fas fa-gas-pump fa-2x mb-2"></i>
                                <div>Statistiques d'approvisionnement</div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="statistiques_maintenances.php?periode=<?= $periode ?>" class="btn btn-outline-danger w-100 p-4">
                                <i class="fas fa-tools fa-2x mb-2"></i>
                                <div>Statistiques de maintenance</div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="statistiques.php" class="btn btn-outline-primary w-100 p-4">
                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                <div>Tableau de bord temps réel</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ajout des scripts JavaScript nécessaires -->
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
<script src="assets/js/index/dashboard-stats-overview.js"></script>

<script>
// Données pour les graphiques
const fuelConsumptionLabels = <?= $json_labels ?>;
const fuelConsumptionQuantity = <?= $json_quantite ?>;
const fuelConsumptionCost = <?= $json_cout ?>;

const maintenanceLabels = <?= $json_maint_labels ?>;
const maintenanceCount = <?= $json_maint_nombre ?>;
const maintenanceCost = <?= $json_maint_cout ?>;

const reservationLabels = <?= $json_reserv_labels ?>;
const reservationCount = <?= $json_reserv_nombre ?>;
const reservationCompleted = <?= $json_reserv_terminees ?>;

// Graphique de consommation de carburant
const fuelCtx = document.getElementById('fuelConsumptionChart').getContext('2d');
const fuelChart = new Chart(fuelCtx, {
    type: 'line',
    data: {
        labels: fuelConsumptionLabels,
        datasets: [{
            label: 'Quantité (L)',
            data: fuelConsumptionQuantity,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            yAxisID: 'y-quantity'
        }, {
            label: 'Coût (FCFA)',
            data: fuelConsumptionCost,
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            yAxisID: 'y-cost'
        }]
    },
    options: {
    responsive: true,
    maintainAspectRatio: true, // Changé à true
    interaction: {
        mode: 'index',
        intersect: false,
    },
    scales: {
        'y-quantity': {
            type: 'linear',
            position: 'left',
            title: {
                display: true,
                text: 'Quantité (L)',
                font: { weight: 'bold' }
            },
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' }
        },
        'y-cost': {
            type: 'linear',
            position: 'right',
            title: {
                display: true,
                text: 'Coût (FCFA)',
                font: { weight: 'bold' }
            },
            beginAtZero: true,
            grid: { drawOnChartArea: false }
        },
        x: {
            grid: { color: 'rgba(0,0,0,0.05)' }
        }
    },
    plugins: {
        legend: {
            position: 'top',
            labels: { usePointStyle: true }
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) label += ': ';
                    if (context.datasetIndex === 0) {
                        label += new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2 }).format(context.raw) + ' L';
                    } else {
                        label += new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 }).format(context.raw);
                    }
                    return label;
                }
            }
        }
    },
    animation: {
        duration: 500 // Réduire la durée d'animation
    }
}
});

// Graphique des coûts de maintenance
const maintenanceCtx = document.getElementById('maintenanceChart').getContext('2d');
const maintenanceChart = new Chart(maintenanceCtx, {
    type: 'line',
    data: {
        labels: maintenanceLabels,
        datasets: [{
            label: 'Nombre de maintenances',
            data: maintenanceCount,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            yAxisID: 'y-count'
        }, {
            label: 'Coût total (FCFA)',
            data: maintenanceCost,
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            yAxisID: 'y-cost'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            'y-count': {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Nombre',
                    font: { weight: 'bold' }
                },
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            'y-cost': {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Coût (FCFA)',
                    font: { weight: 'bold' }
                },
                beginAtZero: true,
                grid: { drawOnChartArea: false }
            },
            x: {
                grid: { color: 'rgba(0,0,0,0.05)' }
            }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: { usePointStyle: true }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.datasetIndex === 0) {
                            label += new Intl.NumberFormat('fr-FR').format(context.raw);
                        } else {
                            label += new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 }).format(context.raw);
                        }
                        return label;
                    }
                }
            }
        }
    }
});

// Graphique des réservations
const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
const reservationsChart = new Chart(reservationsCtx, {
    type: 'bar',
    data: {
        labels: reservationLabels,
        datasets: [{
            label: 'Nombre total',
            data: reservationCount,
            backgroundColor: 'rgba(255, 193, 7, 0.5)',
            borderColor: 'rgb(255, 193, 7)',
            borderWidth: 1
        }, {
            label: 'Terminées',
            data: reservationCompleted,
            backgroundColor: 'rgba(40, 167, 69, 0.5)',
            borderColor: 'rgb(40, 167, 69)',
            borderWidth: 1
        }]
    },
    options: {
    responsive: true,
    maintainAspectRatio: true, // Changé à true
    scales: {
        y: {
            beginAtZero: true,
            title: {
                display: true,
                text: 'Nombre de réservations',
                font: { weight: 'bold' }
            }
        },
        x: {
            grid: { display: false }
        }
    },
    plugins: {
        legend: {
            position: 'top',
            labels: { usePointStyle: true }
        },
        tooltip: {
            mode: 'index',
            intersect: false
        }
    },
    animation: {
        duration: 500 // Réduire la durée d'animation
    }
}
});

// Gestion des événements
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'impression
    document.getElementById('printBtn').addEventListener('click', function() {
        window.print();
    });
    
    // Gestion de l'exportation en PDF
    document.getElementById('exportPDF').addEventListener('click', function(e) {
        e.preventDefault();
        alert('Fonctionnalité d\'export PDF en cours d\'implémentation');
        // Ici, vous pouvez ajouter le code pour l'exportation en PDF
    });
    
    // Gestion de l'exportation en Excel
    document.getElementById('exportExcel').addEventListener('click', function(e) {
        e.preventDefault();
        exportToExcel();
    });
    
    // Gestion de l'exportation en CSV
    document.getElementById('exportCSV').addEventListener('click', function(e) {
        e.preventDefault();
        exportToCSV();
    });
    
    // Rafraîchissement des graphiques
    document.getElementById('refresh-fuel-chart').addEventListener('click', function() {
        fuelChart.update();
    });
    
    document.getElementById('refresh-maintenance-chart').addEventListener('click', function() {
        maintenanceChart.update();
    });
    
    // Personnalisation de la période
    document.querySelector('button[data-bs-target="#collapsePersonnalise"]').addEventListener('click', function() {
        const collapseElement = document.getElementById('collapsePersonnalise');
        const isVisible = collapseElement.classList.contains('show');
        
        if (!isVisible) {
            // Si on affiche le collapse, on met à jour l'input caché
            const periodeInput = document.querySelector('input[name="periode"]');
            periodeInput.value = 'personnalise';
        }
    });
    
    // Animation des statistiques
    animateCounters();
});

/**
 * Anime les compteurs dans les cartes de statistiques
 */
function animateCounters() {
    const counterElements = document.querySelectorAll('.display-5');
    
    counterElements.forEach(counter => {
        const finalValue = parseFloat(counter.textContent.replace(/[^\d.-]/g, ''));
        
        // Déterminer si c'est une valeur monétaire
        const isCurrency = counter.textContent.includes('FCFA');
        
        // Déterminer si c'est une valeur décimale
        const isDecimal = counter.textContent.includes(',');
        
        // Configurer l'animation
        const duration = 1000; // 1 seconde
        const steps = 20;
        const stepValue = finalValue / steps;
        let currentStep = 0;
        
        counter.textContent = '0';
        
        // Animation
        const interval = setInterval(() => {
            currentStep++;
            const value = stepValue * currentStep;
            
            // Formater la valeur selon son type
            if (isCurrency) {
                counter.textContent = new Intl.NumberFormat('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(value) + ' FCFA';
            } else if (isDecimal) {
                counter.textContent = new Intl.NumberFormat('fr-FR', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1
                }).format(value);
            } else {
                counter.textContent = new Intl.NumberFormat('fr-FR').format(value);
            }
            
            if (currentStep >= steps) {
                clearInterval(interval);
                // S'assurer que la valeur finale est exacte
                if (isCurrency) {
                    counter.textContent = new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(finalValue) + ' FCFA';
                } else if (isDecimal) {
                    counter.textContent = new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 1,
                        maximumFractionDigits: 1
                    }).format(finalValue);
                } else {
                    counter.textContent = new Intl.NumberFormat('fr-FR').format(finalValue);
                }
            }
        }, duration / steps);
    });
}

/**
 * Exporter les données en Excel
 */
function exportToExcel() {
    // Récupération des données des tableaux
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Ajouter un en-tête
    csvContent += "Statistiques globales - Période: " + document.querySelector('h5').textContent.split('statistique ')[1] + "\r\n\r\n";
    
    // Ajouter les statistiques de carburant
    csvContent += "STATISTIQUES DE CARBURANT\r\n";
    csvContent += "Approvisionnements,Quantité totale (L),Coût total (FCFA),Prix moyen (FCFA/L)\r\n";
    csvContent += document.querySelectorAll('.col-md-6:first-child .display-5')[0].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:first-child .display-5')[1].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:first-child .display-5')[2].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:first-child .display-5')[3].textContent.trim() + "\r\n\r\n";
    
    // Ajouter les statistiques de maintenance
    csvContent += "STATISTIQUES DE MAINTENANCE\r\n";
    csvContent += "Maintenances,Coût total (FCFA),Coût moyen (FCFA),Durée moyenne (jours)\r\n";
    csvContent += document.querySelectorAll('.col-md-6:nth-child(2) .display-5')[0].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:nth-child(2) .display-5')[1].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:nth-child(2) .display-5')[2].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:nth-child(2) .display-5')[3].textContent.trim() + "\r\n\r\n";
    
    // Ajouter les statistiques de réservations
    csvContent += "STATISTIQUES DE RÉSERVATIONS\r\n";
    csvContent += "Total,En attente,Validées,En cours,Terminées,Annulées\r\n";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[0].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[1].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[2].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[3].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[4].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[5].textContent.trim() + "\r\n";
    
    // Créer un lien de téléchargement
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "statistiques_globales.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Exporter les données en CSV
 */
function exportToCSV() {
    // Utiliser la même fonction que pour Excel mais avec un autre nom de fichier
    // Récupération des données des tableaux
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Ajouter un en-tête
    csvContent += "Statistiques globales - Période: " + document.querySelector('h5').textContent.split('statistique ')[1] + "\r\n\r\n";
    
    // Ajouter les statistiques de carburant
    csvContent += "STATISTIQUES DE CARBURANT\r\n";
    csvContent += "Approvisionnements,Quantité totale (L),Coût total (FCFA),Prix moyen (FCFA/L)\r\n";
    csvContent += document.querySelectorAll('.col-md-6:first-child .display-5')[0].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:first-child .display-5')[1].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:first-child .display-5')[2].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:first-child .display-5')[3].textContent.trim() + "\r\n\r\n";
    
    // Ajouter les statistiques de maintenance
    csvContent += "STATISTIQUES DE MAINTENANCE\r\n";
    csvContent += "Maintenances,Coût total (FCFA),Coût moyen (FCFA),Durée moyenne (jours)\r\n";
    csvContent += document.querySelectorAll('.col-md-6:nth-child(2) .display-5')[0].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:nth-child(2) .display-5')[1].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:nth-child(2) .display-5')[2].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.col-md-6:nth-child(2) .display-5')[3].textContent.trim() + "\r\n\r\n";
    
    // Ajouter les statistiques de réservations
    csvContent += "STATISTIQUES DE RÉSERVATIONS\r\n";
    csvContent += "Total,En attente,Validées,En cours,Terminées,Annulées\r\n";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[0].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[1].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[2].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[3].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[4].textContent.trim() + ",";
    csvContent += document.querySelectorAll('.row:nth-child(1) .display-5')[5].textContent.trim() + "\r\n";
    
    // Créer un lien de téléchargement
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "statistiques_globales.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php"); ?>