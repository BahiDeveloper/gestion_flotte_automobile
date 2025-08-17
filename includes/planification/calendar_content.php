<!-- start calendar.php -->
<div class="container-fluid mt-4">
    <!-- Titre de la page -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-calendar"></i> Calendrier dynamique</h2>
        </div>
    </div>

    <div class="row">
        <!-- Filtres latéraux -->
        <div class="col-md-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Filtres</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        <!-- Type de véhicule -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type de véhicule</label>
                            <div class="form-check">
                                <input class="form-check-input filter-statut" type="checkbox" value="reserve"
                                    id="filterReserve" checked>
                                <label class="form-check-label" for="filterReserve">
                                    <span class="badge bg-warning">Réservé</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input filter-statut" type="checkbox" value="maintenance"
                                    id="filterMaintenance" checked>
                                <label class="form-check-label" for="filterMaintenance">
                                    <span class="badge bg-danger">En maintenance</span>
                                </label>
                            </div>
                        </div>

                        <!-- Bouton d'application des filtres -->
                        <button type="button" class="btn btn-primary w-100" id="applyFilters">
                            <i class="fas fa-filter"></i> Appliquer les filtres
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Calendrier principal -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Calendrier des disponibilités</h5>
                    <div>
                        <button class="btn btn-light btn-sm me-2" id="btnVueVehicules">
                            <i class="fas fa-car"></i> Vue Véhicules
                        </button>
                        <button class="btn btn-light btn-sm me-2" id="btnVueChauffeurs">
                            <i class="fas fa-user"></i> Vue Chauffeurs
                        </button>
                        <button class="btn btn-success btn-sm" id="btnNouvelleReservation">
                            <i class="fas fa-plus"></i> Nouvelle réservation
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Modal de détails de réservation -->
<div class="modal fade" id="reservationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la réservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations générales</h6>
                        <ul class="list-unstyled">
                            <li><strong>Date de départ:</strong> <span id="modalDateDepart"></span></li>
                            <li><strong>Durée estimée:</strong> <span id="modalDuree"></span></li>
                            <li><strong>Statut:</strong> <span id="modalStatut"></span></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Détails du trajet</h6>
                        <ul class="list-unstyled">
                            <li><strong>Départ:</strong> <span id="modalLieuDepart"></span></li>
                            <li><strong>Arrivée:</strong> <span id="modalLieuArrivee"></span></li>
                            <li><strong>Passagers:</strong> <span id="modalPassagers"></span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="btnModifierReservation">Modifier</button>
            </div>
        </div>
    </div>
</div>
<!-- start calendar.php -->