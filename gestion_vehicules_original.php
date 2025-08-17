<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Inclure les requêtes nécessaires
include_once("request" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "vehicules.php");

// Inclure les messages d'alerte
include_once("alerts" . DIRECTORY_SEPARATOR . "alert_vehicule.php");

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
?>

<!--start container   -->
<h1 class="text-center mb-4">Gestion des véhicules</h1>

<!-- Onglets de navigation -->
<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button"
            role="tab" aria-controls="list" aria-selected="true">
            <i class="fas fa-car me-2"></i>Liste des véhicules
        </button>
    </li>
    <?php if ($roleAccess->hasPermission('tracking')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="all-maintenance-tab" data-bs-toggle="tab" data-bs-target="#all-maintenance"
                type="button" role="tab" aria-controls="all-maintenance" aria-selected="false">
                <i class="fas fa-wrench me-2"></i>Toutes les maintenances
            </button>
        </li>
    <?php endif; ?>
    <?php if ($roleAccess->hasPermission('tracking')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="approvisionnements-tab" data-bs-toggle="tab" data-bs-target="#approvisionnements"
                type="button" role="tab" aria-controls="approvisionnements" aria-selected="false">
                <i class="fas fa-gas-pump me-2"></i>Approvisionnements
            </button>
        </li>
    <?php endif; ?>
    <?php if ($roleAccess->hasPermission('form')): ?>
        <!-- Nouvel onglet pour l'enregistrement des véhicules -->
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="add-vehicle-tab" data-bs-toggle="tab" data-bs-target="#add-vehicle" type="button"
                role="tab" aria-controls="add-vehicle" aria-selected="false">
                <i class="fas fa-plus me-2"></i>Ajouter un véhicule
            </button>
        </li>
    <?php endif; ?>
</ul>

    <!-- Contenu des onglets -->
    <div class="tab-content" id="myTabContent">

    <!-- Liste des véhicules -->
    <footer>
  <p class="text-danger">Debut Footer test 1</p>
</footer>
        <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
            <div class="section">
                <h2><i class="fas fa-car me-2"></i>Liste des véhicules</h2>
                <hr>
                <!-- Liste des véhicules -->
                <div class="row" id="vehicleList">

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="vehiculesTable">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-image"></i> Logo</th>
                                            <th><i class="fas fa-align-left"></i> Description</th>
                                            <th><i class="fas fa-users"></i> Capacité</th>
                                            <th><i class="fas fa-gas-pump"></i> Carburant</th>
                                            <th><i class="fas fa-info-circle"></i> Statut</th>
                                            <th><i class="fas fa-map-marker-alt"></i> Zone</th>
                                            <th><i class="fas fa-cogs"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vehicules as $vehicule) {
                                            // Définir la classe pour le statut
                                            $statusClass = '';
                                            switch ($vehicule['statut']) {
                                                case 'disponible':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'en_course':
                                                    $statusClass = 'bg-warning';
                                                    break;
                                                case 'maintenance':
                                                    $statusClass = 'bg-info';
                                                    break;
                                                case 'hors_service':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="logo_marque_vehicule">
                                                        <?php if (!empty($vehicule['logo_marque_vehicule'])): ?>
                                                            <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($vehicule['logo_marque_vehicule']) ?>"
                                                                class="img-fluid"
                                                                alt="Logo <?= htmlspecialchars($vehicule['marque']) ?>"
                                                                style="max-height: 50px;">
                                                        <?php else: ?>
                                                            <div class="text-center text-muted"><i class="fas fa-car fa-2x"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($vehicule['marque']) ?> -
                                                        <?= htmlspecialchars($vehicule['modele']) ?></strong>
                                                    <div><small
                                                            class="text-muted"><?= htmlspecialchars($vehicule['immatriculation']) ?></small>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($vehicule['capacite_passagers']) ?> places</td>
                                                <td><?= htmlspecialchars($vehicule['type_carburant']) ?></td>
                                                <td><span
                                                        class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($vehicule['statut'])) ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($vehicule['nom_zone'] ?? 'Non définie') ?></td>
                                                <td>
                                                    <?php if ($roleAccess->hasPermission('tracking')): ?>
                                                        <!-- Maintenance -->
                                                        <a href="maintenance_vehicule.php?id=<?= $vehicule['id_vehicule'] ?>"
                                                            title="Maintenance" class="btn btn-dark btn-sm m-1">
                                                            <i class="fas fa-tools"></i>
                                                        </a>

                                                        <!-- Approvisionnement -->
                                                        <a href="approvisionnement_carburant.php?id=<?= $vehicule['id_vehicule'] ?>"
                                                            title="Approvisionnement" class="btn btn-primary btn-sm m-1">
                                                            <i class="fas fa-gas-pump"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <!-- Détails -->
                                                    <a href="details_vehicule.php?id=<?= $vehicule['id_vehicule'] ?>"
                                                        title="Détails" class="btn btn-info btn-sm m-1">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
                                                        <!-- Modifier -->
                                                        <a href="modifier_vehicule.php?id=<?= $vehicule['id_vehicule'] ?>"
                                                            title="Modifier" class="btn btn-warning btn-sm m-1">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($roleAccess->hasPermission('deleteHistorique')): ?>
                                                        <!-- Supprimer -->
                                                        <button type="button" class="btn btn-danger btn-sm m-1"
                                                            title="Supprimer"
                                                            onclick="confirmerSuppression(<?= $vehicule['id_vehicule'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <footer>
  <p class="text-danger">Fin Footer test 1</p>
</footer>
    <?php if ($roleAccess->hasPermission('tracking')): ?>
        <footer>
  <p class="text-danger">Debut Footer test 2</p>
</footer>
        <!-- Onglet "Toutes les maintenances" -->
        <div class="tab-pane fade" id="all-maintenance" role="tabpanel" aria-labelledby="all-maintenance-tab">
            <div class="section">
                <h2>
                    <i class="fas fa-wrench me-2"></i>Toutes les maintenances
                </h2>
                <hr>

                <!-- Filtres de recherche -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
                    </div>
                    <div class="card-body">
                        <form id="maintenanceFilterForm" class="row g-3">
                            <div class="col-md-3">
                                <label for="filterVehicule" class="form-label">Véhicule</label>
                                <select class="form-select" id="filterVehicule">
                                    <option value="">Tous les véhicules</option>
                                    <?php foreach ($vehicules as $vehicule): ?>
                                        <option value="<?= $vehicule['id_vehicule'] ?>">
                                            <?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['immatriculation'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterType" class="form-label">Type de maintenance</label>
                                <select class="form-select" id="filterType">
                                    <option value="">Tous les types</option>
                                    <option value="preventive">Préventive</option>
                                    <option value="corrective">Corrective</option>
                                    <option value="revision">Révision</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterStatut" class="form-label">Statut</label>
                                <select class="form-select" id="filterStatut">
                                    <option value="">Tous les statuts</option>
                                    <option value="planifiee">Planifiée</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="terminee">Terminée</option>
                                    <option value="annulee">Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterDateDebut" class="form-label">Date (début)</label>
                                <input type="date" class="form-control" id="filterDateDebut">
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo me-2"></i>Réinitialiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tableau des maintenances -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="allMaintenancesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-image me-1"></i>Véhicule</th>
                                        <th><i class="fas fa-tools me-1"></i>Type</th>
                                        <th><i class="fas fa-align-left me-1"></i>Description</th>
                                        <th><i class="fas fa-calendar-alt me-1"></i>Date début</th>
                                        <th><i class="fas fa-calendar-check me-1"></i>Date fin prévue</th>
                                        <th><i class="fas fa-calendar-check me-1"></i>Date fin effective</th>
                                        <th><i class="fas fa-money-bill-wave me-1"></i>Coût</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Statut</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Récupérer toutes les maintenances avec les informations du véhicule
                                    $stmt = $pdo->prepare("
                                SELECT m.*, v.marque, v.modele, v.immatriculation, v.logo_marque_vehicule, z.nom_zone
                                FROM maintenances m
                                LEFT JOIN vehicules v ON m.id_vehicule = v.id_vehicule
                                LEFT JOIN zone_vehicules z ON v.id_zone = z.id
                                ORDER BY m.date_debut DESC
                            ");
                                    $stmt->execute();
                                    $toutes_maintenances = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($toutes_maintenances as $maintenance):
                                        // Définir la classe pour le statut
                                        $statusClass = '';
                                        switch ($maintenance['statut']) {
                                            case 'planifiee':
                                                $statusClass = 'bg-primary';
                                                break;
                                            case 'en_cours':
                                                $statusClass = 'bg-warning';
                                                break;
                                            case 'terminee':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'annulee':
                                                $statusClass = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <tr class="maintenance-row" data-vehicule="<?= $maintenance['id_vehicule'] ?>"
                                            data-type="<?= $maintenance['type_maintenance'] ?>"
                                            data-statut="<?= $maintenance['statut'] ?>"
                                            data-date="<?= $maintenance['date_debut'] ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($maintenance['logo_marque_vehicule'])): ?>
                                                        <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($maintenance['logo_marque_vehicule']) ?>"
                                                            class="me-2" alt="Logo"
                                                            style="width: 30px; height: 30px; object-fit: contain;">
                                                    <?php else: ?>
                                                        <i class="fas fa-car me-2 text-secondary"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($maintenance['marque'] . ' ' . $maintenance['modele']) ?></strong>
                                                        <div><small
                                                                class="text-muted"><?= htmlspecialchars($maintenance['immatriculation']) ?></small>
                                                        </div>
                                                        <div><small
                                                                class="text-muted"><?= htmlspecialchars($maintenance['nom_zone'] ?? 'N/A') ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                switch ($maintenance['type_maintenance']) {
                                                    case 'preventive':
                                                        echo '<span class="badge bg-info">Préventive</span>';
                                                        break;
                                                    case 'corrective':
                                                        echo '<span class="badge bg-warning">Corrective</span>';
                                                        break;
                                                    case 'revision':
                                                        echo '<span class="badge bg-secondary">Révision</span>';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td><?= htmlspecialchars($maintenance['description']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($maintenance['date_debut'])) ?></td>
                                            <td><?= date('d/m/Y', strtotime($maintenance['date_fin_prevue'])) ?></td>
                                            <td>
                                                <?= $maintenance['date_fin_effective']
                                                    ? date('d/m/Y', strtotime($maintenance['date_fin_effective']))
                                                    : '<span class="text-muted">---</span>' ?>
                                            </td>
                                            <td>
                                                <?= $maintenance['cout']
                                                    ? number_format($maintenance['cout'], 0, ',', ' ') . ' FCFA'
                                                    : '<span class="text-muted">---</span>' ?>
                                            </td>
                                            <td><span
                                                    class="badge <?= $statusClass ?>"><?= ucfirst($maintenance['statut']) ?></span>
                                            </td>
                                            <td>
                                                <!-- Voir détails -->
                                                <button class="btn btn-sm btn-info m-1 view-maintenance-details"
                                                    data-id="<?= $maintenance['id_maintenance'] ?>" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <?php if ($maintenance['statut'] == 'planifiee' || $maintenance['statut'] == 'en_cours'): ?>
                                                    <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
                                                        <!-- Modifier -->
                                                        <a href="modifier_maintenance.php?id=<?= $maintenance['id_maintenance'] ?>"
                                                            class="btn btn-sm btn-warning m-1" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($maintenance['statut'] == 'en_cours' && $roleAccess->hasPermission('validateRequest')): ?>
                                                        <!-- Terminer -->
                                                        <button type="button"
                                                            class="btn btn-sm btn-success m-1 btn-terminer-maintenance"
                                                            data-id="<?= $maintenance['id_maintenance'] ?>" title="Terminer">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($roleAccess->hasPermission('rejectRequest')): ?>
                                                        <!-- Annuler -->
                                                        <button type="button" class="btn btn-sm btn-danger m-1 btn-annuler-maintenance"
                                                            data-id="<?= $maintenance['id_maintenance'] ?>" title="Annuler">
                                                            <i class="fas fa-times-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
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
        <footer>
  <p class="text-danger">Fin Footer test 2</p>
</footer>
    <?php endif; ?>

    <?php if ($roleAccess->hasPermission('tracking')): ?>
        <footer>
  <p class="text-danger">Debut Footer test 3</p>
</footer>
        <!-- Onglet Approvisionnements -->
        <div class="tab-pane fade" id="approvisionnements" role="tabpanel" aria-labelledby="approvisionnements-tab">
        <footer>
  <p class="text-success">Debut Footer test 3</p>
</footer>
            <div class="section">
            <footer>
  <p class="text-success">Debut Footer test 3</p>
</footer>
                <h2>
                    <i class="fas fa-gas-pump me-2"></i>Historique des approvisionnements
                </h2>
                <hr>

                <!-- Filtres de recherche -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
                    </div>
                    <div class="card-body">
                        <form id="approvisionnementFilterForm" class="row g-3">
                            <div class="col-md-3">
                                <label for="filterVehiculeAppro" class="form-label">Véhicule</label>
                                <select class="form-select" id="filterVehiculeAppro">
                                    <option value="">Tous les véhicules</option>
                                    <?php
                                    // Récupérer la liste des véhicules
                                    $stmt_vehicules = $pdo->query("SELECT id_vehicule, marque, modele, immatriculation FROM vehicules");
                                    $vehicules_list = $stmt_vehicules->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($vehicules_list as $vehicule): ?>
                                        <option value="<?= $vehicule['id_vehicule'] ?>">
                                            <?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['immatriculation'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterTypeCarburant" class="form-label">Type de carburant</label>
                                <select class="form-select" id="filterTypeCarburant">
                                    <option value="">Tous les types</option>
                                    <option value="essence">Super</option>
                                    <option value="diesel">Gasoil</option>
                                    <option value="hybride">Essence</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterDateDebutAppro" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="filterDateDebutAppro">
                            </div>
                            <div class="col-md-3">
                                <label for="filterDateFinAppro" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="filterDateFinAppro">
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo me-2"></i>Réinitialiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tableau des approvisionnements -->
                <div class="card">
                <footer>
  <p class="text-warning">Debut Footer test 3</p>
</footer>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="allApprovisionnementsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-car me-1"></i>Véhicule</th>
                                        <th><i class="fas fa-user me-1"></i>Chauffeur</th>
                                        <th><i class="fas fa-calendar me-1"></i>Date</th>
                                        <th><i class="fas fa-tint me-1"></i>Quantité</th>
                                        <th><i class="fas fa-gas-pump me-1"></i>Type Carburant</th>
                                        <th><i class="fas fa-money-bill me-1"></i>Prix Total</th>
                                        <th><i class="fas fa-road me-1"></i>Kilométrage</th>
                                        <th><i class="fas fa-building me-1"></i>Station-service</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <footer>
  <p class="text-warning">Debut Footer test 3-1</p>
</footer>
                                <tbody>
                                    <?php
                                    // Récupérer tous les approvisionnements avec les informations du véhicule et du chauffeur
                                    $stmt = $pdo->prepare("
                                SELECT ac.*, 
                                       v.marque, v.modele, v.immatriculation, v.logo_marque_vehicule, 
                                       c.nom, c.prenoms
                                FROM approvisionnements_carburant ac
                                LEFT JOIN vehicules v ON ac.id_vehicule = v.id_vehicule
                                LEFT JOIN chauffeurs c ON ac.id_chauffeur = c.id_chauffeur
                                ORDER BY ac.date_approvisionnement DESC
                            ");
                                    $stmt->execute();
                                    $tous_approvisionnements = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($tous_approvisionnements as $approvisionnement):
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($approvisionnement['logo_marque_vehicule'])): ?>
                                                        <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($approvisionnement['logo_marque_vehicule']) ?>"
                                                            class="me-2" alt="Logo"
                                                            style="width: 30px; height: 30px; object-fit: contain;">
                                                    <?php else: ?>
                                                        <i class="fas fa-car me-2 text-secondary"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($approvisionnement['marque'] . ' ' . $approvisionnement['modele']) ?></strong>
                                                        <div><small
                                                                class="text-muted"><?= htmlspecialchars($approvisionnement['immatriculation']) ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?= $approvisionnement['nom']
                                                    ? htmlspecialchars($approvisionnement['nom'] . ' ' . $approvisionnement['prenoms'])
                                                    : '<em>Non spécifié</em>' ?>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($approvisionnement['date_approvisionnement'])) ?>
                                            </td>
                                            <td><?= number_format($approvisionnement['quantite_litres'], 2, ',', ' ') ?> L</td>
                                            <td>
                                                <?php
                                                switch ($approvisionnement['type_carburant']) {
                                                    case 'essence':
                                                        echo '<span class="badge bg-success">Super</span>';
                                                        break;
                                                    case 'diesel':
                                                        echo '<span class="badge bg-primary">Gasoil</span>';
                                                        break;
                                                    case 'hybride':
                                                        echo '<span class="badge bg-warning">Essence</span>';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td><?= number_format($approvisionnement['prix_total'], 0, ',', ' ') ?> FCFA</td>
                                            <td><?= number_format($approvisionnement['kilometrage'], 0, ',', ' ') ?> km</td>
                                            <td><?= htmlspecialchars($approvisionnement['station_service'] ?? 'N/A') ?></td>
                                            <td>
                                                <!-- Détails -->
                                                <button class="btn btn-sm btn-info view-appro-details"
                                                    data-id="<?= $approvisionnement['id_approvisionnement'] ?>"
                                                    title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <footer>
  <p class="text-warning">Debut Footer test 3-2</p>
</footer>
                            </table>
                        </div>
                        <footer>
  <p class="text-warning">Debut Footer test 3-3</p>
</footer>
                    </div>
                </div>
                <footer>
  <p class="text-success">Debut Footer test 3</p>
</footer>
            </div>
            <footer>
  <p class="text-success">Debut Footer test 3</p>
</footer>
        </div>

        <footer>
  <p class="text-danger">Fin Footer test 3</p>
</footer>
    <?php endif; ?>

    <?php if ($roleAccess->hasPermission('form')): ?>
        <footer>
  <p class="text-danger">Debut Footer test 4</p>
</footer>
        <!-- Nouvel onglet pour l'enregistrement des véhicules -->
        <div class="tab-pane fade" id="add-vehicle" role="tabpanel" aria-labelledby="add-vehicle-tab">
            <div class="card section">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-plus me-2"></i>Ajouter un véhicule
                    </h2>
                </div>
                <div class="card-body">
                    <form id="addVehicleForm" action="actions/vehicules/add_vehicle.php" method="POST"
                        enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="marque" class="form-label">Marque</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-car"></i>
                                        </span>
                                        <input type="text" class="form-control" id="marque" name="marque"
                                            required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ajouter un logo</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-upload"></i>
                                        </span>
                                        <input type="file" name="logo_marque_vehicule" class="form-control"
                                            accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="modele" class="form-label">Modèle</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-cogs"></i>
                                        </span>
                                        <input type="text" class="form-control" id="modele" name="modele"
                                            required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="immatriculation" class="form-label">Immatriculation</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-id-card"></i>
                                        </span>
                                        <input type="text" class="form-control" id="immatriculation" name="immatriculation"
                                            required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type_vehicule" class="form-label">Type de véhicule</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-truck"></i>
                                        </span>
                                        <select class="form-select" id="type_vehicule" name="type_vehicule"
                                            required="required">
                                            <option selected>Choisir le type de véhicule</option>
                                            <?php foreach ($enum_values as $value): ?>
                                                <option value="<?= $value; ?>"><?= ucfirst($value); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacite_passagers" class="form-label">Capacité</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-tachometer-alt"></i>
                                        </span>
                                        <input type="number" class="form-control" id="capacite_passagers"
                                            name="capacite_passagers" required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kilometrage_actuel" class="form-label">Kilométrage actuel</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-road"></i>
                                        </span>
                                        <input type="number" class="form-control" id="kilometrage_actuel"
                                            name="kilometrage_actuel" required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type_carburant" class="form-label">Type carburant</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-gas-pump"></i>
                                        </span>
                                        <select class="form-select" id="type_carburant" name="type_carburant"
                                            required="required">
                                            <option selected>Choisir le type de carburant</option>
                                            <?php foreach ($enum_values_type_carburant as $value): ?>
                                                <option value="<?= $value; ?>"><?= ucfirst($value); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="zone_vehicule" class="form-label">Zone véhicule</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                        <input type="text" class="form-control" id="zone_vehicule" name="zone_vehicule"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="id_zone" name="id_zone"> <!-- Champ caché pour stocker l'ID -->
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <footer>
  <p class="text-danger">Fin Footer test 5</p>
</footer>
    <?php endif; ?>

    </div>

    <!-- Contenu supplémentaire -->
    <div class="row">
        <div class="col-12">
            <p>Lorem, ipsum.</p>
        </div>
    </div>
    
<!--end container -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "zones" . DIRECTORY_SEPARATOR . "modal_creer_zone.php") ?>
<?php include_once("includes" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "modal" . DIRECTORY_SEPARATOR . "modal_détails_approvisionnement.php") ?>
<?php include_once("includes" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "modal" . DIRECTORY_SEPARATOR . "modal_détails_maintenance.php") ?>

<!-- Pass permissions to JavaScript for client-side controls -->
<script>
    // Transmit user permissions to JavaScript
    const userPermissions = <?= json_encode($roleAccess->getRolePermissions()) ?>;
</script>

<script src="assets/js/vehicules/zones/zone_vehicule.js"></script>
<script src="assets/js/vehicules/vehicules.js"></script>
<script src="assets/js/vehicules/filtrage_et_gestion_maintenances.js"></script>
<script src="assets/js/vehicules/approvisionnement_filtrage.js"></script>
<script src="assets/js/vehicules/maintenance_details.js"></script>
<!-- <script src="assets/js/système_filtre_maintenance.js"></script> -->

<!--start footer -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php"); ?>
<!--end footer -->