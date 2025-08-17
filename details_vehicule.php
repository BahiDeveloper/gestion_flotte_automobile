<?php
// Inclure les requêtes nécessaires
include_once("request" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "request_detail_vehicule.php");
?>

<!-- Inclure le header -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>

<?php
// Vérifier que l'objet $roleAccess est bien défini
if (!isset($roleAccess)) {
    include_once("includes" . DIRECTORY_SEPARATOR . "RoleAccess.php");
    $roleAccess = new RoleAccess($_SESSION['role']);
}
?>

<!-- Contenu de la page -->
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-car me-2"></i>Détails du véhicule</h1>
            <a href="gestion_vehicules.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Onglets de navigation -->
    <ul class="nav nav-tabs mb-4" id="vehiculeDetailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                type="button" role="tab" aria-controls="details" aria-selected="true">
                <i class="fas fa-info-circle me-2"></i>Informations générales
            </button>
        </li>
        <?php if ($roleAccess->hasPermission('form') || $roleAccess->hasPermission('historique')): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button"
                    role="tab" aria-controls="documents" aria-selected="false">
                    <i class="fas fa-file-alt me-2"></i>Documents administratifs
                </button>
            </li>
        <?php endif; ?>
        <?php if ($roleAccess->hasPermission('tracking')): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="statistics-tab" data-bs-toggle="tab" data-bs-target="#statistics" type="button"
                    role="tab" aria-controls="statistics" aria-selected="false">
                    <i class="fas fa-chart-line me-2"></i>Statistiques
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Contenu des onglets -->
    <div class="tab-content" id="vehiculeDetailTabsContent">
        <!-- Onglet Informations générales -->
        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <?php if (!empty($vehicule['logo_marque_vehicule'])): ?>
                                <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($vehicule['logo_marque_vehicule']) ?>"
                                    alt="Logo <?= htmlspecialchars($vehicule['marque']) ?>" class="img-thumbnail"
                                    style="max-height: 80px; max-width: 120px;">
                            <?php else: ?>
                                <div class="border rounded p-3 text-center bg-light">
                                    <i class="fas fa-car fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <h3 class="mb-0">
                                <?= htmlspecialchars($vehicule['marque']) ?>
                                <?= htmlspecialchars($vehicule['modele']) ?>
                                <span class="badge <?= getStatusBadgeClass($vehicule['statut']) ?>">
                                    <?= ucfirst(htmlspecialchars($vehicule['statut'])) ?>
                                </span>
                            </h3>
                            <p class="text-muted mb-0">
                                Immatriculation: <strong><?= htmlspecialchars($vehicule['immatriculation']) ?></strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Caractéristiques techniques</h5>
                            <table class="table table-hover">
                                <tbody>
                                    <tr>
                                        <th><i class="fas fa-car-side me-2"></i>Type de véhicule</th>
                                        <td><?= ucfirst(htmlspecialchars($vehicule['type_vehicule'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-users me-2"></i>Capacité</th>
                                        <td><?= htmlspecialchars($vehicule['capacite_passagers']) ?> passagers</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-gas-pump me-2"></i>Type de carburant</th>
                                        <td><?= htmlspecialchars($vehicule['type_carburant']) ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-tachometer-alt me-2"></i>Kilométrage actuel</th>
                                        <td><?= number_format($vehicule['kilometrage_actuel'], 0, ',', ' ') ?> km</td>
                                    </tr>
                                    <?php if (!empty($vehicule['annee_mise_en_service'])): ?>
                                        <tr>
                                            <th><i class="fas fa-calendar me-2"></i>Année de mise en service</th>
                                            <td><?= htmlspecialchars($vehicule['annee_mise_en_service']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($vehicule['capacite_charge_kg'])): ?>
                                        <tr>
                                            <th><i class="fas fa-weight-hanging me-2"></i>Capacité de charge</th>
                                            <td><?= number_format($vehicule['capacite_charge_kg'], 0, ',', ' ') ?> kg</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Informations administratives</h5>
                            <table class="table table-hover">
                                <tbody>
                                    <tr>
                                        <th><i class="fas fa-map-marker-alt me-2"></i>Zone d'affectation</th>
                                        <td><?= htmlspecialchars($vehicule['nom_zone'] ?? 'Non définie') ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-info-circle me-2"></i>Statut actuel</th>
                                        <td>
                                            <span class="badge <?= getStatusBadgeClass($vehicule['statut']) ?>">
                                                <?= ucfirst(htmlspecialchars($vehicule['statut'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php if (!empty($vehicule['date_acquisition'])): ?>
                                        <tr>
                                            <th><i class="fas fa-calendar-plus me-2"></i>Date d'acquisition</th>
                                            <td><?= date('d/m/Y', strtotime($vehicule['date_acquisition'])) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($vehicule['prix_acquisition'])): ?>
                                        <tr>
                                            <th><i class="fas fa-money-bill-wave me-2"></i>Prix d'acquisition</th>
                                            <td><?= number_format($vehicule['prix_acquisition'], 0, ',', ' ') ?> FCFA</td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th><i class="fas fa-clock me-2"></i>Date d'enregistrement</th>
                                        <td><?= date('d/m/Y H:i', strtotime($vehicule['created_at'])) ?></td>
                                    </tr>
                                    <?php if (!empty($vehicule['note'])): ?>
                                        <tr>
                                            <th><i class="fas fa-sticky-note me-2"></i>Notes</th>
                                            <td><?= nl2br(htmlspecialchars($vehicule['note'])) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group">
                        <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
                            <a href="modifier_vehicule.php?id=<?= $vehiculeId ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Modifier
                            </a>
                        <?php endif; ?>

                        <?php if ($roleAccess->hasPermission('tracking')): ?>
                            <a href="maintenance_vehicule.php?id=<?= $vehiculeId ?>" class="btn btn-primary">
                                <i class="fas fa-tools me-2"></i>Maintenance
                            </a>
                            <a href="approvisionnement_carburant.php?id=<?= $vehiculeId ?>" class="btn btn-success">
                                <i class="fas fa-gas-pump me-2"></i>Approvisionnement
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($roleAccess->hasPermission('form') || $roleAccess->hasPermission('historique')): ?>
            <!-- Onglet Documents administratifs -->
            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Documents administratifs</h5>
                        <?php if ($roleAccess->hasPermission('form')): ?>
                            <a href="ajouter_document.php?type=vehicule&id=<?= $vehiculeId ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-2"></i>Ajouter un document
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (count($documents) > 0): ?>
                            <div class="table-responsive">
                                <table id="documentsTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>N° Document</th>
                                            <th>Émis le</th>
                                            <th>Expire le</th>
                                            <th>Fournisseur</th>
                                            <th>Prix</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documents as $doc): ?>
                                            <tr>
                                                <td><?= formatDocumentType($doc['type_document']) ?></td>
                                                <td><?= htmlspecialchars($doc['numero_document'] ?? 'N/A') ?></td>
                                                <td><?= $doc['date_debut'] ?></td>
                                                <td><?= $doc['date_fin'] ?></td>
                                                <td><?= htmlspecialchars($doc['fournisseur'] ?? 'N/A') ?></td>
                                                <td><?= !empty($doc['prix']) ? number_format($doc['prix'], 0, ',', ' ') . ' FCFA' : 'N/A' ?>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge <?= $doc['status'] === 'Actif' ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $doc['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($doc['file_path'])): ?>
                                                        <a href="uploads/documents/<?= htmlspecialchars($doc['file_path']) ?>"
                                                            target="_blank" class="btn btn-sm btn-info" title="Visualiser">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="uploads/documents/<?= htmlspecialchars($doc['file_path']) ?>" download
                                                            class="btn btn-sm btn-secondary" title="Télécharger">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Aucun fichier</span>
                                                    <?php endif; ?>

                                                    <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
                                                        <a href="modifier_document.php?id=<?= $doc['id_document'] ?>"
                                                            class="btn btn-sm btn-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Aucun document n'est associé à ce véhicule.
                                <?php if ($roleAccess->hasPermission('form')): ?>
                                    <a href="ajouter_document.php?type=vehicule&id=<?= $vehiculeId ?>" class="alert-link">
                                        Ajouter un document
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($roleAccess->hasPermission('tracking')): ?>
            <!-- Onglet Statistiques -->
            <div class="tab-pane fade" id="statistics" role="tabpanel" aria-labelledby="statistics-tab">
                <!-- Sous-navigation statistiques -->
                <ul class="nav nav-pills mb-3" id="statisticsSubTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="stats-summary-tab" data-bs-toggle="pill"
                            data-bs-target="#stats-summary" type="button" role="tab" aria-selected="true">
                            <i class="fas fa-clipboard-list me-2"></i>Résumé
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="stats-fuel-tab" data-bs-toggle="pill" data-bs-target="#stats-fuel"
                            type="button" role="tab" aria-selected="false">
                            <i class="fas fa-gas-pump me-2"></i>Carburant
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="stats-maintenance-tab" data-bs-toggle="pill"
                            data-bs-target="#stats-maintenance" type="button" role="tab" aria-selected="false">
                            <i class="fas fa-tools me-2"></i>Maintenance
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="statisticsSubContent">
                    <!-- Résumé des statistiques -->
                    <div class="tab-pane fade show active" id="stats-summary" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Résumé des statistiques</h5>
                                    <div>
                                        <button id="exportStatsCsv" class="btn btn-sm btn-success me-2">
                                            <i class="fas fa-file-csv me-2"></i>Exporter CSV
                                        </button>
                                        <button id="exportStatsPdf" class="btn btn-sm btn-danger">
                                            <i class="fas fa-file-pdf me-2"></i>Exporter PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Le reste du contenu de l'onglet statistiques reste inchangé -->
                            <div class="card-body">
                                <?php if (!empty($stats)): ?>
                                    <div class="row mb-4">
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-road fa-2x mb-2 text-primary"></i>
                                                    <h5>Kilométrage total</h5>
                                                    <h3><?= number_format($kilometrage_total, 0, ',', ' ') ?> km</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-gas-pump fa-2x mb-2 text-warning"></i>
                                                    <h5>Consommation moyenne</h5>
                                                    <h3><?= $consommation_moyenne ?> L/100km</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-route fa-2x mb-2 text-success"></i>
                                                    <h5>Nombre de trajets</h5>
                                                    <h3><?= $nombre_trajets ?></h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-file-alt fa-2x mb-2 text-primary"></i>
                                                    <h5>Documents administratifs</h5>
                                                    <h3><?= number_format($stats['total_documents'], 0, ',', ' ') ?> FCFA</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-money-bill-wave fa-2x mb-2 text-danger"></i>
                                                    <h5>Coût total</h5>
                                                    <h3><?= number_format($cout_total_carburant + $totalMaintenance + $totalDocuments, 0, ',', ' ') ?>
                                                        FCFA</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 class="border-bottom pb-2 mb-3">Statistiques détaillées</h5>
                                            <table class="table table-hover">
                                                <tbody>
                                                    <tr>
                                                        <th><i class="fas fa-gas-pump me-2"></i>Carburant consommé</th>
                                                        <td><?= number_format($total_carburant, 2, ',', ' ') ?> L</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-money-bill-wave me-2"></i>Coût carburant</th>
                                                        <td><?= number_format($cout_total_carburant, 0, ',', ' ') ?> FCFA</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-tools me-2"></i>Coût maintenance</th>
                                                        <td><?= number_format($totalMaintenance, 0, ',', ' ') ?> FCFA</td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-file-alt me-2"></i>Coût documents administratifs
                                                        </th>
                                                        <td><?= number_format($stats['total_documents'], 0, ',', ' ') ?> FCFA
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th><i class="fas fa-clock me-2"></i>Temps d'utilisation</th>
                                                        <td><?= $heures ?> heures <?= $minutes ?> minutes</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="border-bottom pb-2 mb-3">Répartition des coûts</h5>
                                            <canvas id="costDistributionChart" height="200"></canvas>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>Aucune statistique n'est disponible pour ce
                                        véhicule.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques carburant -->
                    <div class="tab-pane fade" id="stats-fuel" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-gas-pump me-2"></i>Statistiques de consommation de
                                    carburant</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($fuelCostData)): ?>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h5 class="text-center mb-3">Évolution de la consommation</h5>
                                            <canvas id="consumptionChart" height="250"></canvas>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="text-center mb-3">Évolution des coûts</h5>
                                            <canvas id="fuelCostChart" height="250"></canvas>
                                        </div>
                                    </div>

                                    <h5 class="border-bottom pb-2 mb-3">Historique des approvisionnements</h5>
                                    <div class="table-responsive">
                                        <table id="fuelTable" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Quantité (L)</th>
                                                    <th>Prix unitaire</th>
                                                    <th>Montant total</th>
                                                    <th>Kilométrage</th>
                                                    <th>Station</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Requête pour récupérer l'historique des approvisionnements
                                                $queryFuelHistory = "SELECT * FROM approvisionnements_carburant 
                                                                WHERE id_vehicule = :id_vehicule 
                                                                ORDER BY date_approvisionnement DESC";
                                                $stmtFuelHistory = $pdo->prepare($queryFuelHistory);
                                                $stmtFuelHistory->execute(['id_vehicule' => $vehiculeId]);

                                                while ($row = $stmtFuelHistory->fetch(PDO::FETCH_ASSOC)):
                                                    ?>
                                                    <tr>
                                                        <td><?= date('d/m/Y H:i', strtotime($row['date_approvisionnement'])) ?></td>
                                                        <td><?= number_format($row['quantite_litres'], 2, ',', ' ') ?> L</td>
                                                        <td><?= number_format($row['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                                                        <td><?= number_format($row['prix_total'], 0, ',', ' ') ?> FCFA</td>
                                                        <td><?= number_format($row['kilometrage'], 0, ',', ' ') ?> km</td>
                                                        <td><?= htmlspecialchars($row['station_service'] ?? 'N/A') ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>Aucune donnée de consommation n'est disponible
                                        pour ce véhicule.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques maintenance -->
                    <div class="tab-pane fade" id="stats-maintenance" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Statistiques de maintenance</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($maintenanceData)): ?>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h5 class="text-center mb-3">Répartition par type</h5>
                                            <canvas id="maintenanceTypeChart" height="250"></canvas>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="text-center mb-3">Coûts par type</h5>
                                            <canvas id="maintenanceCostChart" height="250"></canvas>
                                        </div>
                                    </div>

                                    <h5 class="border-bottom pb-2 mb-3">Historique des maintenances</h5>
                                    <div class="table-responsive">
                                        <table id="maintenanceTable" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Date début</th>
                                                    <th>Date fin</th>
                                                    <th>Type</th>
                                                    <th>Description</th>
                                                    <th>Coût</th>
                                                    <th>Statut</th>
                                                    <th>Prestataire</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Requête pour récupérer l'historique des maintenances
                                                $queryMaintenanceHistory = "SELECT * FROM maintenances 
                                                                       WHERE id_vehicule = :id_vehicule 
                                                                       ORDER BY date_debut DESC";
                                                $stmtMaintenanceHistory = $pdo->prepare($queryMaintenanceHistory);
                                                $stmtMaintenanceHistory->execute(['id_vehicule' => $vehiculeId]);

                                                while ($row = $stmtMaintenanceHistory->fetch(PDO::FETCH_ASSOC)):
                                                    // Formater le statut
                                                    $statutClass = '';
                                                    switch ($row['statut']) {
                                                        case 'planifiee':
                                                            $statutClass = 'bg-secondary';
                                                            break;
                                                        case 'en_cours':
                                                            $statutClass = 'bg-warning';
                                                            break;
                                                        case 'terminee':
                                                            $statutClass = 'bg-success';
                                                            break;
                                                        case 'annulee':
                                                            $statutClass = 'bg-danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><?= date('d/m/Y', strtotime($row['date_debut'])) ?></td>
                                                        <td>
                                                            <?= !empty($row['date_fin_effective'])
                                                                ? date('d/m/Y', strtotime($row['date_fin_effective']))
                                                                : (!empty($row['date_fin_prevue'])
                                                                    ? date('d/m/Y', strtotime($row['date_fin_prevue'])) . ' (prévue)'
                                                                    : 'Non définie')
                                                                ?>
                                                        </td>
                                                        <td><?= ucfirst($row['type_maintenance']) ?></td>
                                                        <td><?= htmlspecialchars($row['description']) ?></td>
                                                        <td><?= !empty($row['cout']) ? number_format($row['cout'], 0, ',', ' ') . ' FCFA' : 'N/A' ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?= $statutClass ?>">
                                                                <?= ucfirst($row['statut']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($row['prestataire'] ?? 'N/A') ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>Aucune donnée de maintenance n'est disponible
                                        pour ce véhicule.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript pour initialiser les graphiques et les tableaux -->
<!-- Préparation des données pour les graphiques -->
<script>
    // Variables globales pour les graphiques
    const vehiculeId = <?= json_encode($vehiculeId) ?>;

    // Transmettre les permissions utilisateur au JavaScript
    const userPermissions = <?= json_encode($roleAccess->getRolePermissions()) ?>;

    // Données pour le graphique de répartition des coûts
    <?php if (!empty($stats)): ?>
        const costData = {
            carburant: <?= $cout_total_carburant ?>,
            maintenance: <?= $totalMaintenance ?>
        };
    <?php endif; ?>

    // Données pour les graphiques de carburant
    <?php if (!empty($fuelCostData)): ?>
        const fuelData = {
            months: [<?php
            foreach ($graphData as $data) {
                $date = date('M Y', strtotime($data['mois'] . '-01'));
                echo "'" . $date . "', ";
            }
            ?>],
            liters: [<?php
            foreach ($graphData as $data) {
                echo $data['litres'] . ', ';
            }
            ?>],
            costs: [<?php
            foreach ($fuelCostData as $data) {
                echo $data['cout_total'] . ', ';
            }
            ?>],
            averagePrices: [<?php
            foreach ($fuelCostData as $data) {
                echo $data['prix_moyen'] . ', ';
            }
            ?>]
        };
    <?php endif; ?>

    // Données pour les graphiques de maintenance
    <?php if (!empty($maintenanceData)): ?>
        const maintenanceData = {
            types: [<?php
            foreach ($maintenanceData as $data) {
                echo "'" . ucfirst($data['type_maintenance']) . "', ";
            }
            ?>],
            counts: [<?php
            foreach ($maintenanceData as $data) {
                echo $data['nombre'] . ', ';
            }
            ?>],
            costs: [<?php
            foreach ($maintenanceData as $data) {
                echo $data['cout_total'] . ', ';
            }
            ?>]
        };
    <?php endif; ?>
</script>

<!-- Inclusion du script externe -->
<script src="assets/js/vehicules/details_vehicule.js"></script>

<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>

<?php
// Fonctions utilitaires
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'disponible':
            return 'bg-success';
        case 'en_course':
            return 'bg-warning';
        case 'maintenance':
            return 'bg-info';
        case 'hors_service':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function formatDocumentType($type)
{
    $types = [
        'carte_transport' => 'Carte de transport',
        'carte_grise' => 'Carte grise',
        'visite_technique' => 'Visite technique',
        'assurance' => 'Assurance',
        'carte_stationnement' => 'Carte de stationnement'
    ];

    return $types[$type] ?? $type;
}
?>