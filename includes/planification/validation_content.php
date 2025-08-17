<!--start Validation des Demandes  -->
<div class="container mt-4">
    <!-- En-tête de page -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-tasks"></i> Validation des Demandes</h2>
                <p class="text-muted">Gestion et validation des demandes de déplacement</p>
            </div>
            <div>
                <button class="btn btn-primary" id="btnRefresh">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
            </div>
        </div>
    </div>

    <!-- Filtres de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select class="form-select" id="filterStatut">
                        <option value="">Tous</option>
                        <option value="en_attente" selected>En attente</option>
                        <option value="valide">Validé</option>
                        <option value="refuse">Refusé</option>
                        <option value="modifie">Modifié</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Période</label>
                    <select class="form-select" id="filterPeriode">
                        <option value="today">Aujourd'hui</option>
                        <option value="tomorrow">Demain</option>
                        <option value="week" selected>Cette semaine</option>
                        <option value="month">Ce mois</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priorité</label>
                    <select class="form-select" id="filterPriorite">
                        <option value="">Toutes</option>
                        <option value="4">Critique</option>
                        <option value="3">Haute</option>
                        <option value="2">Moyenne</option>
                        <option value="1">Normale</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type de véhicule</label>
                    <select class="form-select" id="filterTypeVehicule">
                        <option value="">Tous</option>
                        <option value="utilitaire">Utilitaire</option>
                        <option value="berline">Berline</option>
                        <option value="camion">Camion</option>
                        <option value="bus">Bus</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des demandes -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="demandesTable">
                    <thead>
                        <tr>
                            <th>Date demande</th>
                            <th>Demandeur</th>
                            <th>Véhicule</th>
                            <th>Départ</th>
                            <th>Durée</th>
                            <th>Priorité</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rempli via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de validation détaillée -->
<div class="modal fade" id="validationDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Validation de la demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Détails de la demande originale -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Demande originale</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Demandeur:</strong>
                                        <p id="modalDemandeur"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Service:</strong>
                                        <p id="modalService"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Date départ:</strong>
                                        <p id="modalDateDepart"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Durée estimée:</strong>
                                        <p id="modalDuree"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Véhicule demandé:</strong>
                                        <p id="modalVehicule"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Chauffeur:</strong>
                                        <p id="modalChauffeur"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <strong>Motif:</strong>
                                        <p id="modalMotif"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Passagers:</strong>
                                        <p id="modalPassagers"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Chargement:</strong>
                                        <p id="modalChargement"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de validation/modification -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Validation et modifications</h6>
                            </div>
                            <div class="card-body">
                                <form id="validationForm">
                                    <input type="hidden" id="demandeId" name="demandeId">

                                    <!-- Proposition de véhicule -->
                                    <div class="mb-3">
                                        <label class="form-label">Véhicule proposé</label>
                                        <select class="form-select" id="vehiculePropose" name="vehiculeId">
                                            <!-- Options chargées dynamiquement -->
                                        </select>
                                        <div class="form-text" id="vehiculeDispoInfo"></div>
                                    </div>

                                    <!-- Proposition de chauffeur -->
                                    <div class="mb-3">
                                        <label class="form-label">Chauffeur proposé</label>
                                        <select class="form-select" id="chauffeurPropose" name="chauffeurId">
                                            <!-- Options chargées dynamiquement -->
                                        </select>
                                        <div class="form-text" id="chauffeurDispoInfo"></div>
                                    </div>

                                    <!-- Modification des horaires -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nouvelle date/heure de départ</label>
                                            <input type="datetime-local" class="form-control" id="dateProposee"
                                                name="dateProposee">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nouvelle durée (heures)</label>
                                            <input type="number" class="form-control" id="dureeProposee"
                                                name="dureeProposee" min="1">
                                        </div>
                                    </div>

                                    <!-- Commentaire de validation -->
                                    <div class="mb-3">
                                        <label class="form-label">Commentaire</label>
                                        <textarea class="form-control" id="commentaireValidation" name="commentaire"
                                            rows="3"
                                            placeholder="Ajoutez un commentaire pour le demandeur..."></textarea>
                                    </div>

                                    <!-- Notification -->
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notifierDemandeur"
                                                name="notifier" checked>
                                            <label class="form-check-label" for="notifierDemandeur">
                                                Notifier le demandeur par email
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historique des modifications -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Historique des modifications</h6>
                            </div>
                            <div class="card-body">
                                <div id="historiqueModifications">
                                    <!-- Rempli dynamiquement -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-danger" id="btnRefuser">
                    <i class="fas fa-times"></i> Refuser
                </button>
                <button type="button" class="btn btn-warning" id="btnModifier">
                    <i class="fas fa-edit"></i> Proposer modifications
                </button>
                <button type="button" class="btn btn-success" id="btnValider">
                    <i class="fas fa-check"></i> Valider
                </button>
            </div>
        </div>
    </div>
</div>

<!--end validation_demandes.php -->