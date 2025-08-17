<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Inclure les requêtes nécessaires
include_once("request/request_detail_vehicule.php");

?>

<!-- Inclure le header -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>

<!-- Contenu de la page -->
<div class="container mt-5">
    <h1 class="text-center mb-4">Détails du véhicule</h1>

    <!-- Onglets de navigation -->
    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                type="button" role="tab" aria-controls="details" aria-selected="true">
                <i class="fas fa-car me-2"></i>
                <!-- Icône pour "Détails du véhicule" -->
                Détails du véhicule
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button"
                role="tab" aria-controls="documents" aria-selected="false">
                <i class="fas fa-file-alt me-2"></i>
                <!-- Icône pour "Documents associés" -->
                Documents associés
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="statistics-tab" data-bs-toggle="tab" data-bs-target="#statistics" type="button"
                role="tab" aria-controls="statistics" aria-selected="false">
                <i class="fas fa-chart-line me-2"></i>
                <!-- Icône pour "Statistiques" -->
                Statistiques
            </button>
        </li>
    </ul>

    <!-- Contenu des onglets -->
    <div class="tab-content" id="myTabContent">

        <!-- Onglet Détails du véhicule -->
        <div class="tab-pane fade show active vehicle-card" id="details" role="tabpanel" aria-labelledby="details-tab ">
            <div class="card">
                <div class="card-header">
                    <div class="card-img logo_marque_vehicule">
                        <?php if (!empty($vehicule['logo_marque_vehicule'])): ?>
                            <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($vehicule['logo_marque_vehicule']) ?>"
                                class="img-fluid" alt="Photo du vehicule">
                        <?php else: ?>
                            <span class="badge badge-offline">Aucune photo</span>
                        <?php endif; ?>
                        <img src="" alt="">
                    </div>
                    <h5 class="card-title"><?= htmlspecialchars($vehicule['type_vehicule']) ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 pb-3">

                            <p class="card-text">
                                <i class="fas fa-car me-2"></i>
                                <strong>Marque :</strong>
                                <?= htmlspecialchars($vehicule['marque']) ?>
                            </p>

                            <p class="card-text">
                                <i class="fas fa-cog me-2"></i>
                                <strong>Modèle :</strong>
                                <?= htmlspecialchars($vehicule['modele']) ?>
                            </p>

                            <p class="card-text">
                                <i class="fas fa-id-card me-2"></i>
                                <strong>Immatriculation :</strong>
                                <?= htmlspecialchars($vehicule['immatriculation']) ?>
                            </p>

                            <p class="card-text">
                                <i class="fas fa-map-marker-alt me-2 me-2"></i>
                                <strong>Zone :</strong>
                                <?= htmlspecialchars($vehicule['nom_zone']) ?>
                            </p>
                        </div>
                        <div class="col-md-6">

                            <p class="card-text">
                                <i class="fas fa-users me-2"></i>
                                <strong>Capacité :</strong>
                                <?= htmlspecialchars($vehicule['capacite']) ?>
                                places
                            </p>

                            <p class="card-text">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>État :</strong>
                                <span class="badge <?=
                                    $vehicule['etat'] === 'Disponible' ? 'badge-available' :
                                    ($vehicule['etat'] === 'En maintenance' ? 'badge-maintenance' : 'badge-in-use')
                                    ?>">
                                    <?= htmlspecialchars($vehicule['etat']) ?>
                                </span>
                            </p>

                            <p class="card-text">
                                <i class="fas fa-gas-pump me-2"></i>
                                <strong>Type carburant :</strong>
                                <?= htmlspecialchars($vehicule['type_carburant']) ?>
                            </p>

                            <p class="card-text">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                <strong>Kilométrage actuel :</strong>
                                <?= htmlspecialchars($vehicule['kilometrage_actuel']) ?>
                                km
                            </p>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <a href="gestion_vehicules.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                </div>
            </div>
        </div>

        <!-- Onglet Documents associés -->
        <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Documents associés</h5>
                </div>
                <div class="card-body">
                    <?php if (count($documents) > 0): ?>
                        <!-- Tableau des documents -->
                        <table id="documentsTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Type de document</th>
                                    <th>Date de début</th>
                                    <th>Date de fin</th>
                                    <th>Véhicule</th>
                                    <th>Fournisseur</th>
                                    <th>Coût récurrent</th>
                                    <th>Fréquence de renouvellement</th>
                                    <th>Statut</th>
                                    <th>Fichier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $document): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($document['type_document']) ?></td>
                                        <td><?= htmlspecialchars($document['date_debut']) ?></td>
                                        <td><?= htmlspecialchars($document['date_fin']) ?></td>
                                        <td><?= htmlspecialchars($vehicule['type_vehicule']) ?></td>
                                        <!-- Nom du véhicule -->
                                        <td><?= htmlspecialchars($document['fournisseur']) ?></td>
                                        <td><?= htmlspecialchars($document['prix']) ?>
                                            FCFA</td>
                                        <td><?= htmlspecialchars($document['frequence_renouvellement']) ?></td>
                                        <td>
                                            <span
                                                class="badge <?= $document['status'] === 'Actif' ? 'badge-available' : 'badge-danger' ?>">
                                                <?= htmlspecialchars($document['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="uploads/documents/<?= htmlspecialchars($document['file_path']) ?>"
                                                target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="uploads/documents/<?= htmlspecialchars($document['file_path']) ?>"
                                                download="download" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>
                            <i class="fas fa-exclamation-circle me-2"></i>Aucun document associé à ce véhicule.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Onglet Statistiques -->
        <div class="tab-pane fade" id="statistics" role="tabpanel" aria-labelledby="statistics-tab">
            <!-- Navigation des sous-onglets -->
            <ul class="nav nav-tabs" id="subTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="vehicle-stats-tab" data-bs-toggle="tab" href="#vehicle-stats"
                        role="tab" aria-controls="vehicle-stats" aria-selected="true"> <i
                            class="fas fa-chart-line me-2"></i>Statistiques du véhicule</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="maintenance-history-tab" data-bs-toggle="tab" href="#maintenance-history"
                        role="tab" aria-controls="maintenance-history" aria-selected="false"> <i
                            class="fas fa-tools me-2"></i>Historique des
                        maintenances</a>
                </li>
            </ul>
            <div class="tab-content" id="subTabContent">
                <!-- Sous-onglet Statistiques du véhicule -->
                <div class="tab-pane fade show active" id="vehicle-stats" role="tabpanel"
                    aria-labelledby="vehicle-stats-tab">
                    <!-- Contenu des statistiques du véhicule -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Statistiques du véhicule</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($stats)): ?>
                                <p>
                                    <i class="fas fa-exclamation-circle me-2"></i>Aucune statistique disponible pour ce
                                    véhicule.
                                </p>
                            <?php else: ?>
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <input type="date" id="startDate" class="form-control" placeholder="Date de début">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" id="endDate" class="form-control" placeholder="Date de fin">
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-primary" id="applyFilters">Appliquer</button>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <button class="btn btn-success" id="exportStats">
                                        <i class="fas fa-file-csv"></i> Exporter les statistiques
                                    </button>
                                    <button class="btn btn-excel" id="exportMaintenance">
                                        <i class="fas fa-file-excel"></i> Exporter maintenance
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 pb-3">
                                        <p class="card-text">
                                            <i class="fas fa-road me-2"></i>
                                            <strong>Kilométrage total :</strong>
                                            <?= htmlspecialchars($kilometrage_total ?? 'N/A') ?> km
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-gas-pump me-2"></i>
                                            <strong>Consommation moyenne :</strong>
                                            <?= htmlspecialchars($consommation_moyenne ?? 'N/A') ?> L/100km
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-money-bill-wave me-2"></i>
                                            <strong>Coût total du carburant :</strong>
                                            <?= htmlspecialchars($cout_total_carburant ?? 'N/A') ?> FCFA
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-gas-pump me-2"></i>
                                            <strong>Quantité totale de carburant :</strong>
                                            <?= htmlspecialchars($total_carburant ?? 'N/A') ?> L
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-route me-2"></i>
                                            <strong>Nombre de trajets :</strong>
                                            <?= htmlspecialchars($nombre_trajets ?? 'N/A') ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-clock me-2"></i>
                                            <strong>Durée totale des déplacements :</strong>
                                            <?= htmlspecialchars($heures . " heures " . $minutes . " minutes") ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-tools me-2"></i>
                                            <strong>Coût total de maintenance :</strong>
                                            <?= htmlspecialchars($totalMaintenance ?? 'N/A') ?> FCFA
                                        </p>

                                    </div>
                                    <div class="col-md-6">
                                        <!-- Graphique de consommation -->
                                        <canvas id="consumptionChart" width="400" height="200"></canvas>
                                        <!-- Graphique des coûts de maintenance -->
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <canvas id="fuelCostChart"></canvas>
                                    </div>
                                    <div class="col-md-6">
                                        <canvas id="maintenanceChart" width="400" height="200"></canvas>
                                        <canvas id="maintenanceCostChart"></canvas>
                                    </div>
                                </div>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Sous-onglet Historique des maintenances -->
                <div class="tab-pane fade" id="maintenance-history" role="tabpanel"
                    aria-labelledby="maintenance-history-tab">
                    <!-- Contenu de l'historique des maintenances -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Historique des maintenances</h5>
                        </div>
                        <div class="card-body">
                            <table id="maintenanceTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date début</th>
                                        <th>Date fin</th>
                                        <th>Description</th>
                                        <th>Coût</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $queryMaintenanceList = "SELECT * FROM maintenance WHERE id_vehicule = :id_vehicule";
                                    $stmtMaintenanceList = $pdo->prepare($queryMaintenanceList);
                                    $stmtMaintenanceList->execute(['id_vehicule' => $vehiculeId]);
                                    while ($row = $stmtMaintenanceList->fetch(PDO::FETCH_ASSOC)):
                                        ?>
                                        <tr>
                                            <td><?= $row['date_debut'] ?></td>
                                            <td><?= $row['date_fin'] ?? 'En cours' ?></td>
                                            <td><?= $row['description'] ?></td>
                                            <td><?= $row['cout'] ?> FCFA</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<!-- Inclure Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Injection des données PHP dans des variables globales
    window.graphData = <?= json_encode($graphData) ?>;
    window.maintenanceData = <?= json_encode($maintenanceData) ?>;
    window.fuelCostData = <?= json_encode($fuelCostData) ?>;
    window.vehiculeId = <?= json_encode($vehiculeId) ?>;
</script>
<!-- Ensuite, incluez votre fichier JavaScript externe -->
<script src="assets/js/statistics_vehicule.js"></script>


<!-- Initialiser DataTable dans vehicule_details.php -->
<script src="assets/js/dataTable_for_doc_car.js"></script>
<!-- Inclure le footer -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>