<?php
// Inclure les requêtes nécessaires
include_once("request" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "modifier_maintenance.php");
$today = date('Y-m-d');
?>

<!-- Inclure le header -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>

<!-- Contenu de la page -->
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1>
                <i class="fas fa-edit me-2"></i>Modifier la maintenance
            </h1>
            <a href="maintenance_vehicule.php?id=<?= $vehiculeId ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

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
                    <?php if (!empty($maintenance['logo_marque_vehicule'])): ?>
                        <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($maintenance['logo_marque_vehicule']) ?>"
                            alt="Logo <?= htmlspecialchars($maintenance['marque']) ?>" class="img-thumbnail"
                            style="max-height: 60px; max-width: 100px;">
                    <?php else: ?>
                        <div class="border rounded p-2 text-center bg-light">
                            <i class="fas fa-car fa-2x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <h3 class="mb-0"><?= htmlspecialchars($maintenance['marque']) ?>
                        <?= htmlspecialchars($maintenance['modele']) ?>
                    </h3>
                    <p class="text-muted mb-0">
                        <strong>Immatriculation:</strong> <?= htmlspecialchars($maintenance['immatriculation']) ?> |
                        <strong>Statut:</strong>
                        <span class="badge <?= ($maintenance['statut_vehicule'] === 'maintenance') ? 'bg-info' :
                            (($maintenance['statut_vehicule'] === 'disponible') ? 'bg-success' :
                                (($maintenance['statut_vehicule'] === 'en_course') ? 'bg-warning' : 'bg-danger')) ?>">
                            <?= ucfirst(htmlspecialchars($maintenance['statut_vehicule'])) ?>
                        </span>
                        <?php if (!empty($maintenance['nom_zone'])): ?>
                            | <strong>Zone:</strong> <?= htmlspecialchars($maintenance['nom_zone']) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails de la maintenance actuelle -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>Maintenance #<?= $maintenanceId ?>
                <span class="badge <?= getMaintenanceStatusClass($maintenance['statut']) ?> ms-2">
                    <?= ucfirst($maintenance['statut']) ?>
                </span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Type:</strong> <?= formatMaintenanceType($maintenance['type_maintenance']) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Date de début:</strong> <?= date('d/m/Y', strtotime($maintenance['date_debut'])) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Date de fin prévue:</strong>
                        <?= date('d/m/Y', strtotime($maintenance['date_fin_prevue'])) ?></p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p><strong>Description:</strong> <?= htmlspecialchars($maintenance['description']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de modification -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier les détails</h5>
        </div>
        <div class="card-body">
            <form id="updateMaintenanceForm" method="POST">
                <input type="hidden" name="id_maintenance" value="<?= $maintenanceId ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="type_maintenance" class="form-label">Type de maintenance</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tools"></i></span>
                                <select class="form-select" id="type_maintenance" name="type_maintenance" required
                                    <?= in_array($maintenance['statut'], ['terminee', 'annulee']) ? 'disabled' : '' ?>>
                                    <option value="preventive" <?= $maintenance['type_maintenance'] === 'preventive' ? 'selected' : '' ?>>Préventive</option>
                                    <option value="corrective" <?= $maintenance['type_maintenance'] === 'corrective' ? 'selected' : '' ?>>Corrective</option>
                                    <option value="revision" <?= $maintenance['type_maintenance'] === 'revision' ? 'selected' : '' ?>>Révision</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <select class="form-select" id="statut" name="statut" required
                                    data-statut-initial="<?= $maintenance['statut'] ?>"
                                    <?= in_array($maintenance['statut'], ['terminee', 'annulee']) ? 'disabled' : '' ?>>
                                    <?php foreach ($statutsDisponibles as $statut): ?>
                                        <option value="<?= $statut ?>" <?= $maintenance['statut'] === $statut ? 'selected' : '' ?>>
                                            <?= ucfirst($statut) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description des travaux</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                        <textarea class="form-control" id="description" name="description" rows="3" required
                            <?= in_array($maintenance['statut'], ['terminee', 'annulee']) ? 'readonly' : '' ?>><?= htmlspecialchars($maintenance['description']) ?></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" class="form-control" id="date_debut" name="date_debut"
                                    value="<?= formatDateForInput($maintenance['date_debut']) ?>"
                                    <?= $maintenance['statut'] === 'planifiee' ? 'min="' . $today . '"' : '' ?> required
                                    <?= in_array($maintenance['statut'], ['terminee', 'annulee']) ? 'readonly' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="date_fin_prevue" class="form-label">Date de fin prévue</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                <input type="date" class="form-control" id="date_fin_prevue" name="date_fin_prevue"
                                    value="<?= formatDateForInput($maintenance['date_fin_prevue']) ?>" required
                                    <?= in_array($maintenance['statut'], ['terminee', 'annulee']) ? 'readonly' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="date_fin_effective" class="form-label">Date de fin effective</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                <input type="date" class="form-control" id="date_fin_effective"
                                    name="date_fin_effective"
                                    value="<?= formatDateForInput($maintenance['date_fin_effective']) ?>"
                                    <?= $maintenance['statut'] !== 'terminee' ? 'disabled' : 'readonly' ?>>
                            </div>
                            <small class="text-muted">Rempli automatiquement à la fin de la maintenance</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="kilometrage" class="form-label">Kilométrage</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                <input type="number" class="form-control" id="kilometrage" name="kilometrage"
                                    value="<?= $maintenance['kilometrage'] ?>" min="0"
                                    <?= in_array($maintenance['statut'], ['terminee', 'annulee']) ? 'readonly' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="prestataire" class="form-label">Prestataire</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                <input type="text" class="form-control" id="prestataire" name="prestataire"
                                    value="<?= htmlspecialchars($maintenance['prestataire'] ?? '') ?>"
                                    <?= in_array($maintenance['statut'], ['terminee', 'annulee']) ? 'readonly' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <?php if (!in_array($maintenance['statut'], ['terminee', 'annulee'])): ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>

                        <?php if ($maintenance['statut'] === 'planifiee'): ?>
                            <button type="button" class="btn btn-success ms-2"
                                onclick="confirmerAction('démarrer', 
                                   'Voulez-vous démarrer cette maintenance?', 
                                   'Cette action changera le statut à \'En cours\' et mettra le véhicule en maintenance.',
                                   'actions/vehicules/maintenance_vehicule.php?id=<?= $vehiculeId ?>&action=demarrer&maintenance_id=<?= $maintenanceId ?>')">
                                <i class="fas fa-play-circle me-2"></i>Démarrer la maintenance
                            </button>

                            <button type="button" class="btn btn-danger ms-2"
                                onclick="confirmerAction('annuler', 
                                   'Voulez-vous annuler cette maintenance?', 
                                   'Cette action annulera définitivement la maintenance planifiée.',
                                   'actions/vehicules/maintenance_vehicule.php?id=<?= $vehiculeId ?>&action=annuler&maintenance_id=<?= $maintenanceId ?>')">
                                <i class="fas fa-times-circle me-2"></i>Annuler la maintenance
                            </button>
                        <?php elseif ($maintenance['statut'] === 'en_cours'): ?>
                            <button type="button" class="btn btn-success ms-2" onclick="terminerMaintenanceAvecCout(<?= $maintenanceId ?>, 
                                    <?= $vehiculeId ?>, 
                                    '<?= htmlspecialchars($maintenance['prestataire'] ?? '') ?>', 
                                    '<?= $maintenance['cout'] ?? '' ?>')">
                                <i class="fas fa-check-circle me-2"></i>Terminer la maintenance
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <a href="maintenance_vehicule.php?id=<?= $vehiculeId ?>" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript pour validation du formulaire et confirmation des actions -->
<script src="assets/js/vehicules/modifier_maintenance.js"></script>

<!-- Inclusion du footer -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>