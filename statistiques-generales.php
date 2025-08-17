<?php
// Vérifier les permissions
if (in_array($_SESSION['role'], ['administrateur', 'gestionnaire'])):
?>
<div class="row" id="statistiques-generales">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Tableau de Bord Général
                </h5>
                <div class="d-flex align-items-center">
                    <span id="last-update" class="me-3 small text-white-50">
                        <i class="fas fa-sync me-1"></i>Mise à jour : --/--/----
                    </span>
                    <button class="btn btn-sm btn-light refresh-stats">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Carte Véhicules -->
                    <div class="col-md-3">
                        <div class="card border-start border-primary border-3 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Véhicules</h6>
                                        <h3 id="total-vehicules" class="text-primary">0</h3>
                                    </div>
                                    <div class="text-end">
                                        <div class="d-flex flex-column">
                                            <small class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <span id="vehicules-disponibles">0</span> Disponibles
                                            </small>
                                            <small class="text-warning">
                                                <i class="fas fa-wrench me-1"></i>
                                                <span id="vehicules-maintenance">0</span> Maintenance
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="vehicules-progress" class="progress-bar bg-primary" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte Chauffeurs -->
                    <div class="col-md-3">
                        <div class="card border-start border-success border-3 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Chauffeurs</h6>
                                        <h3 id="total-chauffeurs" class="text-success">0</h3>
                                    </div>
                                    <div class="text-end">
                                        <div class="d-flex flex-column">
                                            <small class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <span id="chauffeurs-disponibles">0</span> Disponibles
                                            </small>
                                            <small class="text-warning">
                                                <i class="fas fa-road me-1"></i>
                                                <span id="chauffeurs-course">0</span> En Course
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="chauffeurs-progress" class="progress-bar bg-success" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte Documents -->
                    <div class="col-md-3">
                        <div class="card border-start border-warning border-3 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Documents</h6>
                                        <h3 id="total-documents" class="text-warning">0</h3>
                                    </div>
                                    <div class="text-end">
                                        <div class="d-flex flex-column">
                                            <small class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <span id="documents-valides">0</span> Valides
                                            </small>
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <span id="documents-expiration">0</span> À Renouveler
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="documents-progress" class="progress-bar bg-warning" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte Finances -->
                    <div class="col-md-3">
                        <div class="card border-start border-info border-3 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Finances</h6>
                                        <h3 id="total-finances" class="text-info">0 FCFA</h3>
                                    </div>
                                    <div class="text-end">
                                        <div class="d-flex flex-column">
                                            <small class="text-primary">
                                                <i class="fas fa-gas-pump me-1"></i>
                                                <span id="cout-carburant">0</span> FCFA Carburant
                                            </small>
                                            <small class="text-success">
                                                <i class="fas fa-tools me-1"></i>
                                                <span id="cout-maintenance">0</span> FCFA Maintenance
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="finances-progress" class="progress-bar bg-info" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statsContainer = document.getElementById('statistiques-generales');
    const lastUpdateEl = document.getElementById('last-update');
    const refreshButton = statsContainer.querySelector('.refresh-stats');

    function updateStatistics() {
        fetch('api/statistiques-generales.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mise à jour des statistiques de véhicules
                    document.getElementById('total-vehicules').textContent = data.vehicules.total;
                    document.getElementById('vehicules-disponibles').textContent = data.vehicules.disponibles;
                    document.getElementById('vehicules-maintenance').textContent = data.vehicules.maintenance;
                    document.getElementById('vehicules-progress').style.width = `${(data.vehicules.disponibles / data.vehicules.total * 100)}%`;

                    // Mise à jour des statistiques de chauffeurs
                    document.getElementById('total-chauffeurs').textContent = data.chauffeurs.total;
                    document.getElementById('chauffeurs-disponibles').textContent = data.chauffeurs.disponibles;
                    document.getElementById('chauffeurs-course').textContent = data.chauffeurs.en_course;
                    document.getElementById('chauffeurs-progress').style.width = `${(data.chauffeurs.disponibles / data.chauffeurs.total * 100)}%`;

                    // Mise à jour des statistiques de documents
                    document.getElementById('total-documents').textContent = data.documents.total;
                    document.getElementById('documents-valides').textContent = data.documents.valides;
                    document.getElementById('documents-expiration').textContent = data.documents.a_renouveler;
                    document.getElementById('documents-progress').style.width = `${(data.documents.valides / data.documents.total * 100)}%`;

                    // Mise à jour des statistiques financières
                    document.getElementById('total-finances').textContent = `${(data.finances.carburant + data.finances.maintenance).toLocaleString()} FCFA`;
                    document.getElementById('cout-carburant').textContent = data.finances.carburant.toLocaleString();
                    document.getElementById('cout-maintenance').textContent = data.finances.maintenance.toLocaleString();
                    document.getElementById('finances-progress').style.width = `${(data.finances.maintenance / (data.finances.carburant + data.finances.maintenance) * 100)}%`;

                    // Mise à jour de la date
                    lastUpdateEl.innerHTML = `<i class="fas fa-sync me-1"></i>Mise à jour : ${new Date().toLocaleString()}`;
                }
            })
            .catch(error => {
                console.error('Erreur de chargement:', error);
                lastUpdateEl.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-1"></i>Erreur de mise à jour';
            });
    }

    // Charger les statistiques immédiatement
    updateStatistics();

    // Événement de rafraîchissement manuel
    refreshButton.addEventListener('click', updateStatistics);

    // Actualiser automatiquement toutes les 5 minutes
    setInterval(updateStatistics, 5 * 60 * 1000);
});
</script>
<?php endif; ?>