<!--start reservation.php -->
<div class="container mt-4">
    <!-- Titre de la page -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-calendar-plus"></i> Nouvelle Réservation</h2>
        </div>
    </div>

    <!-- Formulaire de réservation -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Détails de la réservation</h5>
                </div>
                <div class="card-body">
                    <form id="reservationForm" method="POST">
                        <!-- Période de réservation -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dateDepart" class="form-label">Date et heure de départ *</label>
                                <input type="datetime-local" class="form-control" id="dateDepart" name="dateDepart"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="dureeEstimee" class="form-label">Durée estimée (en heures) *</label>
                                <input type="number" class="form-control" id="dureeEstimee" name="dureeEstimee" min="1"
                                    required>
                            </div>
                        </div>

                        <!-- Sélection du véhicule -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="typeVehicule" class="form-label">Type de véhicule *</label>
                                <select class="form-select" id="typeVehicule" name="typeVehicule" required>
                                    <option value="">Sélectionnez un type</option>
                                    <option value="utilitaire">Utilitaire</option>
                                    <option value="berline">Berline</option>
                                    <option value="camion">Camion</option>
                                    <option value="bus">Bus</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="vehicule" class="form-label">Véhicule disponible *</label>
                                <select class="form-select" id="vehicule" name="vehicule" required disabled>
                                    <option value="">Sélectionnez d'abord un type</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sélection du chauffeur -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="chauffeur" class="form-label">Chauffeur *</label>
                                <select class="form-select" id="chauffeur" name="chauffeur" required disabled>
                                    <option value="">Sélectionnez d'abord un véhicule</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nbPassagers" class="form-label">Nombre de passagers *</label>
                                <input type="number" class="form-control" id="nbPassagers" name="nbPassagers" min="1"
                                    required>
                            </div>
                        </div>

                        <!-- Détails du trajet -->
                        <div class="mb-3">
                            <label for="typeChargement" class="form-label">Type de chargement *</label>
                            <textarea class="form-control" id="typeChargement" name="typeChargement" rows="3"
                                placeholder="Décrivez la nature et le volume du chargement" required></textarea>
                        </div>

                        <!-- Itinéraire -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="lieuDepart" class="form-label">Lieu de départ *</label>
                                <input type="text" class="form-control" id="lieuDepart" name="lieuDepart" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lieuArrivee" class="form-label">Lieu d'arrivée *</label>
                                <input type="text" class="form-control" id="lieuArrivee" name="lieuArrivee" required>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                <i class="fas fa-arrow-left"></i> Retour
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Soumettre la demande
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panneau d'informations -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <!-- Détails du véhicule sélectionné -->
                    <div id="vehiculeInfo" class="mb-4" style="display: none;">
                        <h6 class="border-bottom pb-2">Détails du véhicule</h6>
                        <ul class="list-unstyled">
                            <li><strong>Modèle:</strong> <span id="modeleVehicule"></span></li>
                            <li><strong>Capacité:</strong> <span id="capaciteVehicule"></span> passagers</li>
                            <li><strong>Kilométrage:</strong> <span id="kilometrageVehicule"></span> km</li>
                        </ul>
                    </div>

                    <!-- Détails du chauffeur sélectionné -->
                    <div id="chauffeurInfo" style="display: none;">
                        <h6 class="border-bottom pb-2">Détails du chauffeur</h6>
                        <ul class="list-unstyled">
                            <li><strong>Nom:</strong> <span id="nomChauffeur"></span></li>
                            <li><strong>Spécialisation:</strong> <span id="specialisationChauffeur"></span></li>
                            <li><strong>Expérience:</strong> <span id="experienceChauffeur"></span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end reservation.php -->