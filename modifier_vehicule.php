<?php
// Inclure les requêtes nécessaires
include_once("request" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "modifier_vehicule.php");
?>

<!--start header -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header -->

<!--start container -->
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1>
                <i class="fas fa-edit me-2"></i>Modifier un véhicule
            </h1>
            <a href="gestion_vehicules.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
    <div class="card p-3">
        <form id="editVehicleForm" action="modifier_vehicule.php?id=<?= $id_vehicule ?>" method="POST"
            enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="marque" class="form-label">Marque</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-car"></i>
                            </span>
                            <input type="text" class="form-control" id="marque" name="marque"
                                value="<?= htmlspecialchars($vehicule['marque']) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Logo de la marque</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-upload"></i>
                            </span>
                            <input type="file" name="logo_marque_vehicule" class="form-control" accept="image/*">
                        </div>
                        <?php if ($vehicule['logo_marque_vehicule']): ?>
                            <small class="text-muted">Logo actuel :
                                <?= htmlspecialchars($vehicule['logo_marque_vehicule']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="modele" class="form-label">Modèle</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-cogs"></i>
                            </span>
                            <input type="text" class="form-control" id="modele" name="modele"
                                value="<?= htmlspecialchars($vehicule['modele']) ?>" required>
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
                                value="<?= htmlspecialchars($vehicule['immatriculation']) ?>" required>
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
                            <select class="form-select" id="type_vehicule" name="type_vehicule" required>
                                <option value="utilitaire" <?= $vehicule['type_vehicule'] === 'utilitaire' ? 'selected' : '' ?>>Utilitaire</option>
                                <option value="berline" <?= $vehicule['type_vehicule'] === 'berline' ? 'selected' : '' ?>>
                                    Berline</option>
                                <option value="camion" <?= $vehicule['type_vehicule'] === 'camion' ? 'selected' : '' ?>>
                                    Camion</option>
                                <option value="bus" <?= $vehicule['type_vehicule'] === 'bus' ? 'selected' : '' ?>>Bus
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="capacite_passagers" class="form-label">Capacité</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-tachometer-alt"></i>
                            </span>
                            <input type="number" class="form-control" id="capacite_passagers" name="capacite_passagers"
                                value="<?= htmlspecialchars($vehicule['capacite_passagers']) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="kilometrage_actuel" class="form-label">Kilométrage actuel</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-road"></i>
                            </span>
                            <input type="number" class="form-control" id="kilometrage_actuel" name="kilometrage_actuel"
                                value="<?= htmlspecialchars($vehicule['kilometrage_actuel']) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="type_carburant" class="form-label">Type carburant</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-gas-pump"></i>
                            </span>
                            <select class="form-select" id="type_carburant" name="type_carburant" required>
                                <option value="Super" <?= $vehicule['type_carburant'] === 'Super' ? 'selected' : '' ?>>
                                    Super</option>
                                <option value="Gasoil" <?= $vehicule['type_carburant'] === 'Gasoil' ? 'selected' : '' ?>>
                                    Gasoil</option>
                                <option value="Essence" <?= $vehicule['type_carburant'] === 'Essence' ? 'selected' : '' ?>>
                                    Essence</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="zone_vehicule" class="form-label">Zone véhicule</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <input type="text" class="form-control" id="zone_vehicule" name="zone_vehicule"
                                value="<?= htmlspecialchars($vehicule['nom_zone']) ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="id_zone" name="id_zone" value="<?= htmlspecialchars($vehicule['id_zone']) ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

<?php include_once("includes" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "zones" . DIRECTORY_SEPARATOR . "modal_creer_zone.php") ?>

<!--end container -->
<script src="assets/js/vehicules/zones/zone_vehicule.js"></script>
<!--start footer -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer -->