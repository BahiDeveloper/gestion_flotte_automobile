<?php
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

include_once("request/request_zones.php");

// Inclure les messages d'alerte
include_once("alerts/alert_zones.php");

?>
<!-- Inclure le header -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>

<h2 class="text-center my-4"><i class="fas fa-file-alt"></i> Gestion des zones</h2>

<!-- Onglets de navigation -->
<ul class="nav nav-tabs" id="zoneTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ajouter-tab" data-bs-toggle="tab" data-bs-target="#ajouter" type="button"
            role="tab" aria-controls="ajouter" aria-selected="true">
            <i class="fas fa-plus"></i> Ajouter une zone
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="liste-tab" data-bs-toggle="tab" data-bs-target="#liste" type="button" role="tab"
            aria-controls="liste" aria-selected="false">
            <i class="fas fa-list"></i> Liste des zones
        </button>
    </li>
</ul>

<div class="tab-content mt-3" id="zoneTabsContent">
    <!-- Onglet : Ajouter une zone -->
    <div class="tab-pane fade show active" id="ajouter" role="tabpanel" aria-labelledby="ajouter-tab">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-plus-circle"></i> Ajouter une zone
            </div>
            <div class="card-body">
                <form method="POST" action="actions/add_zone.php">

                    <div class="mb-3">
                        <label for="nom_zone" class="form-label">Nom de la zone</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" id="nom_zone" name="nom_zone" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="id_vehicule" class="form-label">Véhicule</label>
                        <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-car"></i></i></span>
                            <select class="form-select" id="id_vehicule" name="id_vehicule" multiple size="4"
                                aria-label="multiple select example">
                                <option selected>Choisir un véhicule</option>
                                <?php
                                $sql_vehicules = "SELECT id, marque, modele FROM vehicules";
                                $stmt_vehicules = $pdo->query($sql_vehicules);
                                foreach ($stmt_vehicules as $vehicule) {
                                    echo "<option value='{$vehicule['id']}'>{$vehicule['marque']} {$vehicule['modele']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Ajouter une zone</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Onglet : Liste des zones -->
    <div class="tab-pane fade" id="liste" role="tabpanel" aria-labelledby="liste-tab">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-list"></i> Liste des zones
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="zoneTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom de zone</th>
                                <th>Véhicule attribué</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($zones as $zone): ?>
                                <tr>
                                    <td><?= htmlspecialchars($zone['nom_zone']) ?></td>
                                    <td><?= htmlspecialchars($zone['marque']) ?>
                                        <?= htmlspecialchars($zone['modele']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($zone['created_at']) ?></td>
                                    <td>
                                        <a href="actions/delete_zone.php?id=<?= $zone['id'] ?>"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="actions/delete_zone.php?id=<?= $zone['id'] ?>"
                                            class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
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


<!-- Initialiser DataTable dans vehicule_details.php  -->
<script src="assets/js/dataTable_for_doc_car.js"></script>
<script src="assets/js/gestion_zones.js"></script>

<!-- Inclure le footer -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>