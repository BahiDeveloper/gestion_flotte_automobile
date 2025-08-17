<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier l'authentification
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: auth/views/login.php');
    exit;
}

// Récupérer toutes les zones
try {
    $stmt = $pdo->query("SELECT * FROM zone_vehicules ORDER BY nom_zone ASC");
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des zones: " . $e->getMessage();
    $zones = [];
}

// Récupérer les statistiques des véhicules par zone
try {
    $stmt = $pdo->query("
        SELECT z.id, z.nom_zone, 
            COUNT(v.id_vehicule) as total_vehicules,
            SUM(CASE WHEN v.statut = 'disponible' THEN 1 ELSE 0 END) as vehicules_disponibles,
            SUM(CASE WHEN v.statut = 'en_course' THEN 1 ELSE 0 END) as vehicules_en_course,
            SUM(CASE WHEN v.statut = 'maintenance' THEN 1 ELSE 0 END) as vehicules_maintenance,
            SUM(CASE WHEN v.statut = 'hors_service' THEN 1 ELSE 0 END) as vehicules_hors_service
        FROM zone_vehicules z
        LEFT JOIN vehicules v ON z.id = v.id_zone
        GROUP BY z.id, z.nom_zone
    ");
    $stats_zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transformer le tableau pour faciliter l'accès aux données
    $zone_stats = [];
    foreach ($stats_zones as $stat) {
        $zone_stats[$stat['id']] = $stat;
    }
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
    $zone_stats = [];
}

// Inclure le header
include_once("includes" . DIRECTORY_SEPARATOR . "header.php");

// Vérifier les permissions d'accès
if (!isset($roleAccess)) {
    require_once 'includes/RoleAccess.php';
    $roleAccess = new RoleAccess($_SESSION['role']);
}
?>

<div class="container-fluid py-4">
    <h1 class="text-center mb-4">
        <i class="fas fa-map-marker-alt me-2"></i>Gestion des Zones de Véhicules
    </h1>

    <!-- Onglets de navigation -->
    <ul class="nav nav-tabs mb-4" id="zoneTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">
                <i class="fas fa-list me-2"></i>Liste des Zones
            </button>
        </li>
        <?php if ($roleAccess->hasPermission('form')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab">
                <i class="fas fa-plus me-2"></i>Ajouter une Zone
            </button>
        </li>
        <?php endif; ?>
        <?php if ($roleAccess->hasPermission('historique')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab">
                <i class="fas fa-chart-pie me-2"></i>Statistiques
            </button>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Contenu des onglets -->
    <div class="tab-content" id="zoneTabsContent">
        <!-- Liste des zones -->
        <div class="tab-pane fade show active" id="list" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Liste des Zones</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" id="btnExportZones">
                                <i class="fas fa-file-export me-1"></i>Exporter
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnPrintZones">
                                <i class="fas fa-print me-1"></i>Imprimer
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="zonesTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom de la Zone</th>
                                    <th>Description</th>
                                    <th>Véhicules</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($zones as $zone): ?>
                                <tr>
                                    <td><?= htmlspecialchars($zone['id']) ?></td>
                                    <td><?= htmlspecialchars($zone['nom_zone']) ?></td>
                                    <td><?= htmlspecialchars($zone['description'] ?? 'Non spécifié') ?></td>
                                    <td>
                                        <?php 
                                        $total = isset($zone_stats[$zone['id']]) ? $zone_stats[$zone['id']]['total_vehicules'] : 0;
                                        echo $total;
                                        ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($zone['created_at'])) ?></td>
                                    <td>
                                        <?php if ($roleAccess->hasPermission('tracking')): ?>
                                        <button class="btn btn-sm btn-info view-zone" data-id="<?= $zone['id'] ?>" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
                                        <button class="btn btn-sm btn-warning edit-zone" data-id="<?= $zone['id'] ?>" 
                                                data-name="<?= htmlspecialchars($zone['nom_zone']) ?>"
                                                data-description="<?= htmlspecialchars($zone['description'] ?? '') ?>" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($roleAccess->hasPermission('deleteHistorique')): ?>
                                        <button class="btn btn-sm btn-danger delete-zone" data-id="<?= $zone['id'] ?>" 
                                                data-name="<?= htmlspecialchars($zone['nom_zone']) ?>" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

        <!-- Ajouter une zone -->
        <?php if ($roleAccess->hasPermission('form')): ?>
        <div class="tab-pane fade" id="add" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Ajouter une Zone</h5>
                </div>
                <div class="card-body">
                    <form id="addZoneForm" action="actions/zones/add_zone.php" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom_zone" class="form-label">Nom de la Zone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom_zone" name="nom_zone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <?php if ($roleAccess->hasPermission('historique')): ?>
        <div class="tab-pane fade" id="stats" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistiques des Zones</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="zoneVehicleChart" width="400" height="300"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="zoneStatusChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Zone</th>
                                    <th>Total Véhicules</th>
                                    <th>Disponibles</th>
                                    <th>En Course</th>
                                    <th>Maintenance</th>
                                    <th>Hors Service</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats_zones as $stat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($stat['nom_zone']) ?></td>
                                    <td><?= $stat['total_vehicules'] ?></td>
                                    <td>
                                        <span class="badge bg-success"><?= $stat['vehicules_disponibles'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"><?= $stat['vehicules_en_course'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $stat['vehicules_maintenance'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger"><?= $stat['vehicules_hors_service'] ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour modifier une zone -->
<div class="modal fade" id="editZoneModal" tabindex="-1" aria-labelledby="editZoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editZoneModalLabel">Modifier une Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editZoneForm" action="actions/zones/edit_zone.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_zone_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nom_zone" class="form-label">Nom de la Zone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nom_zone" name="nom_zone" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour voir les détails d'une zone -->
<div class="modal fade" id="viewZoneModal" tabindex="-1" aria-labelledby="viewZoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewZoneModalLabel">Détails de la Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Informations de la zone</h6>
                        <p><strong>Nom:</strong> <span id="view_nom_zone"></span></p>
                        <p><strong>Description:</strong> <span id="view_description"></span></p>
                        <p><strong>Créée le:</strong> <span id="view_created_at"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Statistiques</h6>
                        <p><strong>Total des véhicules:</strong> <span id="view_total_vehicules"></span></p>
                        <p><strong>Véhicules disponibles:</strong> <span id="view_vehicules_disponibles"></span></p>
                        <p><strong>Véhicules en course:</strong> <span id="view_vehicules_en_course"></span></p>
                    </div>
                </div>
                <hr>
                <h6>Véhicules dans cette zone</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="zoneVehiclesTable">
                        <thead>
                            <tr>
                                <th>Immatriculation</th>
                                <th>Marque/Modèle</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="zone_vehicles_list">
                            <!-- Les véhicules seront chargés dynamiquement ici -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Script pour la gestion des zones -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de DataTables
    const zonesTable = $('#zonesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copier',
                className: 'btn btn-sm btn-outline-primary'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-sm btn-outline-primary'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-outline-primary'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-outline-primary'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimer',
                className: 'btn btn-sm btn-outline-primary'
            }
        ]
    });

    // Export et impression
    $('#btnExportZones').click(function() {
        zonesTable.button('.buttons-excel').trigger();
    });

    $('#btnPrintZones').click(function() {
        zonesTable.button('.buttons-print').trigger();
    });

    // Modifier une zone
    $('.edit-zone').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');
        
        $('#edit_zone_id').val(id);
        $('#edit_nom_zone').val(name);
        $('#edit_description').val(description);
        
        const modal = new bootstrap.Modal(document.getElementById('editZoneModal'));
        modal.show();
    });

    // Voir les détails d'une zone
    $('.view-zone').click(function() {
        const zoneId = $(this).data('id');
        
        // Afficher le spinner de chargement
        $('#zone_vehicles_list').html('<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></td></tr>');
        
        // Charger les détails de la zone
        fetch(`actions/zones/get_zone_details.php?id=${zoneId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remplir les détails de la zone
                    $('#view_nom_zone').text(data.zone.nom_zone);
                    $('#view_description').text(data.zone.description || 'Non spécifié');
                    $('#view_created_at').text(new Date(data.zone.created_at).toLocaleString());
                    
                    // Remplir les statistiques
                    $('#view_total_vehicules').text(data.stats.total_vehicules);
                    $('#view_vehicules_disponibles').text(data.stats.vehicules_disponibles);
                    $('#view_vehicules_en_course').text(data.stats.vehicules_en_course);
                    
                    // Remplir la liste des véhicules
                    let vehiclesList = '';
                    if (data.vehicles.length > 0) {
                        data.vehicles.forEach(vehicle => {
                            let statusClass = '';
                            switch (vehicle.statut) {
                                case 'disponible': statusClass = 'bg-success'; break;
                                case 'en_course': statusClass = 'bg-warning'; break;
                                case 'maintenance': statusClass = 'bg-info'; break;
                                case 'hors_service': statusClass = 'bg-danger'; break;
                            }
                            
                            vehiclesList += `
                                <tr>
                                    <td>${vehicle.immatriculation}</td>
                                    <td>${vehicle.marque} ${vehicle.modele}</td>
                                    <td>${vehicle.type_vehicule}</td>
                                    <td><span class="badge ${statusClass}">${vehicle.statut}</span></td>
                                    <td>
                                        <a href="details_vehicule.php?id=${vehicle.id_vehicule}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        vehiclesList = '<tr><td colspan="5" class="text-center">Aucun véhicule dans cette zone</td></tr>';
                    }
                    
                    $('#zone_vehicles_list').html(vehiclesList);
                } else {
                    alert('Erreur lors du chargement des détails de la zone');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                $('#zone_vehicles_list').html('<tr><td colspan="5" class="text-center text-danger">Erreur lors du chargement des données</td></tr>');
            });
        
        const modal = new bootstrap.Modal(document.getElementById('viewZoneModal'));
        modal.show();
    });

    // Supprimer une zone
    $('.delete-zone').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        Swal.fire({
            title: 'Êtes-vous sûr?',
            text: `Voulez-vous vraiment supprimer la zone "${name}"? Cette action est irréversible.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `actions/zones/delete_zone.php?id=${id}`;
            }
        });
    });

    // Graphiques statistiques si l'onglet statistiques existe
    if (document.getElementById('zoneVehicleChart')) {
        // Données pour les graphiques
        const zoneNames = <?= json_encode(array_column($stats_zones, 'nom_zone')) ?>;
        const totalVehicles = <?= json_encode(array_column($stats_zones, 'total_vehicules')) ?>;
        const availableVehicles = <?= json_encode(array_column($stats_zones, 'vehicules_disponibles')) ?>;
        const busyVehicles = <?= json_encode(array_column($stats_zones, 'vehicules_en_course')) ?>;
        const maintenanceVehicles = <?= json_encode(array_column($stats_zones, 'vehicules_maintenance')) ?>;
        const outOfServiceVehicles = <?= json_encode(array_column($stats_zones, 'vehicules_hors_service')) ?>;

        // Graphique de répartition des véhicules par zone
        const zoneVehicleCtx = document.getElementById('zoneVehicleChart').getContext('2d');
        new Chart(zoneVehicleCtx, {
            type: 'bar',
            data: {
                labels: zoneNames,
                datasets: [{
                    label: 'Nombre de véhicules',
                    data: totalVehicles,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Répartition des véhicules par zone'
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Graphique de statut des véhicules par zone
        const zoneStatusCtx = document.getElementById('zoneStatusChart').getContext('2d');
        new Chart(zoneStatusCtx, {
            type: 'pie',
            data: {
                labels: ['Disponibles', 'En Course', 'Maintenance', 'Hors Service'],
                datasets: [{
                    data: [
                        availableVehicles.reduce((a, b) => a + b, 0),
                        busyVehicles.reduce((a, b) => a + b, 0),
                        maintenanceVehicles.reduce((a, b) => a + b, 0),
                        outOfServiceVehicles.reduce((a, b) => a + b, 0)
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)', // Disponibles
                        'rgba(255, 206, 86, 0.7)', // En Course
                        'rgba(54, 162, 235, 0.7)', // Maintenance
                        'rgba(255, 99, 132, 0.7)'  // Hors Service
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Répartition des statuts de véhicules'
                    },
                    legend: {
                        display: true,
                        position: 'right'
                    }
                }
            }
        });
    }
});

// Afficher les messages d'alerte
<?php if (isset($_SESSION['success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Succès',
        text: '<?= addslashes($_SESSION['success']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Erreur',
        text: '<?= addslashes($_SESSION['error']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
</script>

<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php"); ?>