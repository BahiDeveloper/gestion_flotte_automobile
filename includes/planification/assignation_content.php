<!--start assignations_multiples.php -->
<div class="container mt-4">
    <!-- En-tête de page -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-layer-group"></i> Assignations Multiples</h2>
        </div>
    </div>

    <div class="row">
        <!-- Liste des réservations en attente -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Réservations en attente</h5>
                    <button class="btn btn-light btn-sm" id="refreshList">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="reservationsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Date/Heure</th>
                                    <th>Trajet</th>
                                    <th>Passagers</th>
                                    <th>Durée est.</th>
                                    <th>Distance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rempli dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panneau d'assignation -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Assignation Groupée</h5>
                </div>
                <div class="card-body">
                    <form id="assignationForm">
                        <!-- Sélection du véhicule -->
                        <div class="mb-3">
                            <label class="form-label">Véhicule</label>
                            <select class="form-select" id="vehiculeSelect" required>
                                <option value="">Sélectionnez un véhicule</option>
                            </select>
                            <!-- Informations du véhicule -->
                            <div id="vehiculeInfo" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <small>
                                        <strong>Capacité:</strong> <span id="capaciteVehicule"></span> passagers<br>
                                        <strong>Type:</strong> <span id="typeVehicule"></span><br>
                                        <strong>État:</strong> <span id="etatVehicule"></span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Sélection du chauffeur -->
                        <div class="mb-3">
                            <label class="form-label">Chauffeur</label>
                            <select class="form-select" id="chauffeurSelect" required>
                                <option value="">Sélectionnez un chauffeur</option>
                            </select>
                            <!-- Informations du chauffeur -->
                            <div id="chauffeurInfo" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <small>
                                        <strong>Spécialisation:</strong> <span id="specialisationChauffeur"></span><br>
                                        <strong>Expérience:</strong> <span id="experienceChauffeur"></span><br>
                                        <strong>Statut:</strong> <span id="statutChauffeur"></span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Résumé de l'assignation -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Résumé de l'assignation</h6>
                                <div id="resumeAssignation">
                                    <p class="mb-2"><strong>Courses sélectionnées:</strong> <span
                                            id="nbCourses">0</span></p>
                                    <p class="mb-2"><strong>Total passagers:</strong> <span id="totalPassagers">0</span>
                                    </p>
                                    <p class="mb-2"><strong>Durée totale estimée:</strong> <span
                                            id="dureeTotale">0h</span></p>
                                    <p class="mb-0"><strong>Distance totale:</strong> <span id="distanceTotale">0
                                            km</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Alertes et validations -->
                        <div id="alertesContainer"></div>

                        <!-- Boutons d'action -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" id="btnVerifier">
                                <i class="fas fa-check-circle"></i> Vérifier la faisabilité
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnAssigner" disabled>
                                <i class="fas fa-save"></i> Confirmer l'assignation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation d'assignation multiple</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous vraiment assigner ces courses au même véhicule ?</p>
                <div id="recapAssignation"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnConfirmerAssignation">Confirmer</button>
            </div>
        </div>
    </div>
</div>
<!-- end assignations_multiples.php -->