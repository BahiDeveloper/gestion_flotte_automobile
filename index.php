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

// On charge les données initiales pour le premier rendu de la page
// Ensuite, les mises à jour seront faites via AJAX
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_vehicules,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as vehicules_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as vehicules_en_course,
            SUM(CASE WHEN statut = 'maintenance' THEN 1 ELSE 0 END) as vehicules_maintenance,
            SUM(CASE WHEN statut = 'hors_service' THEN 1 ELSE 0 END) as vehicules_hors_service
        FROM vehicules
    ");
    $stats_vehicules = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_vehicules = [
        'total_vehicules' => 0,
        'vehicules_disponibles' => 0,
        'vehicules_en_course' => 0,
        'vehicules_maintenance' => 0,
        'vehicules_hors_service' => 0
    ];
}

// Statistiques des chauffeurs
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_chauffeurs,
            SUM(CASE WHEN statut = 'disponible' THEN 1 ELSE 0 END) as chauffeurs_disponibles,
            SUM(CASE WHEN statut = 'en_course' THEN 1 ELSE 0 END) as chauffeurs_en_course,
            SUM(CASE WHEN statut = 'conge' THEN 1 ELSE 0 END) as chauffeurs_en_conge
        FROM chauffeurs
    ");
    $stats_chauffeurs = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_chauffeurs = [
        'total_chauffeurs' => 0,
        'chauffeurs_disponibles' => 0,
        'chauffeurs_en_course' => 0,
        'chauffeurs_en_conge' => 0
    ];
}

// Statistiques des maintenances
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_maintenances,
            SUM(CASE WHEN statut = 'planifiee' THEN 1 ELSE 0 END) as maintenances_planifiees,
            SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as maintenances_en_cours,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as maintenances_terminees
        FROM maintenances
        WHERE date_debut >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stats_maintenances = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_maintenances = [
        'total_maintenances' => 0,
        'maintenances_planifiees' => 0,
        'maintenances_en_cours' => 0,
        'maintenances_terminees' => 0
    ];
}

// Statistiques des réservations
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_reservations,
            SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as reservations_en_attente,
            SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as reservations_en_cours,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as reservations_terminees
        FROM reservations_vehicules
        WHERE date_demande >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stats_reservations = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats_reservations = [
        'total_reservations' => 0,
        'reservations_en_attente' => 0,
        'reservations_en_cours' => 0,
        'reservations_terminees' => 0
    ];
}

