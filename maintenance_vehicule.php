<?php
// Inclure les requêtes nécessaires
include_once("request" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "maintenance.php");

$today = date('Y-m-d');
?>

<!-- Inclure le header -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>

<!-- Contenu de la page -->
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1>
                <i class="fas fa-tools me-2"></i>Maintenance du véhicule
            </h1>
            <a href="gestion_vehicules.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Alerte de succès -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Opération réalisée avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Alerte d'erreur -->
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $errorMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Informations du véhicule -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col-auto">
                    <?php if (!empty($vehicule['logo_marque_vehicule'])): ?>
                        <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($vehicule['logo_marque_vehicule']) ?>"
                            alt="Logo <?= htmlspecialchars($vehicule['marque']) ?>" class="img-thumbnail"
                            style="max-height: 60px; max-width: 100px;">
                    <?php else: ?>
                        <div class="border rounded p-2 text-center bg-light">
                            <i class="fas fa-car fa-2x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <h3 class="mb-0"><?= htmlspecialchars($vehicule['marque']) ?>
                        <?= htmlspecialchars($vehicule['modele']) ?>
                    </h3>
                    <p class="text-muted mb-0">
                        <strong>Immatriculation:</strong> <?= htmlspecialchars($vehicule['immatriculation']) ?> |
                        <strong>Statut:</strong>
                        <span class="badge <?= ($vehicule['statut'] === 'maintenance') ? 'bg-info' :
                            (($vehicule['statut'] === 'disponible') ? 'bg-success' :
                                (($vehicule['statut'] === 'en_course') ? 'bg-warning' : 'bg-danger')) ?>">
                            <?= ucfirst(htmlspecialchars($vehicule['statut'])) ?>
                        </span>
                        <?php if (!empty($vehicule['nom_zone'])): ?>
                            | <strong>Zone:</strong> <?= htmlspecialchars($vehicule['nom_zone']) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation par onglets -->
    <ul class="nav nav-tabs mb-4" id="maintenanceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="current-tab" data-bs-toggle="tab" data-bs-target="#current"
                type="button" role="tab" aria-controls="current" aria-selected="true">
                <i class="fas fa-wrench me-2"></i>Maintenances en cours
                <?php if (count($maintenancesEnCours) > 0): ?>
                    <span class="badge bg-warning ms-2"><?= count($maintenancesEnCours) ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button"
                role="tab" aria-controls="history" aria-selected="false">
                <i class="fas fa-history me-2"></i>Historique
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab"
                aria-controls="add" aria-selected="false">
                <i class="fas fa-plus-circle me-2"></i>Nouvelle maintenance
            </button>
        </li>
    </ul>

    <!-- Contenu des onglets -->
    <div class="tab-content" id="maintenanceTabsContent">

        <!-- Onglet des maintenances en cours -->
        <div class="tab-pane fade show active" id="current" role="tabpanel" aria-labelledby="current-tab">
            <?php if (count($maintenancesEnCours) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="maintenancesEnCours">
                        <thead class="table-light">
                            <tr>
                                <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                <th><i class="fas fa-tools me-1"></i>Type</th>
                                <th><i class="fas fa-align-left me-1"></i>Description</th>
                                <th><i class="fas fa-calendar-alt me-1"></i>Date de début</th>
                                <th><i class="fas fa-calendar-check me-1"></i>Date de fin prévue</th>
                                <th><i class="fas fa-money-bill-wave me-1"></i>Coût estimé</th>
                                <th><i class="fas fa-info-circle me-1"></i>Statut</th>
                                <th><i class="fas fa-cogs me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maintenancesEnCours as $maintenance): ?>
                                <tr>
                                    <td><?= $maintenance['id_maintenance'] ?></td>
                                    <td><?= formatMaintenanceType($maintenance['type_maintenance']) ?></td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;"
                                            title="<?= htmlspecialchars($maintenance['description']) ?>">
                                            <?= htmlspecialchars($maintenance['description']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($maintenance['date_demarrage'] ?? $maintenance['date_debut'])) ?></td>

                                    <td><?= date('d/m/Y', strtotime($maintenance['date_fin_prevue'])) ?></td>
                                    <td>
                                        <?= !empty($maintenance['cout'])
                                            ? number_format($maintenance['cout'], 0, ',', ' ') . ' FCFA'
                                            : '---'
                                            ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= getMaintenanceStatusClass($maintenance['statut']) ?>">
                                            <?= ucfirst($maintenance['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($maintenance['statut'] === 'planifiee'): ?>
                                            <!-- Bouton pour démarrer la maintenance -->
                                            <button type="button" class="btn btn-primary btn-sm me-1" title="Démarrer"
                                                onclick="confirmerAction('démarrer', 
                                                'Êtes-vous sûr de vouloir démarrer cette maintenance?', 
                                                'Cette action changera le statut de la maintenance à \'en cours\' et mettra le véhicule en maintenance.',
                                                'actions/vehicules/maintenance_vehicule.php?id=<?= $vehiculeId ?>&action=demarrer&maintenance_id=<?= $maintenance['id_maintenance'] ?>')">
                                                <i class="fas fa-play-circle"></i>
                                            </button>

                                            <!-- Bouton pour annuler la maintenance (uniquement si planifiée) -->
                                            <button type="button" class="btn btn-danger btn-sm" title="Annuler"
                                                onclick="confirmerAction('annuler', 
                                                'Êtes-vous sûr de vouloir annuler cette maintenance?', 
                                                'Cette action annulera définitivement la maintenance planifiée.',
                                                'actions/vehicules/maintenance_vehicule.php?id=<?= $vehiculeId ?>&action=annuler&maintenance_id=<?= $maintenance['id_maintenance'] ?>')">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        <?php elseif ($maintenance['statut'] === 'en_cours'): ?>
                                            <!-- Bouton pour terminer la maintenance (uniquement si en cours) -->
                                            <button type="button" class="btn btn-success btn-sm me-1" title="Terminer"
                                                onclick="terminerMaintenanceAvecCout(<?= $maintenance['id_maintenance'] ?>,<?= $vehiculeId ?>)">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Voir détails -->
                                        <button class="btn btn-sm btn-info m-1 view-maintenance-details"
                                            data-id="<?= $maintenance['id_maintenance'] ?>" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <!-- Bouton pour modifier la maintenance (toujours disponible) -->
                                        <a href="modifier_maintenance.php?id=<?= $maintenance['id_maintenance'] ?>"
                                            class="btn btn-warning btn-sm me-1" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Aucune maintenance en cours pour ce véhicule.
                </div>
            <?php endif; ?>
        </div>

        <!-- Onglet historique -->
        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
            <!-- Sous-navigation pour l'historique -->
            <ul class="nav nav-pills mb-3" id="historySubTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="completed-tab" data-bs-toggle="pill" data-bs-target="#completed"
                        type="button" role="tab" aria-controls="completed" aria-selected="true">
                        <i class="fas fa-check-circle me-2"></i>Maintenances terminées
                        <?php if (count($maintenancesTerminees) > 0): ?>
                            <span class="badge bg-success ms-2"><?= count($maintenancesTerminees) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="canceled-tab" data-bs-toggle="pill" data-bs-target="#canceled"
                        type="button" role="tab" aria-controls="canceled" aria-selected="false">
                        <i class="fas fa-ban me-2"></i>Maintenances annulées
                        <?php if (count($maintenancesAnnulees) > 0): ?>
                            <span class="badge bg-danger ms-2"><?= count($maintenancesAnnulees) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
            </ul>

            <!-- Contenu des sous-onglets de l'historique -->
            <div class="tab-content" id="historySubContent">
                <!-- Maintenances terminées -->
                <div class="tab-pane fade show active" id="completed" role="tabpanel" aria-labelledby="completed-tab">
                    <?php if (count($maintenancesTerminees) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="maintenancesTermineesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                        <th><i class="fas fa-tools me-1"></i>Type</th>
                                        <th><i class="fas fa-align-left me-1"></i>Description</th>
                                        <th><i class="fas fa-calendar-alt me-1"></i>Date de début</th>
                                        <th><i class="fas fa-calendar-check me-1"></i>Date de fin effective</th>
                                        <th><i class="fas fa-money-bill-wave me-1"></i>Coût final</th>
                                        <th><i class="fas fa-user me-1"></i>Prestataire</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenancesTerminees as $maintenance): ?>
                                        <tr>
                                            <td><?= $maintenance['id_maintenance'] ?></td>
                                            <td><?= formatMaintenanceType($maintenance['type_maintenance']) ?></td>
                                            <td>
                                                <span class="d-inline-block text-truncate" style="max-width: 200px;"
                                                    title="<?= htmlspecialchars($maintenance['description']) ?>">
                                                    <?= htmlspecialchars($maintenance['description']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($maintenance['date_debut'])) ?></td>
                                            <td>
                                                <?= !empty($maintenance['date_fin_effective'])
                                                    ? date('d/m/Y', strtotime($maintenance['date_fin_effective']))
                                                    : '---'
                                                    ?>
                                            </td>
                                            <td>
                                                <?= !empty($maintenance['cout'])
                                                    ? number_format($maintenance['cout'], 0, ',', ' ') . ' FCFA'
                                                    : '---'
                                                    ?>
                                            </td>
                                            <td><?= htmlspecialchars($maintenance['prestataire'] ?? '---') ?></td>
                                            <td>
                                                <!-- Bouton pour voir les détails -->
                                                <button class="btn btn-sm btn-info m-1 view-maintenance-details"
                                                    data-id="<?= $maintenance['id_maintenance'] ?>" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <!-- Bouton pour imprimer le rapport -->
                                                <a href="imprimer_rapport_maintenance.php?id=<?= $maintenance['id_maintenance'] ?>"
                                                    class="btn btn-secondary btn-sm" title="Imprimer rapport">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Aucune maintenance terminée dans l'historique.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Maintenances annulées -->
                <div class="tab-pane fade" id="canceled" role="tabpanel" aria-labelledby="canceled-tab">
                    <?php if (count($maintenancesAnnulees) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="maintenancesAnnuleesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                        <th><i class="fas fa-tools me-1"></i>Type</th>
                                        <th><i class="fas fa-align-left me-1"></i>Description</th>
                                        <th><i class="fas fa-calendar-alt me-1"></i>Date prévue</th>
                                        <th><i class="fas fa-money-bill-wave me-1"></i>Coût estimé</th>
                                        <th><i class="fas fa-user me-1"></i>Prestataire</th>
                                        <th><i class="fas fa-calendar-times me-1"></i>Date d'annulation</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenancesAnnulees as $maintenance): ?>
                                        <tr>
                                            <td><?= $maintenance['id_maintenance'] ?></td>
                                            <td><?= formatMaintenanceType($maintenance['type_maintenance']) ?></td>
                                            <td>
                                                <span class="d-inline-block text-truncate" style="max-width: 200px;"
                                                    title="<?= htmlspecialchars($maintenance['description']) ?>">
                                                    <?= htmlspecialchars($maintenance['description']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($maintenance['date_debut'])) ?></td>
                                            <td>
                                                <?= !empty($maintenance['cout'])
                                                    ? number_format($maintenance['cout'], 0, ',', ' ') . ' FCFA'
                                                    : '---'
                                                    ?>
                                            </td>
                                            <td><?= htmlspecialchars($maintenance['prestataire'] ?? '---') ?></td>
                                            <td><?= date('d/m/Y', strtotime($maintenance['updated_at'] ?? $maintenance['date_debut'])) ?>
                                            </td>
                                            <td>
                                                <!-- Bouton pour voir les détails -->
                                                <button class="btn btn-sm btn-info m-1 view-maintenance-details"
                                                    data-id="<?= $maintenance['id_maintenance'] ?>" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Aucune maintenance annulée dans l'historique.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Onglet d'ajout d'une nouvelle maintenance -->
        <div class="tab-pane fade" id="add" role="tabpanel" aria-labelledby="add-tab">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter une nouvelle maintenance</h5>
                </div>
                <div class="card-body">
                    <form id="addMaintenanceForm" method="POST"
                        action="actions/vehicules/maintenance_vehicule.php?id=<?= $vehiculeId ?>">
                        <input type="hidden" name="action" value="add_maintenance">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type_maintenance" class="form-label">Type de maintenance</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tools"></i></span>
                                        <select class="form-select" id="type_maintenance" name="type_maintenance"
                                            required>
                                            <option value="" selected disabled>Sélectionnez le type</option>
                                            <option value="preventive">Préventive</option>
                                            <option value="corrective">Corrective</option>
                                            <option value="revision">Révision</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="statut" class="form-label">Statut initial</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                        <select class="form-select" id="statut" name="statut" required>
                                            <option value="planifiee">Planifiée</option>
                                            <option value="en_cours">En cours</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description des travaux</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    required></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_debut" class="form-label">Date de début</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" class="form-control" id="date_debut" name="date_debut"
                                            min="<?= $today ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_fin_prevue" class="form-label">Date de fin prévue</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                        <input type="date" class="form-control" id="date_fin_prevue"
                                            name="date_fin_prevue" min="<?= $today ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cout_estime" class="form-label">Coût estimé (FCFA)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                        <input type="number" class="form-control" id="cout_estime" name="cout_estime"
                                            min="0">
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kilometrage" class="form-label">Kilométrage actuel</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                        <input type="number" class="form-control" id="kilometrage" name="kilometrage"
                                            value="<?= $vehicule['kilometrage_actuel'] ?>" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prestataire" class="form-label">Prestataire</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                        <input type="text" class="form-control" id="prestataire" name="prestataire">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer la maintenance
                            </button>
                            <button type="reset" class="btn btn-secondary ms-2">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmationMessage">
                Êtes-vous sûr de vouloir effectuer cette action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="#" id="confirmActionBtn" class="btn btn-danger">Confirmer</a>
            </div>
        </div>
    </div>
</div>

<?php include_once("includes" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "modal_cout_final_maintenance.php") ?>
<?php include_once("includes" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "modal" . DIRECTORY_SEPARATOR . "modal_détails_maintenance.php") ?>

<!-- JavaScript pour validation et dataTables -->
<script src="assets/js/vehicules/maintenance.js"></script>
<script src="assets/js/vehicules/sweet_alert_maintenance.js"></script>
<script src="assets/js/vehicules/maintenance_details.js"></script>

<!-- Inclusion du footer -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>