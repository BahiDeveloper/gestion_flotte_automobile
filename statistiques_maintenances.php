<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Inclure les requêtes nécessaires
include_once("request" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "vehicules.php");

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

// Récupérer les statistiques globales pour la période
$sql_global = "
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
";
$stmt_global = $pdo->prepare($sql_global);
$stmt_global->bindParam(':date_debut', $date_debut);
$stmt_global->bindParam(':date_fin', $date_fin);
$stmt_global->execute();
$stats_global = $stmt_global->fetch(PDO::FETCH_ASSOC);

// Récupérer les statistiques par type de maintenance
$sql_types = "
    SELECT 
        type_maintenance,
        COUNT(*) as nombre_maintenances,
        SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as nombre_terminees,
        SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as nombre_en_cours,
        SUM(CASE WHEN statut = 'planifiee' THEN 1 ELSE 0 END) as nombre_planifiees,
        SUM(CASE WHEN statut = 'annulee' THEN 1 ELSE 0 END) as nombre_annulees,
        SUM(cout) as cout_total
    FROM maintenances
    WHERE date_debut BETWEEN :date_debut AND :date_fin
    GROUP BY type_maintenance
";
$stmt_types = $pdo->prepare($sql_types);
$stmt_types->bindParam(':date_debut', $date_debut);
$stmt_types->bindParam(':date_fin', $date_fin);
$stmt_types->execute();
$stats_types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les statistiques par véhicule
$sql_vehicules = "
    SELECT 
        v.id_vehicule,
        v.marque,
        v.modele,
        v.immatriculation,
        v.logo_marque_vehicule,
        v.type_vehicule,
        COUNT(m.id_maintenance) as nombre_maintenances,
        SUM(CASE WHEN m.statut = 'terminee' THEN 1 ELSE 0 END) as nombre_terminees,
        SUM(CASE WHEN m.statut = 'en_cours' THEN 1 ELSE 0 END) as nombre_en_cours,
        SUM(CASE WHEN m.statut = 'planifiee' THEN 1 ELSE 0 END) as nombre_planifiees,
        SUM(CASE WHEN m.statut = 'annulee' THEN 1 ELSE 0 END) as nombre_annulees,
        SUM(m.cout) as cout_total
    FROM vehicules v
    LEFT JOIN maintenances m ON v.id_vehicule = m.id_vehicule
        AND m.date_debut BETWEEN :date_debut AND :date_fin
    GROUP BY v.id_vehicule
    HAVING nombre_maintenances > 0
    ORDER BY cout_total DESC
";
$stmt_vehicules = $pdo->prepare($sql_vehicules);
$stmt_vehicules->bindParam(':date_debut', $date_debut);
$stmt_vehicules->bindParam(':date_fin', $date_fin);
$stmt_vehicules->execute();
$stats_vehicules = $stmt_vehicules->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les statistiques de maintenance par mois (pour le graphique)
$sql_mois = "
    SELECT 
        DATE_FORMAT(date_debut, '%Y-%m') as mois,
        COUNT(*) as nombre_maintenances,
        SUM(cout) as cout_total
    FROM maintenances
    WHERE date_debut BETWEEN 
        DATE_SUB(:date_fin, INTERVAL 11 MONTH) AND :date_fin
    GROUP BY DATE_FORMAT(date_debut, '%Y-%m')
    ORDER BY mois ASC
";
$stmt_mois = $pdo->prepare($sql_mois);
$stmt_mois->bindParam(':date_fin', $date_fin);
$stmt_mois->execute();
$stats_mois = $stmt_mois->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les statistiques par statut
$sql_statuts = "
    SELECT 
        statut,
        COUNT(*) as nombre,
        SUM(cout) as cout_total
    FROM maintenances
    WHERE date_debut BETWEEN :date_debut AND :date_fin
    GROUP BY statut
";
$stmt_statuts = $pdo->prepare($sql_statuts);
$stmt_statuts->bindParam(':date_debut', $date_debut);
$stmt_statuts->bindParam(':date_fin', $date_fin);
$stmt_statuts->execute();
$stats_statuts = $stmt_statuts->fetchAll(PDO::FETCH_ASSOC);

// Convertir les données mensuelles en JSON pour les graphiques
$labels_mois = [];
$data_nombre = [];
$data_cout = [];

foreach ($stats_mois as $mois) {
    $labels_mois[] = date('M Y', strtotime($mois['mois'] . '-01'));
    $data_nombre[] = intval($mois['nombre_maintenances']);
    $data_cout[] = intval($mois['cout_total']);
}

