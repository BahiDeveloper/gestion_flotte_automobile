<?php
// Démarrer la session
session_start();

// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier l'authentification
// require_once 'auth/controllers/auth_controller.php';
// AuthController::requireAuth();

// Requête pour récupérer les documents proches d'expirer
$sql_documents = "SELECT d.*, 
       v.marque, v.modele, 
       DATEDIFF(d.date_expiration, CURDATE()) as jours_restants,
       (CASE 
           WHEN DATEDIFF(d.date_expiration, CURDATE()) <= 7 THEN 'danger'
           WHEN DATEDIFF(d.date_expiration, CURDATE()) <= 30 THEN 'warning'
           ELSE 'info'
       END) as niveau_alerte
FROM documents_administratifs d
LEFT JOIN vehicules v ON d.id_vehicule = v.id_vehicule
WHERE d.statut != 'expire'
AND DATEDIFF(d.date_expiration, CURDATE()) <= 60
ORDER BY jours_restants";

$stmt_documents = $pdo->prepare($sql_documents);
$stmt_documents->execute();
$documents_alerts = $stmt_documents->fetchAll(PDO::FETCH_ASSOC);

// Requête pour les réservations en attente
$sql_reservations = "SELECT r.id_reservation, 
       v.marque, v.modele, 
       c.nom as nom_chauffeur, c.prenoms as prenom_chauffeur,
       r.date_depart
FROM reservations_vehicules r
LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
WHERE r.statut = 'en_attente'
ORDER BY r.date_depart
LIMIT 5";

$stmt_reservations = $pdo->prepare($sql_reservations);
$stmt_reservations->execute();
$reservations_attente = $stmt_reservations->fetchAll(PDO::FETCH_ASSOC);

// Requête pour l'état des véhicules
$sql_vehicules_status = "SELECT 
    statut, 
    COUNT(*) as nombre_vehicules
FROM vehicules
GROUP BY statut";

$stmt_vehicules_status = $pdo->prepare($sql_vehicules_status);
$stmt_vehicules_status->execute();
$vehicules_status = $stmt_vehicules_status->fetchAll(PDO::FETCH_ASSOC);

// Préparer les statistiques des véhicules
$vehicules_stats = [
    'disponible' => 0,
    'en_course' => 0,
    'maintenance' => 0,
    'hors_service' => 0
];

foreach ($vehicules_status as $status) {
    switch ($status['statut']) {
        case 'disponible':
            $vehicules_stats['disponible'] = $status['nombre_vehicules'];
            break;
        case 'en_course':
            $vehicules_stats['en_course'] = $status['nombre_vehicules'];
            break;
        case 'maintenance':
            $vehicules_stats['maintenance'] = $status['nombre_vehicules'];
            break;
        case 'hors_service':
            $vehicules_stats['hors_service'] = $status['nombre_vehicules'];
            break;
    }
}

// Définir le titre de la page
$title = "Tableau de bord";

// Inclure le header
include_once("includes" . DIRECTORY_SEPARATOR . "header.php");
?>

<div class="container-fluid py-4">
    <h1 class="text-center mb-4" style="color: #2c3e50; font-weight: 700;">
        <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
    </h1>

    <div class="row">
        <div class="col-md-12">
            <!-- Notifications -->
            <div class="dashboard-section">
                <h2><i class="fas fa-bell me-2"></i>Notifications</h2>
                <?php if (empty($documents_alerts)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>Aucun document n'approche de son expiration.
                    </div>
                <?php else: ?>
                    <?php foreach ($documents_alerts as $doc): ?>
                        <div class="notification">
                            <i class="fas <?=
                                $doc['niveau_alerte'] == 'danger' ? 'fa-exclamation-triangle notification-icon notification-danger' :
                                ($doc['niveau_alerte'] == 'warning' ? 'fa-exclamation-circle notification-icon notification-warning' :
                                    'fa-info-circle notification-icon notification-info')
                                ?>"></i>
                            <div>
                                <p class="mb-0">
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['type_document']))) ?>
                                    <?= $doc['marque'] ? 'du ' . htmlspecialchars($doc['marque'] . ' ' . $doc['modele']) : '' ?>
                                    expire dans <?= $doc['jours_restants'] ?> jours
                                </p>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($doc['date_expiration'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-12">
            <!-- Résumé des réservations en attente -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-check me-2"></i>Réservations en attente</h2>
                <?php if (empty($reservations_attente)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Aucune réservation en attente.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Véhicule</th>
                                    <th scope="col">Chauffeur</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations_attente as $reservation): ?>
                                    <tr>
                                        <td>
                                            <?= $reservation['marque'] ?
                                                htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']) :
                                                'Non assigné'
                                                ?>
                                        </td>
                                        <td>
                                            <?= $reservation['nom_chauffeur'] ?
                                                htmlspecialchars($reservation['nom_chauffeur'] . ' ' . $reservation['prenom_chauffeur']) :
                                                'Non assigné'
                                                ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($reservation['date_depart'])) ?></td>
                                        <td><span class="badge bg-warning">En attente</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- État des véhicules -->
            <div class="dashboard-section">
                <h2><i class="fas fa-car me-2"></i>État des véhicules</h2>
                <div class="vehicle-status">
                    <div class="vehicle-status-card">
                        <i class="fas fa-check-circle vehicle-status-available"></i>
                        <h5>Disponibles</h5>
                        <p class="mb-0"><?= $vehicules_stats['disponible'] ?> véhicules</p>
                    </div>
                    <div class="vehicle-status-card">
                        <i class="fas fa-road vehicle-status-in-use"></i>
                        <h5>En déplacement</h5>
                        <p class="mb-0"><?= $vehicules_stats['en_course'] ?> véhicules</p>
                    </div>
                    <div class="vehicle-status-card">
                        <i class="fas fa-tools vehicle-status-maintenance"></i>
                        <h5>Maintenance</h5>
                        <p class="mb-0"><?= $vehicules_stats['maintenance'] ?> véhicules</p>
                    </div>
                    <div class="vehicle-status-card">
                        <i class="fas fa-times-circle vehicle-status-out-of-service"></i>
                        <h5>Hors service</h5>
                        <p class="mb-0"><?= $vehicules_stats['hors_service'] ?> véhicules</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Raccourcis vers les principales sections -->
            <div class="dashboard-section">
                <h2><i class="fas fa-link me-2"></i>Raccourcis</h2>
                <div class="quick-links">
                    <a href="gestion_documents.php" class="quick-link">
                        <i class="fas fa-file-alt"></i>
                        <p>Gestion des documents</p>
                    </a>
                    <a href="gestion_vehicules.php" class="quick-link">
                        <i class="fas fa-car"></i>
                        <p>Gestion des véhicules</p>
                    </a>
                    <a href="gestion_chauffeurs.php" class="quick-link">
                        <i class="fas fa-users"></i>
                        <p>Gestion des chauffeurs</p>
                    </a>
                    <a href="planification.php" class="quick-link">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Planification</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer   -->