// Documents à renouveler bientôt
try {
    $stmt = $pdo->query("
        SELECT d.*, 
            v.marque, v.modele, v.immatriculation,
            DATEDIFF(d.date_expiration, CURDATE()) as jours_restants
        FROM documents_administratifs d
        LEFT JOIN vehicules v ON d.id_vehicule = v.id_vehicule
        WHERE DATEDIFF(d.date_expiration, CURDATE()) <= 60
            AND d.statut != 'expire'
        ORDER BY jours_restants ASC
        LIMIT 5
    ");
    $documents_alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $documents_alerts = [];
}

// Réservations en attente
try {
    $stmt = $pdo->query("
        SELECT r.*, 
            v.marque, v.modele, 
            CONCAT(c.nom, ' ', c.prenoms) as nom_chauffeur
        FROM reservations_vehicules r
        LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
        LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
        WHERE r.statut = 'en_attente'
        ORDER BY r.date_depart ASC
        LIMIT 5
    ");
    $reservations_attente = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reservations_attente = [];
}

// Maintenances en cours
try {
    $stmt = $pdo->query("
        SELECT m.*, 
            v.marque, v.modele, v.immatriculation
        FROM maintenances m
        JOIN vehicules v ON m.id_vehicule = v.id_vehicule
        WHERE m.statut = 'en_cours'
        ORDER BY m.date_debut ASC
        LIMIT 5
    ");
    $maintenances_en_cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $maintenances_en_cours = [];
}

// Inclure le header
include_once("includes" . DIRECTORY_SEPARATOR . "header.php");
?>

<?php
// On vérifie que l'objet $roleAccess est bien défini (il devrait l'être dans header.php)
if (!isset($roleAccess)) {
    require_once 'includes/RoleAccess.php';
    $roleAccess = new RoleAccess($_SESSION['role']);
}
?>

<!-- Inclusion du CSS pour les animations du dashboard -->
<link rel="stylesheet" href="assets/css/dashboard-realtime.css"> 
<link rel="stylesheet" href="assets/css/dashboard-stats.css"> 

<div class="container-fluid py-4">
    <h1 class="text-center mb-4">
        <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
        <small class="text-muted fs-6 d-block mt-2">Mise à jour en temps réel</small>
    </h1>

    <!-- Toast container pour les notifications -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
    
    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <!-- Statistiques véhicules -->
        <div class="col-md-3">
            <div class="card shadow-sm dashboard-card vehicles">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Véhicules disponibles</h6>
                            <h2 class="mb-0" data-stat="vehicules-disponibles"><?= $stats_vehicules['vehicules_disponibles'] ?></h2>
                        </div>
                        <div class="dashboard-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-car"></i>
                        </div>
                    </div>
                    <div class="small text-muted">
                        sur <span data-stat="vehicules-total"><?= $stats_vehicules['total_vehicules'] ?></span> véhicules au total
                    </div>
                </div>
            </div>
        </div>

        <!-- Chauffeurs disponibles -->
        <div class="col-md-3">
            <div class="card shadow-sm dashboard-card drivers">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Chauffeurs disponibles</h6>
                            <h2 class="mb-0" data-stat="chauffeurs-disponibles"><?= $stats_chauffeurs['chauffeurs_disponibles'] ?></h2>
                        </div>
                        <div class="dashboard-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                    <div class="small text-muted">
                        sur <span data-stat="chauffeurs-total"><?= $stats_chauffeurs['total_chauffeurs'] ?></span> chauffeurs au total
                    </div>
                </div>
            </div>
        </div>

       <!-- Véhicules en maintenance -->
       <div class="col-md-3">
            <div class="card shadow-sm dashboard-card maintenance">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">En maintenance</h6>
                            <h2 class="mb-0" data-stat="vehicules-maintenance"><?= $stats_vehicules['vehicules_maintenance'] ?></h2>
                        </div>
                        <div class="dashboard-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                    <div class="small text-muted">
                        véhicules en réparation
                    </div>
                </div>
            </div>
        </div>

        <!-- Réservations en attente -->
        <div class="col-md-3">
            <div class="card shadow-sm dashboard-card reservations">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Réservations en attente</h6>
                            <h2 class="mb-0" data-stat="reservations-attente"><?= count($reservations_attente) ?></h2>
                        </div>
                        <div class="dashboard-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="small text-muted">
                        demandes à traiter
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents et Alertes -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Documents à renouveler
                    </h5>
                    <small class="text-muted" id="documents-update-time"></small>
                </div>
                <div class="card-body" id="documents-alerts-container">
                    <?php if (empty($documents_alerts)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>Aucun document n'arrive à expiration
                        </div>
                    <?php else: ?>
                        <?php foreach ($documents_alerts as $doc): ?>
                            <div class="alert <?=
                                $doc['jours_restants'] <= 7 ? 'alert-danger' :
                                ($doc['jours_restants'] <= 30 ? 'alert-warning' : 'alert-info')
                                ?> doc-alert" data-doc-id="<?= $doc['id_document'] ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['type_document']))) ?></strong>
                                        <?php if ($doc['marque']): ?>
                                            <br>
                                            <small>
                                                <?= htmlspecialchars($doc['marque'] . ' ' . $doc['modele'] . ' (' . $doc['immatriculation'] . ')') ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <strong><?= $doc['jours_restants'] ?> jours</strong><br>
                                        <small>jusqu'à expiration</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2 text-primary"></i>Maintenances en cours
                    </h5>
                    <small class="text-muted" id="maintenances-update-time"></small>
                </div>
                <div class="card-body" id="maintenances-container">
                    <?php if (empty($maintenances_en_cours)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Aucune maintenance en cours
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($maintenances_en_cours as $maintenance): ?>
                                <div class="list-group-item maintenance-item" data-maintenance-id="<?= $maintenance['id_maintenance'] ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?= htmlspecialchars($maintenance['marque'] . ' ' . $maintenance['modele']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($maintenance['date_fin_prevue'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($maintenance['description']) ?></p>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($maintenance['type_maintenance']) ?> -
                                        <?= htmlspecialchars($maintenance['prestataire']) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Accès rapides -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-link me-2"></i>Accès rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <?php if ($_SESSION['role'] !== 'validateur'): ?>
                            <div class="col-md-3 mb-3">
                                <a href="gestion_vehicules.php"
                                    class="btn btn-lg btn-light border w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                                    <i class="fas fa-car fa-3x mb-3 text-primary"></i>
                                    <span>Gestion des véhicules</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="gestion_chauffeurs.php"
                                    class="btn btn-lg btn-light border w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                                    <i class="fas fa-users fa-3x mb-3 text-success"></i>
                                    <span>Gestion des chauffeurs</span>
                                </a>
                            </div>
                            <!-- Nouvel accès rapide pour les zones de véhicules -->
                            <div class="col-md-3 mb-3">
                                <a href="gestion_zones_vehicules.php"
                                    class="btn btn-lg btn-light border w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                                    <i class="fas fa-map-marker-alt fa-3x mb-3 text-danger"></i>
                                    <span>Zones de véhicules</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($roleAccess->hasPermission('tracking')): ?>
                            <!-- NOUVEAU: Accès rapide aux statistiques globales -->
                            <div class="col-md-3 mb-3">
                                <a href="statistiques_globales.php"
                                    class="btn btn-lg btn-light border w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                                    <i class="fas fa-chart-pie fa-3x mb-3 text-primary"></i>
                                    <span>Statistiques globales</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="gestion_documents.php"
                                    class="btn btn-lg btn-light border w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                                    <i class="fas fa-file-alt fa-3x mb-3 text-info"></i>
                                    <span>Documents administratifs</span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-3 mb-3">
                            <a href="planification.php"
                                class="btn btn-lg btn-light border w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                                <i class="fas fa-calendar-alt fa-3x mb-3 text-warning"></i>
                                <span>Réservations</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ajout du script pour le tableau de bord en temps réel -->
<script src="assets/js/index/dashboard-realtime.js"></script>
<script src="assets/js/index/dashboard-stats-overview.js"></script>

<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php"); ?>