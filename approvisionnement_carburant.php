<?php
// Inclure les requêtes nécessaires
include_once("request" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "approvisionnement_carburant.php");

// Inclure le header
include_once("includes" . DIRECTORY_SEPARATOR . "header.php");
?>

<div class="container mt-4">
    <!-- Fil d'Ariane -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item"><a href="gestion_vehicules.php">Gestion des véhicules</a></li>
            <li class="breadcrumb-item active" aria-current="page">Approvisionnement en carburant</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-gas-pump me-2"></i>
                        Approvisionnement en carburant -
                        <?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']) ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            <!-- Logo du véhicule -->
                            <?php if (!empty($vehicule['logo_marque_vehicule'])): ?>
                                <img src="uploads/vehicules/logo_marque/<?= htmlspecialchars($vehicule['logo_marque_vehicule']) ?>"
                                    class="img-fluid rounded" alt="Logo <?= htmlspecialchars($vehicule['marque']) ?>"
                                    style="max-height: 100px; max-width: 100%;">
                            <?php else: ?>
                                <div class="bg-light rounded p-3 d-flex align-items-center justify-content-center"
                                    style="height: 100px;">
                                    <i class="fas fa-car fa-3x text-secondary"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <strong><i class="fas fa-id-card me-2"></i>Immatriculation:</strong>
                                <span class="ms-2"><?= htmlspecialchars($vehicule['immatriculation']) ?></span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-map-marker-alt me-2"></i>Zone:</strong>
                                <span
                                    class="ms-2"><?= htmlspecialchars($vehicule['nom_zone'] ?? 'Non définie') ?></span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-road me-2"></i>Kilométrage actuel:</strong>
                                <span class="ms-2"><?= number_format($vehicule['kilometrage_actuel'], 0, ',', ' ') ?>
                                    km</span>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <strong><i class="fas fa-gas-pump me-2"></i>Type de carburant:</strong>
                                <span class="ms-2"><?= htmlspecialchars($vehicule['type_carburant']) ?></span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-users me-2"></i>Capacité:</strong>
                                <span class="ms-2"><?= htmlspecialchars($vehicule['capacite_passagers']) ?>
                                    places</span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-car me-2"></i>Type de véhicule:</strong>
                                <span class="ms-2"><?= htmlspecialchars(ucfirst($vehicule['type_vehicule'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section d'alertes améliorée -->
    <div id="alerts-container">
        <?php if (isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
            // Décommentez cette ligne pour voir si l'alerte est traitée
            // echo "<!-- Alerte success traitée: " . $_SESSION['success'] . " -->";
            unset($_SESSION['success']);
            ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
            // Décommentez cette ligne pour voir si l'alerte est traitée
            // echo "<!-- Alerte error traitée: " . $_SESSION['error'] . " -->";
            unset($_SESSION['error']);
            ?>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Formulaire d'approvisionnement -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Nouvel approvisionnement</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST" id="approForm">
                        <input type="hidden" name="id_vehicule" value="<?= $id_vehicule ?>">

                        <div class="mb-3">
                            <label for="date_approvisionnement" class="form-label">Date d'approvisionnement</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="datetime-local" class="form-control" id="date_approvisionnement"
                                    name="date_approvisionnement" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="id_chauffeur" class="form-label">Chauffeur</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <select class="form-select" id="id_chauffeur" name="id_chauffeur">
                                    <option value="">-- Sélectionner un chauffeur --</option>
                                    <?php foreach ($chauffeurs as $chauffeur): ?>
                                        <option value="<?= $chauffeur['id_chauffeur'] ?>">
                                            <?= htmlspecialchars($chauffeur['nom'] . ' ' . $chauffeur['prenoms']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Cette partie remplace la section des champs quantité et prix du formulaire -->
                        <div class="mb-3">
                            <label for="calcul_mode" class="form-label">Mode de calcul</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                                <select class="form-select" id="calcul_mode" name="calcul_mode">
                                    <option value="cout-quantite">Saisir le coût → Calculer la quantité</option>
                                    <!-- <option value="quantite-prix">Saisir la quantité → Calculer le coût</option> -->
                                </select>
                            </div>
                        </div>

                        <!-- Coût total avec formatage amélioré -->
                        <div class="mb-3" id="cout-container">
                            <label for="cout_total" class="form-label">Coût total</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                <input type="number" step="1" min="100" class="form-control" id="cout_total"
                                    name="cout_total" inputmode="numeric" pattern="[0-9]*">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            <div class="form-text calculated-field-text d-none">Valeur calculée automatiquement</div>
                            <div class="form-text">Saisissez un montant entier</div>
                        </div>

                        <div class="mb-3" id="quantite-container">
                            <label for="quantite_litres" class="form-label">Quantité (Litres)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tint"></i></span>
                                <input type="number" step="0.01" min="0.1" class="form-control" id="quantite_litres"
                                    name="quantite_litres" required>
                                <span class="input-group-text">L</span>
                            </div>
                            <div class="form-text calculated-field-text d-none">Valeur calculée automatiquement</div>
                        </div>

                        <!-- Prix unitaire en lecture seule -->
                        <div class="mb-3">
                            <label for="prix_unitaire" class="form-label">Prix unitaire
                                (<?= $vehicule['type_carburant'] ?>)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-money-bill"></i></span>
                                <input type="number" step="0.01" min="1" class="form-control bg-light"
                                    id="prix_unitaire" name="prix_unitaire" readonly>
                                <span class="input-group-text">FCFA/L</span>
                            </div>
                            <div class="form-text">Prix fixé selon les tarifs nationaux en vigueur</div>
                        </div>

                        <!-- Type de carburant en lecture seule -->
                        <div class="mb-3">
                            <label for="type_carburant" class="form-label">Type de carburant</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-gas-pump"></i></span>
                                <select class="form-select bg-light" id="type_carburant" name="type_carburant" required
                                    disabled>
                                    <option value="essence" <?= $vehicule['type_carburant'] == 'Super' ? 'selected' : '' ?>>Super</option>
                                    <option value="diesel" <?= $vehicule['type_carburant'] == 'Gasoil' ? 'selected' : '' ?>>Gasoil</option>
                                    <option value="hybride" <?= $vehicule['type_carburant'] == 'Essence' ? 'selected' : '' ?>>Essence</option>
                                </select>
                                <!-- Champ caché pour soumettre la valeur même si le select est désactivé -->
                                <input type="hidden" name="type_carburant"
                                    value="<?= $vehicule['type_carburant'] == 'Super' ? 'essence' : ($vehicule['type_carburant'] == 'Gasoil' ? 'diesel' : 'hybride') ?>">
                            </div>
                            <div class="form-text">Défini par les caractéristiques du véhicule</div>
                        </div>

                        <!-- Prix total (vérifié) avec formatage amélioré -->
                        <div class="mb-3">
                            <label for="prix_total" class="form-label">Prix total (vérifié)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                <input type="text" class="form-control bg-light" id="prix_total" readonly>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="kilometrage" class="form-label">Kilométrage actuel</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-road"></i></span>
                                <input type="number" min="<?= $vehicule['kilometrage_actuel'] ?>" class="form-control"
                                    id="kilometrage" name="kilometrage" value="<?= $vehicule['kilometrage_actuel'] ?>"
                                    required>
                                <span class="input-group-text">km</span>
                            </div>
                            <div class="form-text">
                                Le kilométrage ne peut pas être inférieur à
                                <?= number_format($vehicule['kilometrage_actuel'], 0, ',', ' ') ?> km.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="station_service" class="form-label">Station-service</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" class="form-control" id="station_service" name="station_service">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Enregistrer l'approvisionnement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistiques et historique -->
        <div class="col-md-7">
            <!-- Statistiques de consommation -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Statistiques de consommation</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Consommation moyenne</h6>
                                    <h3 class="mb-0">
                                        <?= number_format($statistiques['consommation_moyenne'], 2, ',', ' ') ?> L/100km
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Coût moyen par km</h6>
                                    <h3 class="mb-0"><?= number_format($statistiques['cout_moyen_km'], 2, ',', ' ') ?>
                                        FCFA/km</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Distance parcourue</h6>
                                    <h4 class="mb-0"><?= number_format($statistiques['distance_totale'], 0, ',', ' ') ?>
                                        km</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Carburant total</h6>
                                    <h4 class="mb-0"><?= number_format($statistiques['carburant_total'], 2, ',', ' ') ?>
                                        L</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Coût total</h6>
                                    <h4 class="mb-0"><?= number_format($statistiques['cout_total'], 0, ',', ' ') ?> FCFA
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des approvisionnements -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des approvisionnements</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($historique)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Aucun approvisionnement enregistré pour ce véhicule.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="historyTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Chauffeur</th>
                                        <th>Quantité</th>
                                        <th>Prix</th>
                                        <th>Kilométrage</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historique as $appro): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($appro['date_approvisionnement'])) ?></td>
                                            <td>
                                                <?= $appro['nom'] ? htmlspecialchars($appro['nom'] . ' ' . $appro['prenoms']) : '<em>Non spécifié</em>' ?>
                                            </td>
                                            <td><?= number_format($appro['quantite_litres'], 2, ',', ' ') ?> L</td>
                                            <td><?= number_format($appro['prix_total'], 0, ',', ' ') ?> FCFA</td>
                                            <td><?= number_format($appro['kilometrage'], 0, ',', ' ') ?> km</td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-details"
                                                    data-id="<?= $appro['id_approvisionnement'] ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détails -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailsModalLabel">Détails de l'approvisionnement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Inclure le fichier JavaScript externe -->
<script src="assets/js/vehicules/approvisionnement_carburant.js"></script>

<script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(function () {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>

<?php
// Inclure le footer
include_once("includes" . DIRECTORY_SEPARATOR . "footer.php");
?>