$json_labels = json_encode($labels_mois);
$json_nombre = json_encode($data_nombre);
$json_cout = json_encode($data_cout);

// Données pour le graphique camembert des types de maintenance
$labels_types = [];
$data_types = [];
$colors_types = [
    'preventive' => '#28a745', // Vert pour préventive
    'corrective' => '#dc3545', // Rouge pour corrective
    'revision' => '#007bff'  // Bleu pour révision
];
$background_colors = [];

foreach ($stats_types as $type) {
    $labels_types[] = ucfirst($type['type_maintenance']);
    $data_types[] = intval($type['nombre_maintenances']);
    $background_colors[] = $colors_types[$type['type_maintenance']];
}

$json_labels_types = json_encode($labels_types);
$json_data_types = json_encode($data_types);
$json_colors_types = json_encode($background_colors);

// Données pour le graphique des statuts
$labels_statuts = [];
$data_statuts = [];
$colors_statuts = [
    'planifiee' => '#007bff', // Bleu pour planifiée
    'en_cours' => '#ffc107', // Jaune pour en cours
    'terminee' => '#28a745', // Vert pour terminée
    'annulee' => '#dc3545'  // Rouge pour annulée
];
$background_colors_statuts = [];

foreach ($stats_statuts as $statut) {
    $labels_statuts[] = ucfirst($statut['statut']);
    $data_statuts[] = intval($statut['nombre']);
    $background_colors_statuts[] = $colors_statuts[$statut['statut']];
}

$json_labels_statuts = json_encode($labels_statuts);
$json_data_statuts = json_encode($data_statuts);
$json_colors_statuts = json_encode($background_colors_statuts);

// Données pour le graphique des coûts par véhicule
$labels_vehicules = [];
$data_cout_vehicules = [];

foreach ($stats_vehicules as $vehicule) {
    if ($vehicule['cout_total'] > 0) { // Ne montrer que les véhicules avec des coûts
        $labels_vehicules[] = $vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['immatriculation'] . ')';
        $data_cout_vehicules[] = intval($vehicule['cout_total']);
    }
}

$json_labels_vehicules = json_encode($labels_vehicules);
$json_cout_vehicules = json_encode($data_cout_vehicules);

?>

<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->

<?php
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

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-bar me-2"></i>Statistiques de maintenance</h1>
        <a href="gestion_vehicules.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
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

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Statistiques globales <?= $titre_periode ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Nombre de maintenances</h6>
                                            <h2 class="mb-0"><?= number_format($stats_global['nombre_maintenances'], 0, ',', ' ') ?></h2>
                                        </div>
                                        <i class="fas fa-wrench fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Terminées</h6>
                                            <h2 class="mb-0"><?= number_format($stats_global['nombre_terminees'], 0, ',', ' ') ?></h2>
                                        </div>
                                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Coût total</h6>
                                            <h2 class="mb-0"><?= number_format($stats_global['cout_total'], 0, ',', ' ') ?> FCFA</h2>
                                        </div>
                                        <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Durée moyenne</h6>
                                            <h2 class="mb-0"><?= number_format($stats_global['duree_moyenne'], 1, ',', ' ') ?> jours</h2>
                                        </div>
                                        <i class="fas fa-clock fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution sur 12 mois</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartEvolution" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition par type</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="chartTypes" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Deuxième rangée de graphiques -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition par statut</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="chartStatuts" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-car me-2"></i>Coût par véhicule</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartVehicules" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails par véhicule -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Détails par véhicule <?= $titre_periode ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-car me-1"></i>Véhicule</th>
                                    <th><i class="fas fa-truck me-1"></i>Type</th>
                                    <th><i class="fas fa-list-ol me-1"></i>Nb total</th>
                                    <th><i class="fas fa-check-circle me-1"></i>Terminées</th>
                                    <th><i class="fas fa-spinner me-1"></i>En cours</th>
                                    <th><i class="fas fa-calendar-alt me-1"></i>Planifiées</th>
                                    <th><i class="fas fa-times-circle me-1"></i>Annulées</th>
                                    <th><i class="fas fa-money-bill-wave me-1"></i>Coût total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats_vehicules as $vehicule): 
                                    // Définir les types de véhicule
                                    $label_type = "";
                                    switch ($vehicule['type_vehicule']) {
                                        case 'berline':
                                            $label_type = '<span class="badge bg-info">Berline</span>';
                                            break;
                                        case 'utilitaire':
                                            $label_type = '<span class="badge bg-primary">Utilitaire</span>';
                                            break;
                                        case 'camion':
                                            $label_type = '<span class="badge bg-warning">Camion</span>';
                                            break;
                                        case 'bus':
                                            $label_type = '<span class="badge bg-success">Bus</span>';
                                            break;
                                        default:
                                            $label_type = '<span class="badge bg-secondary">N/A</span>';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($vehicule['logo_marque_vehicule'])): ?>
                                                <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($vehicule['logo_marque_vehicule']) ?>" 
                                                    class="me-2" alt="Logo" 
                                                    style="width: 30px; height: 30px; object-fit: contain;">
                                            <?php else: ?>
                                                <i class="fas fa-car me-2 text-secondary"></i>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']) ?></strong>
                                                <div><small class="text-muted"><?= htmlspecialchars($vehicule['immatriculation']) ?></small></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $label_type ?></td>
                                    <td><?= number_format($vehicule['nombre_maintenances'], 0, ',', ' ') ?></td>
                                    <td><span class="badge bg-success"><?= number_format($vehicule['nombre_terminees'], 0, ',', ' ') ?></span></td>
                                    <td><span class="badge bg-warning"><?= number_format($vehicule['nombre_en_cours'], 0, ',', ' ') ?></span></td>
                                    <td><span class="badge bg-primary"><?= number_format($vehicule['nombre_planifiees'], 0, ',', ' ') ?></span></td>
                                    <td><span class="badge bg-danger"><?= number_format($vehicule['nombre_annulees'], 0, ',', ' ') ?></span></td>
                                    <td><?= $vehicule['cout_total'] ? number_format($vehicule['cout_total'], 0, ',', ' ') . ' FCFA' : '---' ?></td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Configuration des couleurs et styles communs pour les graphiques
    Chart.defaults.font.family = "'Poppins', 'Helvetica', 'Arial', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#666';
    
    // Graphique d'évolution
    const ctxEvolution = document.getElementById('chartEvolution').getContext('2d');
    new Chart(ctxEvolution, {
        type: 'line',
        data: {
            labels: <?= $json_labels ?>,
            datasets: [{
                label: 'Nombre de maintenances',
                data: <?= $json_nombre ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                yAxisID: 'y',
            }, {
                label: 'Coût (FCFA)',
                data: <?= $json_cout ?>,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Nombre de maintenances',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Coût (FCFA)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        drawOnChartArea: false,
                        color: 'rgba(220, 53, 69, 0.1)'
                    },
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    titleFont: {
                        weight: 'bold',
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y') {
                                label += context.raw;
                            } else {
                                label += new Intl.NumberFormat('fr-FR', { 
                                    style: 'currency', 
                                    currency: 'XOF',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(context.raw);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Graphique de répartition par type de maintenance
    const ctxTypes = document.getElementById('chartTypes').getContext('2d');
    new Chart(ctxTypes, {
        type: 'doughnut',
        data: {
            labels: <?= $json_labels_types ?>,
            datasets: [{
                data: <?= $json_data_types ?>,
                backgroundColor: <?= $json_colors_types ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    bottom: 20
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    titleFont: {
                        weight: 'bold',
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
    
    // Graphique de répartition par statut
    const ctxStatuts = document.getElementById('chartStatuts').getContext('2d');
    new Chart(ctxStatuts, {
        type: 'doughnut',
        data: {
            labels: <?= $json_labels_statuts ?>,
            datasets: [{
                data: <?= $json_data_statuts ?>,
                backgroundColor: <?= $json_colors_statuts ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    bottom: 20
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    titleFont: {
                        weight: 'bold',
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Graphique des coûts par véhicule
    const ctxVehicules = document.getElementById('chartVehicules').getContext('2d');
    new Chart(ctxVehicules, {
        type: 'bar',
        data: {
            labels: <?= $json_labels_vehicules ?>,
            datasets: [{
                label: 'Coût total',
                data: <?= $json_cout_vehicules ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            layout: {
                padding: {
                    top: 10,
                    bottom: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Coût (FCFA)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(value) + ' FCFA';
                        },
                        maxTicksLimit: 8
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    titleFont: {
                        weight: 'bold',
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('fr-FR', { 
                                style: 'currency', 
                                currency: 'XOF',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(context.raw);
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Afficher l'année active dans la sélection de période
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser DataTables
        const tables = document.querySelectorAll('.table');
        if (tables.length > 0) {
            tables.forEach(table => {
                new DataTable(table, {
                    language: {
                        url: 'assets/js/dataTables.french.json'
                    },
                    order: [[7, 'desc']], // Trier par coût total par défaut
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.childRowImmediate,
                            type: 'none',
                            target: ''
                        }
                    },
                    dom: '<"d-flex justify-content-between align-items-center mb-3"f<"table-buttons">><"table-responsive"t><"d-flex justify-content-between align-items-center mt-3"lip>',
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tous"]],
                    pageLength: 10,
                    columnDefs: [
                        { className: "text-nowrap", targets: [0, 1] }, // Empêcher le retour à la ligne pour ces colonnes
                        { className: "text-end", targets: [2, 3, 4, 5, 6, 7] } // Aligner à droite les colonnes numériques
                    ],
                    initComplete: function() {
                        // Ajouter un bouton pour basculer la visibilité des colonnes
                        const tableButtons = document.querySelector('.table-buttons');
                        if (tableButtons) {
                            const columnToggleBtn = document.createElement('button');
                            columnToggleBtn.className = 'btn btn-sm btn-outline-secondary ms-2';
                            columnToggleBtn.innerHTML = '<i class="fas fa-columns me-1"></i>Colonnes';
                            columnToggleBtn.setAttribute('data-bs-toggle', 'dropdown');
                            columnToggleBtn.setAttribute('aria-expanded', 'false');
                            
                            const dropdownMenu = document.createElement('div');
                            dropdownMenu.className = 'dropdown-menu dropdown-menu-end p-2';
                            
                            // Récupérer toutes les colonnes sauf la première (véhicule)
                            const table = this;
                            const columns = table.columns().indexes().toArray().slice(1);
                            
                            columns.forEach(colIdx => {
                                const column = table.column(colIdx);
                                const columnTitle = $(column.header()).text().trim();
                                
                                const item = document.createElement('div');
                                item.className = 'form-check';
                                
                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.className = 'form-check-input';
                                checkbox.checked = column.visible();
                                checkbox.id = 'col_' + colIdx;
                                
                                checkbox.addEventListener('change', function() {
                                    column.visible(this.checked);
                                });
                                
                                const label = document.createElement('label');
                                label.className = 'form-check-label';
                                label.htmlFor = 'col_' + colIdx;
                                label.textContent = columnTitle;
                                
                                item.appendChild(checkbox);
                                item.appendChild(label);
                                dropdownMenu.appendChild(item);
                            });
                            
                            // Créer le conteneur dropdown
                            const dropdownContainer = document.createElement('div');
                            dropdownContainer.className = 'dropdown';
                            dropdownContainer.appendChild(columnToggleBtn);
                            dropdownContainer.appendChild(dropdownMenu);
                            
                            tableButtons.appendChild(dropdownContainer);
                        }
                        
                        // Améliorer l'apparence des en-têtes du tableau
                        const headerCells = document.querySelectorAll('table.dataTable thead th');
                        headerCells.forEach(th => {
                            // Ajouter une petite icône de tri au survol uniquement
                            const sortIcon = document.createElement('span');
                            sortIcon.className = 'sort-icon ms-1 d-none';
                            sortIcon.innerHTML = '<i class="fas fa-sort"></i>';
                            
                            th.addEventListener('mouseover', function() {
                                sortIcon.classList.remove('d-none');
                            });
                            
                            th.addEventListener('mouseout', function() {
                                if (!th.classList.contains('sorting_asc') && !th.classList.contains('sorting_desc')) {
                                    sortIcon.classList.add('d-none');
                                }
                            });
                            
                            th.appendChild(sortIcon);
                        });
                    }
                });
            });
        }
        
        // Afficher ou masquer les filtres personnalisés au clic sur le bouton
        const btnPersonnaliser = document.querySelector('button[data-bs-target="#collapsePersonnalise"]');
        if (btnPersonnaliser) {
            btnPersonnaliser.addEventListener('click', function() {
                const collapseElement = document.getElementById('collapsePersonnalise');
                const isVisible = collapseElement.classList.contains('show');
                
                if (!isVisible) {
                    // Si on affiche le collapse, on met à jour l'input caché
                    const periodeInput = document.querySelector('input[name="periode"]');
                    periodeInput.value = 'personnalise';
                }
            });
        }
    });
</script>

<!-- Ajouter DataTables après jQuery -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<!-- Scripts personnalisés pour cette page -->
<script src="assets/js/vehicules/statistiques_maintenances.js"></script>

<script src="assets/js/vehicules/mobile-fixes.js"></script>


<!-- Styles supplémentaires pour l'impression et l'interface -->
<style>
    /* Styles généraux optimisés */
    .container-fluid {
        max-width: 1600px;
        margin: 0 auto;
    }
    
    /* Améliorations des en-têtes de tableau */
    .table thead th {
        font-weight: 700;
        background-color: #343a40 !important;
        color: white !important;
        padding: 12px 8px;
        position: sticky;
        top: 0;
        z-index: 10;
        border-bottom: 2px solid #adb5bd;
    }
    
    /* Style des cellules de tableau */
    .table tbody td {
        padding: 10px 8px;
        vertical-align: middle;
    }
    
    /* Amélioration des cartes */
    .card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }
    
    /* En-têtes de carte */
    .card-header {
        font-weight: 700;
        padding: 15px 20px;
    }
    
    /* Corps de carte pour les statistiques */
    .card-body {
        padding: 20px;
        position: relative; /* Ajoutez cette ligne */
    }
    
    /* Style des badges */
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.9em;
        font-weight: 600;
        border-radius: 6px;
    }
    
    /* Contrôles des graphiques repositionnés */
    .chart-controls {
        display: flex;
        justify-content: center; /* Centre le bouton horizontalement */
        position: absolute; /* Positionnement absolu */
        bottom: 10px; /* Distance depuis le bas */
        width: 100%; /* Prend toute la largeur */
        z-index: 10; /* S'assure que le bouton est au-dessus du graphique */
    }
    .chart-controls button {
        padding: 0.25rem 0.75rem;
        font-size: 0.85rem;
        border-radius: 4px;
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #495057;
    }
    
    .chart-controls button:hover {
        background-color: #e9ecef;
        border-color: #ced4da;
    }
    
    /* Optimisation des couleurs de texte pour meilleur contraste */
    .text-success {
        color: #198754 !important;
        font-weight: 600;
    }
    
    .text-warning {
        color: #fd7e14 !important;
        font-weight: 600;
    }
    
    .text-danger {
        color: #dc3545 !important;
        font-weight: 600;
    }
    
    /* Amélioration des espacements */
    .mb-4 {
        margin-bottom: 1.8rem !important;
    }
    
    /* Responsive design amélioré */
    @media (max-width: 992px) {
        .card-body h2 {
            font-size: 1.5rem;
        }
        
        .card-title {
            font-size: 0.9rem;
        }
        
        .badge {
            font-size: 0.8em;
            padding: 0.4em 0.7em;
        }
        
        .table thead th {
            padding: 10px 5px;
            font-size: 0.9rem;
        }
        
        .table tbody td {
            padding: 8px 5px;
            font-size: 0.9rem;
        }
    }
    
    @media (max-width: 768px) {
        h1 {
            font-size: 1.8rem;
        }
        
        .card-body h2 {
            font-size: 1.3rem;
        }
        
        .card-header h5 {
            font-size: 1.1rem;
        }
        
        .chart-controls {
            justify-content: center;
        }
    }
    
    /* Impression optimisée */
    @media print {
        /* Styles pour l'impression */
        header, footer, .nav, .btn, #filterForm, input, select, button, .form-control {
            display: none !important;
        }
        
        body {
            background-color: white;
            color: black;
            margin: 0;
            padding: 20px;
        }
        
        .card {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            box-shadow: none !important;
        }
        
        .card-header {
            background-color: #f0f0f0 !important;
            color: #333 !important;
        }
        
        canvas {
            max-height: 300px;
            width: 100% !important;
        }
        
        .bg-primary, .bg-success, .bg-danger, .bg-info {
            background-color: #f8f9fa !important;
            color: #333 !important;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        
        /* Titre de page plus proéminent */
        body::before {
            content: "Statistiques de maintenance - Rapport généré";
            display: block;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        /* Meilleure lisibilité des tableaux en impression */
        .table thead th {
            background-color: #f0f0f0 !important;
            color: #333 !important;
            border: 1px solid #888;
            font-weight: bold;
        }
        
        .table tbody td {
            border: 1px solid #888;
        }
    }
</style>

<!-- Inclure le pied de page -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php"); ?>