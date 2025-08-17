<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->
<?php
// Au début du fichier, ajoutez ceci après l'inclusion du header
if (!isset($roleAccess)) {
    require_once 'includes/RoleAccess.php';
    $roleAccess = new RoleAccess($_SESSION['role']);
}
?>

<style>
    .tab-content>.tab-pane {
        display: none;
    }

    .tab-content>.tab-pane.show.active {
        display: block;
    }

    body.initializing .tab-content>.tab-pane.active {
        display: block !important;
    }
</style>


<!-- Contenu principal -->
<div class="container my-5">
    <h1 class="text-center mb-4" style="color: #2c3e50; font-weight: 700;">Planification des déplacements</h1>

    <!-- Onglets de navigation -->
    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <!-- Onglet Formulaire -->
        <?php if (in_array($_SESSION['role'], ['utilisateur', 'gestionnaire', 'administrateur'])): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#form" type="button"
                    role="tab" aria-controls="form" aria-selected="true">
                    <i class="fas fa-file-alt me-2"></i>Formulaire
                </button>
            </li>
        <?php endif; ?>

        <!-- Onglet Calendrier -->
        <?php if ($roleAccess->hasPermission('calendar')): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button"
                    role="tab" aria-controls="calendar" aria-selected="false">
                    <i class="fas fa-calendar-alt me-2"></i>Calendrier
                </button>
            </li>
        <?php endif; ?>

        <!-- Onglet Validation -->
        <?php if ($roleAccess->hasPermission('validation')): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="validation-tab" data-bs-toggle="tab" data-bs-target="#validation" type="button"
                    role="tab" aria-controls="validation" aria-selected="false">
                    <i class="fas fa-check-circle me-2"></i>Validation
                </button>
            </li>
        <?php endif; ?>

        <!-- Onglet Suivi -->
        <?php if ($roleAccess->hasPermission('tracking') || $roleAccess->hasPermission('suivi')): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tracking-tab" data-bs-toggle="tab" data-bs-target="#tracking" type="button"
                    role="tab" aria-controls="tracking" aria-selected="false">
                    <i class="fas fa-road me-2"></i>Suivi
                </button>
            </li>
        <?php endif; ?>

        <!-- Onglet Historique -->
        <?php if ($roleAccess->hasPermission('historique')): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="historique-tab" data-bs-toggle="tab" data-bs-target="#historique" type="button"
                    role="tab" aria-controls="historique" aria-selected="false">
                    <i class="fas fa-history me-2"></i>
                    Historique
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Contenu des onglets -->
    <div class="tab-content" id="myTabContent">

        <!-- Formulaire de réservation -->
        <?php if (in_array($_SESSION['role'], ['utilisateur', 'gestionnaire', 'administrateur'])): ?>
            <div class="tab-pane fade show active" id="form" role="tabpanel" aria-labelledby="form-tab">
                <div class="section">
                    <!--start reservation.php -->
                    <div class="container mt-4">
                        <!-- Titre de la page -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h2><i class="fas fa-calendar-plus"></i> Nouvelle Réservation</h2>
                            </div>
                        </div>

                        <!-- Formulaire de réservation -->
                        <form id="reservationForm" method="POST">
                            <!-- Section 1: Informations temporelles -->
                                <div class="card mb-3">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">1. Période, zone et passagers</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="dateDepartPrevue" class="form-label">Date et heure de départ prévue *</label>
                                                <input type="datetime-local" class="form-control" id="dateDepartPrevue" name="dateDepartPrevue" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="dateArriveePrevue" class="form-label">Date et heure d'arrivée prévue *</label>
                                                <input type="datetime-local" class="form-control" id="dateArriveePrevue" name="dateArriveePrevue" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="zoneVehicule" class="form-label">Zone du véhicule *</label>
                                                <select class="form-select" id="zoneVehicule" name="zoneVehicule" required>
                                                    <option value="">Sélectionnez une zone</option>
                                                    <!-- Les zones seront chargées dynamiquement -->
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="typeVehicule" class="form-label">Type de véhicule *</label>
                                                <select class="form-select" id="typeVehicule" name="typeVehicule" required>
                                                    <option value="">Sélectionnez un type</option>
                                                    <option value="utilitaire">Utilitaire</option>
                                                    <option value="camion">Camion</option>
                                                    <option value="bus">Bus</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="nbPassagers" class="form-label">Nombre de passagers *</label>
                                                <input type="number" class="form-control" id="nbPassagers" name="nbPassagers" min="1" required>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="button" id="btnContinuerVehicule" class="btn btn-primary">
                                                <i class="fas fa-arrow-right"></i> Continuer et sélectionner un véhicule
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Sélection du véhicule (affichée après validation de la première section) -->
                                <div class="card mb-3" id="sectionVehicule" style="display: none;">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">2. Sélection du véhicule disponible</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <label for="vehicule" class="form-label">Véhicule disponible *</label>
                                                <select class="form-select" id="vehicule" name="vehicule">
                                                    <option value="">Chargement des véhicules disponibles...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="button" id="btnContinuerTrajet" class="btn btn-primary">
                                                <i class="fas fa-arrow-right"></i> Continuer et définir le trajet
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            <!-- Section 3: Détails du trajet -->
                            <div class="card mb-3" id="sectionTrajet" style="display: none;">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">3. Détails du trajet</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Objet de la demande -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="demandeur" class="form-label">Demandeur *</label>
                                            <input type="text" class="form-control" id="demandeur" name="demandeur"
                                                required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="objetDemande" class="form-label">Objet de la demande *</label>
                                        <textarea class="form-control" id="objetDemande" name="objetDemande" rows="3"
                                            placeholder="Décrivez l'objet de la demande" required></textarea>
                                    </div>

                                    <!-- Itinéraire -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="lieuDepart" class="form-label">Lieu de départ *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                <input type="text" class="form-control" id="lieuDepart" name="lieuDepart"
                                                    placeholder="Saisissez un lieu de départ" autocomplete="off" required>
                                            </div>
                                            <div id="suggestionsDepart" class="list-group position-absolute w-75"
                                                style="z-index: 1000; display: none;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lieuArrivee" class="form-label">Lieu d'arrivée *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-flag-checkered"></i></span>
                                                <input type="text" class="form-control" id="lieuArrivee" name="lieuArrivee"
                                                    placeholder="Saisissez un lieu d'arrivée" autocomplete="off" required>
                                            </div>
                                            <div id="suggestionsArrivee" class="list-group position-absolute w-75"
                                                style="z-index: 1000; display: none;"></div>
                                        </div>
                                    </div>

                                    <!-- Kilométrage et durée (calculés automatiquement) -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="kilometrageEstimee" class="form-label">Kilométrage estimé (en Km)
                                                *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-road"></i></span>
                                                <input type="number" class="form-control" id="kilometrageEstimee"
                                                    name="kilometrageEstimee" readonly required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dureeEstimee" class="form-label">Durée estimée (en heures ou
                                                minutes) *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                                <input type="number" class="form-control" id="dureeEstimee"
                                                    name="dureeEstimee" readonly required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="history.back()">
                                            <i class="fas fa-arrow-left"></i> Retour
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> Soumettre la demande
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                    <!--end reservation.php -->
                </div>
            </div>
        <?php endif; ?>

        <!-- Calendrier de disponibilité -->
        <!-- Calendrier -->
        <div class="tab-pane fade" id="calendar" role="tabpanel" aria-labelledby="calendar-tab">
            <div class="section">
                <div class="container-fluid mt-4">
                    <div class="row">

                        <!-- Filtres latéraux -->
                        <div class="col-md-3">
                            <div class="card sticky-top" style="top: 20px;">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Filtres</h5>
                                </div>
                                <div class="card-body">
                                    <form id="filterForm">
                                        <!-- Statuts de réservation -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Statuts de réservation</label>
                                            <div class="form-check">
                                                <input class="form-check-input filter-statut" type="checkbox"
                                                    value="en_attente" id="filterEnAttente" checked>
                                                <label class="form-check-label" for="filterEnAttente">
                                                    <span class="badge bg-warning">En attente</span>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input filter-statut" type="checkbox"
                                                    value="validee" id="filterValidee" checked>
                                                <label class="form-check-label" for="filterValidee">
                                                    <span class="badge bg-success">Validée</span>
                                                </label>
                                            </div>
                                            <!-- Autres statuts similairement -->
                                        </div>

                                        <!-- Filtre chauffeur -->
                                        <div class="mb-3">
                                            <label for="driverFilter" class="form-label">Filtrer par chauffeur</label>
                                            <select class="form-select" id="driverFilter">
                                                <option value="">Tous</option>
                                                <!-- Charger dynamiquement les chauffeurs -->
                                            </select>
                                        </div>

                                        <button type="button" class="btn btn-primary w-100" id="applyFilters">
                                            <i class="fas fa-filter"></i> Appliquer les filtres
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Calendrier principal -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Calendrier des disponibilités</h5>
                                </div>
                                <div class="card-body">
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteneur pour les modals -->
        <div id="modalContainer"></div>

        <!-- Validation des demandes -->
        <?php if ($roleAccess->hasPermission('validation')): ?>
            <div class="tab-pane fade" id="validation" role="tabpanel" aria-labelledby="validation-tab">
                <div class="section">
                    <!--start Validation des Demandes  -->
                    <div class="container mt-4">
                        <!-- En-tête de page -->
                        <div class="row mb-4">
                            <div class="col-12 d-flex justify-content-between align-items-center">
                                <div>
                                    <h2><i class="fas fa-check-circle me-2"></i>Validation des demandes</h2>
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
                                            <!-- <option value="">Tous</option> -->
                                            <option value="en_attente" selected>En attente</option>
                                            <!-- <option value="valide">Validé</option>
                                        <option value="refuse">Refusé</option>
                                        <option value="modifie">Modifié</option> -->
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
                                                <th>Chauffeur</th>
                                                <th>Trajet</th>
                                                <th>Date de départ prévue</th>
                                                <th>Date d'arrivée prévue</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Rempli via DataTables -->
                                            <tr>
                                                <td>2025-02-15 09:56:00</td>
                                                <td>Didier Drogba</td>
                                                <td>
                                                    <!-- <div class="card-img logo_marque_vehicule">
                                                    <img src="uploads/vehicules/logo_marque/Nissan.jpg"
                                                        class="img-fluid" alt="Photo du vehicule">
                                                </div> -->
                                                    <p>Renault - Clio | REN456-EF</p>
                                                </td>
                                                <td> ---</td>
                                                <td>Point A - Point B</td>
                                                <td> 2025-02-15 09:56:00</td>
                                                <td>2025-02-15 14:59:00</td>
                                                <td><span class="badge badge-in-use">En attente</span></td>
                                                <td>
                                                    <!-- Accepter la demande  -->
                                                    <?php if ($roleAccess->hasPermission('validateRequest')): ?>
                                                        <button class="btn btn-success m-2" title="Accepter la demande">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <!-- Réfuser la demande -->
                                                    <?php if ($roleAccess->hasPermission('rejectRequest')): ?>
                                                        <button class="btn btn-danger" title="Réfuser la demande">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <!-- Voir les détails de la demande -->
                                                    <button class="btn btn-info m-2 btn-view-details"
                                                        title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </button>

                                                    <!-- Modifier la demande  -->
                                                    <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
                                                        <button class="btn btn-warning m-2" title="Modifier la demande">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Suivi des déplacements en cours -->
        <div class="tab-pane fade" id="tracking" role="tabpanel" aria-labelledby="tracking-tab">
            <div class="section">
                <h2><i class="fas fa-road me-2"></i>Suivi des déplacements en cours</h2>
                <div id="ongoingTrips">
                    <!-- Liste des déplacements -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="demandesTable">
                                    <thead>
                                        <tr>
                                            <th>Logo</th>
                                            <th>Designation</th>
                                            <th>Assigné à</th>
                                            <th>Trajet</th>
                                            <th>Date de départ prévue</th>
                                            <th>Date d'arrivée prévue</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Rempli via DataTables -->
                                        <tr>
                                            <td>
                                                <div class="card-img logo_marque_vehicule">
                                                    <img src="uploads/vehicules/logo_marque/Renault.png"
                                                        class="img-fluid rounded-circle" alt="Photo du vehicule">
                                                </div>
                                            </td>
                                            <td>Renault - Clio | REN456-EF</td>
                                            <td>chauffeur_nom</td>
                                            <td>Point A - Point B</td>
                                            <td> 2025-02-15 09:56:00</td>
                                            <td>2025-02-15 14:59:00</td>
                                            <td>En cours</td>
                                            <td>
                                                <!-- Débuter la course -->
                                                <button class="btn btn-primary btn-debuter" title="Débuter la course">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <!-- Course terminée  -->
                                                <button class="btn btn-success" title="Course terminée">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                <!-- Annulées la course  -->
                                                <?php if (in_array($_SESSION['role'], ['gestionnaire', 'administrateur'])): ?>
                                                    <button class="btn btn-danger" title="Annulées la course">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des assignations -->
        <?php if ($roleAccess->hasPermission('historique')): ?>
            <div class="tab-pane fade" id="historique" role="tabpanel" aria-labelledby="historique-tab">
                <h4>
                    <i class="fas fa-history"></i>
                    Historique des assignations
                </h4>
                <hr>

                <!-- Onglets pour les courses terminées et annulées -->
                <ul class="nav nav-tabs" id="historiqueTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="courseTerminee-tab" data-bs-toggle="tab" href="#courseTerminee"
                            role="tab" aria-controls="courseTerminee" aria-selected="true"><i
                                class="fas fa-clipboard-check me-2"></i>Courses Terminées</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="courseAnnulee-tab" data-bs-toggle="tab" href="#courseAnnulee" role="tab"
                            aria-controls="courseAnnulee" aria-selected="false"><i
                                class="fas fa-ban clipboard-check me-2"></i>Courses Annulées</a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="historiqueTabContent">
                    <!-- Liste des courses terminées -->
                    <div class="tab-pane fade show active" id="courseTerminee" role="tabpanel"
                        aria-labelledby="courseTerminee-tab">
                        <!-- courses terminées historique -->
                        <div class="section">
                            <div class="card-header mb-3">
                                <h5>
                                    <i class="fas fa-clipboard-check"></i>
                                    courses terminées
                                </h5>
                                <hr>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="demandesTable">
                                            <thead>
                                                <tr>
                                                    <th>Logo</th>
                                                    <th>Véhicule</th>
                                                    <th>Chauffeur</th>
                                                    <th>Date de départ</th>
                                                    <th>Date d'arrivée</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Rempli via DataTables -->
                                                <tr>
                                                    <td>
                                                        <div class="card-img logo_marque_vehicule">
                                                            <img src="uploads/vehicules/logo_marque/Nissan.jpg"
                                                                class="img-fluid" alt="Photo du vehicule">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p>Renault - Clio | REN456-EF</p>
                                                    </td>
                                                    <td>chauffeur_nom</td>
                                                    <td> 2025-02-15 09:56:00</td>
                                                    <td>2025-02-15 14:59:00</td>
                                                    <td><span class="badge badge-success">Terminée</span></td>
                                                    <td>
                                                        <!-- Voir les détails -->
                                                        <a href="view_detail_course_terminee_historique.php"
                                                            class="btn btn-info m-1" title="Voir les détails">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        <!-- Effacer de l'historique -->
                                                        <?php if ($roleAccess->hasPermission('deleteHistorique')): ?>
                                                            <button class="btn btn-danger m-1" title="Effacer de l'historique">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des courses annulées -->
                    <div class="tab-pane fade" id="courseAnnulee" role="tabpanel" aria-labelledby="courseAnnulee-tab">
                        <!-- courses annullées historique -->
                        <div class="section">
                            <div class="card-header mb-3">
                                <h5>
                                    <i class="fas fa-ban clipboard-check"></i>
                                    courses annullées
                                </h5>
                                <hr>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="demandesTable">
                                            <thead>
                                                <tr>
                                                    <th>Date demande</th>
                                                    <th>Demandeur</th>
                                                    <th>Véhicule</th>
                                                    <th>Chauffeur</th>
                                                    <th>Trajet</th>
                                                    <th>Date de départ prévue</th>
                                                    <th>Date d'arrivée prévue</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Rempli via DataTables -->
                                                <tr>
                                                    <td>2025-02-15 09:56:00</td>
                                                    <td>Didier Drogba</td>
                                                    <td>
                                                        <div class="card-img logo_marque_vehicule">
                                                            <img src="uploads/vehicules/logo_marque/Nissan.jpg"
                                                                class="img-fluid rounded-circle" alt="Photo du vehicule">
                                                        </div>
                                                        <p>Renault - Clio | REN456-EF</p>
                                                    </td>
                                                    <td>chauffeur_nom-</td>
                                                    <td>Point A - Point B</td>
                                                    <td> 2025-02-15 09:56:00</td>
                                                    <td>2025-02-15 14:59:00</td>
                                                    <td><span class="badge badge-danger">Annulé</span></td>
                                                    <td>
                                                        <!-- Effacer de l'historique -->
                                                        <?php if ($roleAccess->hasPermission('deleteHistorique')): ?>
                                                            <button class="btn btn-danger m-1" title="Effacer de l'historique">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="modalContainer"></div>

<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer   -->

<!-- planif -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Éléments du formulaire
    const btnContinuerVehicule = document.getElementById('btnContinuerVehicule');
    const btnContinuerTrajet = document.getElementById('btnContinuerTrajet');
    const sectionVehicule = document.getElementById('sectionVehicule');
    const sectionTrajet = document.getElementById('sectionTrajet');
    const lieuDepart = document.getElementById('lieuDepart');
    const lieuArrivee = document.getElementById('lieuArrivee');
    const suggestionsDepart = document.getElementById('suggestionsDepart');
    const suggestionsArrivee = document.getElementById('suggestionsArrivee');
    const kilometrageEstimee = document.getElementById('kilometrageEstimee');
    const dureeEstimee = document.getElementById('dureeEstimee');
    const vehiculeSelect = document.getElementById('vehicule');
    const reservationForm = document.getElementById('reservationForm');
    const zoneVehiculeSelect = document.getElementById('zoneVehicule');
    const dateDepartPrevue = document.getElementById('dateDepartPrevue');
    const dateArriveePrevue = document.getElementById('dateArriveePrevue');

    // Fonction pour charger les zones
    function chargerZones() {
        fetch('api/zones-vehicules.php')
            .then(response => response.json())
            .then(data => {
                // Vider et remplir le select des zones
                zoneVehiculeSelect.innerHTML = '<option value="">Sélectionnez une zone</option>';

                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(zone => {
                        const option = document.createElement('option');
                        option.value = zone.id;
                        option.textContent = zone.nom_zone;
                        zoneVehiculeSelect.appendChild(option);
                    });
                } else {
                    console.error('Aucune zone disponible ou format de données incorrect');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des zones:', error);
                zoneVehiculeSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            });
    }

    // Charger les zones au chargement de la page
    chargerZones();

    // Validation de la première section et chargement des véhicules disponibles
    btnContinuerVehicule.addEventListener('click', function () {
        const dateDepartValue = dateDepartPrevue.value;
        const dateArriveeValue = dateArriveePrevue.value;
        const typeVehicule = document.getElementById('typeVehicule').value;
        const nbPassagers = document.getElementById('nbPassagers').value;
        const zoneVehicule = zoneVehiculeSelect.value;

        // Valider les dates d'abord
        validerDates();

        // Vérifier si tous les champs sont remplis
        if (dateDepartValue && dateArriveeValue && typeVehicule && nbPassagers && zoneVehicule) {
            // Formater les dates pour l'API
            const dateDepart = new Date(dateDepartValue).toISOString();
            const dateArrivee = new Date(dateArriveeValue).toISOString();

            // Charger les véhicules disponibles en fonction des critères
            fetch(`api/vehicules-disponibles.php?dateDepart=${dateDepart}&dateArrivee=${dateArrivee}&type=${typeVehicule}&passagers=${nbPassagers}&zone=${zoneVehicule}`)
                .then(response => response.json())
                .then(data => {
                    // Vider et remplir le select des véhicules
                    vehiculeSelect.innerHTML = '<option value="">Sélectionnez un véhicule</option>';

                    if (data.success === false || !data.vehicules || data.vehicules.length === 0) {
                        // Message d'erreur personnalisé
                        const errorMessage = data.message || 'Aucun véhicule disponible pour ces critères';
                        vehiculeSelect.innerHTML = `<option value="">${errorMessage}</option>`;
                        console.error(errorMessage);
                    } else {
                        data.vehicules.forEach(vehicule => {
                            const option = document.createElement('option');
                            option.value = vehicule.id_vehicule;
                            option.textContent = `${vehicule.marque} ${vehicule.modele} (${vehicule.immatriculation})`;
                            option.dataset.capacite = vehicule.capacite_passagers;
                            option.dataset.kilometrage = vehicule.kilometrage_actuel;
                            vehiculeSelect.appendChild(option);
                        });
                    }

                    // Afficher la section véhicule
                    sectionVehicule.style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des véhicules:', error);
                    vehiculeSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                });
        } else {
            alert('Veuillez remplir tous les champs de la section 1, y compris la zone du véhicule.');
        }
    });

    // Fonction pour valider les dates
    function validerDates() {
        // Obtenir la date et l'heure actuelles
        const maintenant = new Date();

        // Convertir les valeurs des champs de date en objets Date
        const dateDepart = new Date(dateDepartPrevue.value);
        const dateArrivee = new Date(dateArriveePrevue.value);

        // Vérifier si la date de départ est antérieure à maintenant
        if (dateDepart < maintenant) {
            // Réinitialiser le champ de date de départ à la date et heure actuelles
            const formattedDate = new Date(maintenant.getTime() - maintenant.getTimezoneOffset() * 60000)
                .toISOString().slice(0, 16);
            dateDepartPrevue.value = formattedDate;

            // Afficher un message d'erreur
            alert('La date de départ ne peut pas être antérieure à la date actuelle.');
        }

        // Vérifier si la date d'arrivée est antérieure à la date de départ
        if (dateArrivee <= dateDepart) {
            // Réinitialiser la date d'arrivée à 1 heure après la date de départ
            const dateArriveeParDefaut = new Date(dateDepart);
            dateArriveeParDefaut.setHours(dateDepart.getHours() + 1);

            const formattedArrivee = new Date(dateArriveeParDefaut.getTime() - dateArriveeParDefaut.getTimezoneOffset() * 60000)
                .toISOString().slice(0, 16);
            dateArriveePrevue.value = formattedArrivee;

            // Afficher un message d'erreur
            alert('La date d\'arrivée doit être postérieure à la date de départ.');
        }
    }

    // Ajouter des écouteurs d'événements pour la validation des dates
    dateDepartPrevue.addEventListener('change', validerDates);
    dateArriveePrevue.addEventListener('change', validerDates);

    // Définir les attributs min pour les champs de date
    const formatterDateInput = (date) => {
        return new Date(date.getTime() - date.getTimezoneOffset() * 60000)
            .toISOString().slice(0, 16);
    };

    // Définir la date minimale à la date actuelle
    const maintenant = new Date();
    dateDepartPrevue.min = formatterDateInput(maintenant);
    dateArriveePrevue.min = formatterDateInput(maintenant);

    // Continuer vers la section Trajet
    btnContinuerTrajet.addEventListener('click', function () {
        if (vehiculeSelect.value) {
            sectionTrajet.style.display = 'block';
        } else {
            alert('Veuillez sélectionner un véhicule.');
        }
    });

    // Autocomplétion des lieux
    lieuDepart.addEventListener('input', function () {
        searchLocation(lieuDepart.value, suggestionsDepart);
    });

    lieuArrivee.addEventListener('input', function () {
        searchLocation(lieuArrivee.value, suggestionsArrivee);
    });

    // Calculer l'itinéraire lorsque les deux lieux sont remplis
    lieuArrivee.addEventListener('change', calculateRoute);
    lieuDepart.addEventListener('change', calculateRoute);

    function searchLocation(query, suggestionsElement) {
        if (query.length < 3) {
            suggestionsElement.style.display = 'none';
            return;
        }

        // Appel API de recherche de lieux
        fetch(`api/recherche-lieux.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                suggestionsElement.innerHTML = '';

                if (data.length > 0) {
                    data.forEach(lieu => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = lieu.nom;
                        item.addEventListener('click', function (e) {
                            e.preventDefault();
                            if (suggestionsElement === suggestionsDepart) {
                                lieuDepart.value = lieu.nom;
                            } else {
                                lieuArrivee.value = lieu.nom;
                            }
                            suggestionsElement.style.display = 'none';
                            calculateRoute();
                        });
                        suggestionsElement.appendChild(item);
                    });
                    suggestionsElement.style.display = 'block';
                } else {
                    suggestionsElement.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erreur lors de la recherche de lieux:', error);
                suggestionsElement.style.display = 'none';
            });
    }

    function calculateRoute() {
        const depart = lieuDepart.value;
        const arrivee = lieuArrivee.value;

        if (depart && arrivee) {
            // Appel à une API pour calculer la distance et la durée
            fetch(`api/calculer-itineraire.php?depart=${encodeURIComponent(depart)}&arrivee=${encodeURIComponent(arrivee)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mise à jour des champs
                        kilometrageEstimee.value = data.distance;
                        dureeEstimee.value = data.duree;
                    } else {
                        // Si l'API échoue, définir des valeurs par défaut
                        kilometrageEstimee.value = 1; // Valeur par défaut
                        dureeEstimee.value = 30; // Valeur par défaut
                        console.log('Impossible de calculer l\'itinéraire, utilisation des valeurs par défaut');
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du calcul de l\'itinéraire:', error);
                    // En cas d'erreur, définir des valeurs par défaut
                    kilometrageEstimee.value = 1; // Valeur par défaut
                    dureeEstimee.value = 30; // Valeur par défaut
                });
        }
    }

    // Soumission du formulaire - VERSION CORRIGÉE
    reservationForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Empêcher la soumission par défaut
        console.log('Formulaire soumis'); // Débogage

        try {
            // Vérification manuelle des valeurs nécessaires, sans compter sur les attributs required
            let dateDepartValue = dateDepartPrevue.value;
            let dateArriveeValue = dateArriveePrevue.value;
            let zoneVehiculeValue = zoneVehiculeSelect.value;
            let typeVehiculeValue = document.getElementById('typeVehicule').value;
            let nbPassagersValue = document.getElementById('nbPassagers').value;
            let vehiculeValue = vehiculeSelect.value;
            let demandeurValue = document.getElementById('demandeur').value;
            let lieuDepartValue = lieuDepart.value;
            let lieuArriveeValue = lieuArrivee.value;
            let objetDemandeValue = document.getElementById('objetDemande').value;

            // Vérifier si les valeurs essentielles sont présentes
            if (!dateDepartValue) {
                alert('Veuillez entrer une date de départ');
                return;
            }
            if (!dateArriveeValue) {
                alert('Veuillez entrer une date d\'arrivée');
                return;
            }
            if (!zoneVehiculeValue) {
                alert('Veuillez sélectionner une zone');
                return;
            }
            if (!typeVehiculeValue) {
                alert('Veuillez sélectionner un type de véhicule');
                return;
            }
            if (!nbPassagersValue || nbPassagersValue <= 0) {
                alert('Veuillez entrer un nombre de passagers valide');
                return;
            }
            if (!vehiculeValue) {
                alert('Veuillez sélectionner un véhicule');
                return;
            }
            if (!demandeurValue) {
                alert('Veuillez entrer le nom du demandeur');
                return;
            }
            if (!lieuDepartValue) {
                alert('Veuillez entrer un lieu de départ');
                return;
            }
            if (!lieuArriveeValue) {
                alert('Veuillez entrer un lieu d\'arrivée');
                return;
            }
            if (!objetDemandeValue) {
                alert('Veuillez entrer l\'objet de la demande');
                return;
            }

            // S'assurer que les champs kilométrage et durée ont des valeurs, même par défaut
            if (!kilometrageEstimee.value) kilometrageEstimee.value = 1;
            if (!dureeEstimee.value) dureeEstimee.value = 30;

            // Préparer les données du formulaire manuellement
            const formData = new FormData();
            formData.append('dateDepartPrevue', dateDepartValue);
            formData.append('dateArriveePrevue', dateArriveeValue);
            formData.append('zoneVehicule', zoneVehiculeValue);
            formData.append('typeVehicule', typeVehiculeValue);
            formData.append('nbPassagers', nbPassagersValue);
            formData.append('vehicule', vehiculeValue);
            formData.append('demandeur', demandeurValue);
            formData.append('lieuDepart', lieuDepartValue);
            formData.append('lieuArrivee', lieuArriveeValue);
            formData.append('objetDemande', objetDemandeValue);
            formData.append('kilometrageEstimee', kilometrageEstimee.value);
            formData.append('dureeEstimee', dureeEstimee.value);

            // Afficher un message de chargement
            const loadingMessage = document.createElement('div');
            loadingMessage.className = 'alert alert-info text-center position-fixed top-50 start-50 translate-middle';
            loadingMessage.style.zIndex = '9999';
            loadingMessage.innerHTML = '<strong>Traitement en cours...</strong> <div class="spinner-border spinner-border-sm" role="status"></div>';
            document.body.appendChild(loadingMessage);

            // Envoyer la réservation
            fetch('api/enregistrer-reservation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Vérifier le Content-Type de la réponse
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // Si la réponse n'est pas du JSON, récupérer le texte pour le débogage
                    return response.text().then(text => {
                        console.error('Réponse non-JSON reçue:', text);
                        throw new Error('Réponse du serveur invalide. Veuillez consulter la console pour plus de détails.');
                    });
                }
            })
            .then(data => {
                // Supprimer le message de chargement
                if (document.body.contains(loadingMessage)) {
                    document.body.removeChild(loadingMessage);
                }
                
                if (data.success) {
                    alert('Réservation enregistrée avec succès !');
                    
                    // Réinitialiser le formulaire
                    reservationForm.reset();
                    sectionVehicule.style.display = 'none';
                    sectionTrajet.style.display = 'none';
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'Erreur lors de l\'enregistrement de la réservation');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                // Supprimer le message de chargement
                if (document.body.contains(loadingMessage)) {
                    document.body.removeChild(loadingMessage);
                }
                alert('Erreur lors de l\'envoi de la réservation: ' + error.message);
            });
        } catch (error) {
            console.error('Erreur lors de la soumission:', error);
            alert('Une erreur est survenue lors de la préparation de la soumission: ' + error.message);
        }
    });

    // Masquer les suggestions quand on clique ailleurs
    document.addEventListener('click', function (e) {
        if (e.target !== lieuDepart && e.target !== suggestionsDepart) {
            suggestionsDepart.style.display = 'none';
        }
        if (e.target !== lieuArrivee && e.target !== suggestionsArrivee) {
            suggestionsArrivee.style.display = 'none';
        }
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Forcer le chargement des déplacements en cours pour les validateurs
        function chargerDeplacementsEnCours() {
            fetch('api/charger-deplacements-en-cours.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#tracking .table tbody');
                    tbody.innerHTML = ''; // Vider le tableau existant

                    if (data.success && data.deplacements && data.deplacements.length > 0) {
                        data.deplacements.forEach(deplacement => {
                            const row = document.createElement('tr');

                            // Formater les dates
                            const dateDepart = new Date(deplacement.date_depart).toLocaleString('fr-FR');
                            const dateRetourPrevue = new Date(deplacement.date_retour_prevue).toLocaleString('fr-FR');

                            // Déterminer le statut et les boutons à afficher
                            let statutHtml = '';
                            let boutonsHtml = '';

                            switch (deplacement.statut) {
                                case 'validee':
                                    statutHtml = '<span class="badge bg-success">Validée</span>';
                                    boutonsHtml = `
        <button class="btn btn-primary btn-debuter" data-id="${deplacement.id_reservation}" title="Débuter la course">
            <i class="fas fa-play"></i>
        </button>`;

                                    // Ajouter le bouton d'annulation seulement pour admin et gestionnaire
                                    if ('<?php echo $_SESSION['role']; ?>' === 'administrateur' || '<?php echo $_SESSION['role']; ?>' === 'gestionnaire') {
                                        boutonsHtml += `
            <button class="btn btn-danger btn-annuler" data-id="${deplacement.id_reservation}" title="Annuler la course">
                <i class="fas fa-times"></i>
            </button>`;
                                    }
                                    break;
                                case 'en_cours':
                                    statutHtml = '<span class="badge bg-info">En cours</span>';
                                    boutonsHtml = `
        <button class="btn btn-success btn-terminer" data-id="${deplacement.id_reservation}" title="Terminer la course">
            <i class="fas fa-check-circle"></i>
        </button>`;

                                    // Ajouter le bouton d'annulation seulement pour admin et gestionnaire
                                    if ('<?php echo $_SESSION['role']; ?>' === 'administrateur' || '<?php echo $_SESSION['role']; ?>' === 'gestionnaire') {
                                        boutonsHtml += `
            <button class="btn btn-danger btn-annuler" data-id="${deplacement.id_reservation}" title="Annuler la course">
                <i class="fas fa-times"></i>
            </button>`;
                                    }
                                    break;
                            }
                            // Construire la ligne du tableau
                            row.innerHTML = `
    <td>
        <div class="card-img logo_marque_vehicule">
            <img src="uploads/vehicules/logo_marque/${deplacement.logo_marque_vehicule || 'default.png'}"
                class="img-fluid rounded-circle" alt="Logo du véhicule">
        </div>
    </td>
    <td>${deplacement.marque} - ${deplacement.modele} | ${deplacement.immatriculation}</td>
    <td>${deplacement.chauffeur_nom || '---'}</td>
    <td>${deplacement.point_depart} - ${deplacement.point_arrivee}</td>
    <td>${dateDepart}</td>
    <td>${dateRetourPrevue}</td>
    <td>${statutHtml}</td>
    <td>
        ${deplacement.materiel ?
                                    `<button class="btn btn-info btn-sm mb-1" type="button" data-bs-toggle="tooltip" title="${deplacement.materiel}">
               <i class="fas fa-box"></i> Voir matériel
           </button><br>` : ''}
        ${boutonsHtml}
    </td>
`;

                            tbody.appendChild(row);
                        });
                    } else {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td colspan="8" class="text-center">Aucun déplacement en cours</td>`;
                        tbody.appendChild(row);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des déplacements:', error);
                    const tbody = document.querySelector('#tracking .table tbody');
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="8" class="text-center text-danger">Erreur de chargement des données</td>`;
                    tbody.appendChild(row);
                });
        }

        // Charger immédiatement pour les validateurs
        const userRole = '<?php echo $_SESSION['role']; ?>';
        if (userRole === 'validateur') {
            chargerDeplacementsEnCours();
        }
    });
</script>

<script>
    // Script corrigé pour l'initialisation et le rendu du calendrier
    document.addEventListener('DOMContentLoaded', function () {
        // Définir une variable globale pour l'objet calendar
        window.calendarInstance = null;

        // Fonction pour initialiser le calendrier s'il n'existe pas encore
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return false;

            // Vérifier si le calendrier est déjà initialisé
            if (window.calendarInstance) return true;

            try {
                // Créer l'objet calendrier avec les options complètes
                window.calendarInstance = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'fr',
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: function (fetchInfo, successCallback, failureCallback) {
                        fetch(`api/charger-evenements-calendrier.php?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    failureCallback(new Error(data.message));
                                } else {
                                    successCallback(data);
                                }
                            })
                            .catch(error => {
                                failureCallback(error);
                            });
                    },
                    eventClick: function (info) {
                        // Logique de clic sur événement
                        const event = info.event;
                        const modalHtml = `
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Détails de la réservation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-6">
            <strong>Véhicule :</strong>
            <p>${event.extendedProps.vehicule || 'Non spécifié'}</p>
          </div>
          <div class="col-6">
            <strong>Trajet :</strong>
            <p>${event.extendedProps.trajet || 'Non spécifié'}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <strong>Chauffeur :</strong>
            <p>${event.extendedProps.chauffeur || 'Non assigné'}</p>
          </div>
          <div class="col-6">
            <strong>Demandeur :</strong>
            <p>${event.extendedProps.demandeur || 'Non spécifié'}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <strong>Départ :</strong>
            <p>${event.start ? event.start.toLocaleString() : 'Non spécifié'}</p>
          </div>
          <div class="col-6">
            <strong>Retour :</strong>
            <p>${event.end ? event.end.toLocaleString() : 'Non spécifié'}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <strong>Objet de la demande :</strong>
            <p>${event.extendedProps.objetDemande || 'Non spécifié'}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <strong>Statut :</strong>
            <span class="badge" style="background-color: ${event.backgroundColor}">
              ${event.extendedProps.statut || 'Non spécifié'}
            </span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>
`;

                        // Créer et afficher le modal
                        const modalContainer = document.getElementById('modalContainer');
                        if (modalContainer) {
                            modalContainer.innerHTML = modalHtml;
                            const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
                            modal.show();
                        }
                    },
                    lazyFetching: true,
                    progressiveEventRendering: true
                });

                console.log('Calendrier initialisé avec succès');
                return true;
            } catch (error) {
                console.error('Erreur lors de l\'initialisation du calendrier:', error);
                return false;
            }
        }

        // Fonction sécurisée pour forcer le rendu du calendrier
        function forceCalendarRender() {
            // Vérifier si l'objet calendar existe et est visible
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl || calendarEl.offsetParent === null) return;

            // S'assurer que le calendrier est initialisé
            if (!window.calendarInstance) {
                const success = initializeCalendar();
                if (!success) return;
            }

            try {
                // Rendre le calendrier
                window.calendarInstance.render();

                // Forcer une mise à jour de la taille
                window.calendarInstance.updateSize();

                // Rafraîchir les événements si possible
                if (typeof window.calendarInstance.refetchEvents === 'function') {
                    window.calendarInstance.refetchEvents();
                }

                console.log('Calendrier rafraîchi avec succès');
            } catch (error) {
                console.error('Erreur lors du rafraîchissement du calendrier:', error);
            }
        }

        // Configurer les écouteurs d'événements pour les onglets
        function setupTabEventListeners() {
            const calendarTab = document.getElementById('calendar-tab');
            if (!calendarTab) return;

            // Initialiser le calendrier au chargement de la page
            if (document.querySelector('#calendar-tab.active')) {
                console.log('Onglet calendrier actif au chargement, initialisation...');
                setTimeout(forceCalendarRender, 100);
            }

            // Écouteur pour le clic sur l'onglet
            calendarTab.addEventListener('click', function () {
                console.log('Clic sur l\'onglet calendrier détecté');
                setTimeout(forceCalendarRender, 100);
            });

            // Écouteur pour l'événement "shown.bs.tab"
            calendarTab.addEventListener('shown.bs.tab', function () {
                console.log('Onglet calendrier affiché (événement shown.bs.tab)');
                setTimeout(forceCalendarRender, 100);
            });
        }

        // Surveillance des changements de visibilité
        function setupVisibilityObserver() {
            const calendarPane = document.getElementById('calendar');
            if (!calendarPane) return;

            // Observer les changements de visibilité avec IntersectionObserver
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        console.log('Calendrier visible dans le viewport');
                        setTimeout(forceCalendarRender, 100);
                    }
                });
            }, { threshold: 0.1 });

            observer.observe(calendarPane);
        }

        // Écouteur pour la fin du chargement de la page
        window.addEventListener('load', function () {
            console.log('Page entièrement chargée');

            // Si l'onglet calendrier est actif, forcer le rendu
            if (document.querySelector('#calendar-tab.active')) {
                setTimeout(forceCalendarRender, 200);
            }

            // Configurer les écouteurs d'événements
            setupTabEventListeners();
            setupVisibilityObserver();
        });

        // Fonction pour vérifier l'activation de l'onglet par URL
        function checkUrlForCalendarTab() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabToShow = urlParams.get('tab');

            if (tabToShow === 'calendar') {
                console.log('Paramètre d\'URL pour afficher le calendrier détecté');
                const calendarTabEl = document.getElementById('calendar-tab');
                if (calendarTabEl) {
                    try {
                        const bsTab = new bootstrap.Tab(calendarTabEl);
                        bsTab.show();
                        // Forcer le rendu après le changement d'onglet
                        setTimeout(forceCalendarRender, 200);
                    } catch (error) {
                        console.error('Erreur lors de l\'activation de l\'onglet calendrier:', error);
                    }
                }
            }
        }

        // Vérifier les paramètres URL au chargement
        checkUrlForCalendarTab();
    });
</script>

<!-- validation -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const demandesTable = document.getElementById('demandesTable');
        const filterStatut = document.getElementById('filterStatut');
        const filterPeriode = document.getElementById('filterPeriode');
        const filterPriorite = document.getElementById('filterPriorite');
        const filterTypeVehicule = document.getElementById('filterTypeVehicule');
        const btnRefresh = document.getElementById('btnRefresh');

        // Charger les demandes initiales
        function chargerDemandes(filtres = {}) {
            fetch('api/charger-demandes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(filtres)
            })
                .then(response => response.json())
                .then(data => {
                    const tbody = demandesTable.querySelector('tbody');
                    tbody.innerHTML = ''; // Vider le tableau existant

                    if (data.success && data.demandes && data.demandes.length > 0) {
                        data.demandes.forEach(demande => {
                            const row = document.createElement('tr');

                            // Formater la date de demande
                            const dateDemande = new Date(demande.date_demande).toLocaleString('fr-FR', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            // Formater les dates de départ et de retour
                            const dateDepart = new Date(demande.date_depart).toLocaleString('fr-FR', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            const dateRetour = new Date(demande.date_retour_prevue).toLocaleString('fr-FR', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            // Déterminer la classe du badge de statut
                            let badgeClass = 'badge-warning'; // Par défaut
                            switch (demande.statut) {
                                case 'validee':
                                    badgeClass = 'badge-success';
                                    break;
                                case 'en_cours':
                                    badgeClass = 'badge-info';
                                    break;
                                case 'terminee':
                                    badgeClass = 'badge-secondary';
                                    break;
                                case 'annulee':
                                    badgeClass = 'badge-danger';
                                    break;
                            }

                            row.innerHTML = `
                        <td>${dateDemande}</td>
                        <td>${demande.demandeur || '---'}</td>
                        <td>${demande.vehicule || '---'}</td>
                        <td>${demande.chauffeur || '---'}</td>
                        <td>${demande.trajet || '---'}</td>
                        <td>${dateDepart}</td>
                        <td>${dateRetour}</td>
                        <td><span class="badge ${badgeClass}">${demande.statut_libelle}</span></td>
                        <td>
                            <button class="btn btn-success btn-accepter m-2" data-id="${demande.id_reservation}" title="Accepter la demande">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            <button class="btn btn-danger btn-refuser m-2" data-id="${demande.id_reservation}" title="Réfuser la demande">
                                <i class="fas fa-times"></i>
                            </button>
                            <!-- Voir les détails de la demande -->
                            <button class="btn btn-info m-2 btn-view-details" title="Voir les détails" data-id="${demande.id_reservation}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-warning btn-modifier m-2" data-id="${demande.id_reservation}" title="Modifier la demande">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    `;

                            tbody.appendChild(row);
                        });

                        // Ajouter les écouteurs d'événements pour les boutons
                        ajouterEcouteursActions();
                    } else {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td colspan="9" class="text-center">Aucune demande trouvée</td>`;
                        tbody.appendChild(row);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des demandes:', error);
                    const tbody = demandesTable.querySelector('tbody');
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="9" class="text-center text-danger">Erreur de chargement des données</td>`;
                    tbody.appendChild(row);
                });
        }

        // Fonction pour ajouter les écouteurs d'événements aux boutons d'action
        function ajouterEcouteursActions() {
            // Accepter une demande
            document.querySelectorAll('.btn-accepter').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    traiterDemande(idReservation, 'valider');
                });
            });

            // Refuser une demande
            document.querySelectorAll('.btn-refuser').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    traiterDemande(idReservation, 'refuser');
                });
            });

            // Proposer un véhicule
            document.querySelectorAll('.btn-proposer-vehicule').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    ouvrirModalPropositionVehicule(idReservation);
                });
            });

            // Modifier une demande
            document.querySelectorAll('.btn-modifier').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    ouvrirModalModificationDemande(idReservation);
                });
            });

            // Voir les détails d'une demande
            document.querySelectorAll('.btn-view-details').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    afficherDetailsReservation(idReservation);
                });
            });
        }

        // Fonction pour traiter une demande (validation/refus)
        function traiterDemande(idReservation, action) {
            if (action === 'valider') {
                // Vérifier la disponibilité des ressources avant validation
                fetch(`api/verifier-disponibilite.php?id_reservation=${idReservation}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Ouvrir une modal de confirmation avec les détails
                            const modalHtml = `
                    <div class="modal fade" id="confirmationReservationModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirmation de la réservation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Véhicule attribué</h6>
                                            <select class="form-select mb-3" id="vehiculeSelect">
                                                ${data.vehicules.map(vehicule => `
                                                    <option value="${vehicule.id_vehicule}" ${vehicule.id_vehicule === data.vehicule.id_vehicule ? 'selected' : ''}>
                                                        ${vehicule.marque} ${vehicule.modele} (${vehicule.immatriculation})
                                                    </option>
                                                `).join('')}
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Chauffeur attribué</h6>
                                            <select class="form-select mb-3" id="chauffeurSelect">
                                                ${data.chauffeurs.map(chauffeur => `
                                                    <option value="${chauffeur.id_chauffeur}" ${chauffeur.id_chauffeur === data.chauffeur.id_chauffeur ? 'selected' : ''}>
                                                        ${chauffeur.nom} ${chauffeur.prenoms} (${chauffeur.statut})
                                                    </option>
                                                `).join('')}
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <label class="form-label">Commentaires de validation</label>
                                            <textarea class="form-control" id="commentaireValidation" rows="3" 
                                                placeholder="Commentaires optionnels pour la validation"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Priorité de la réservation</label>
                                            <select class="form-select" id="prioriteReservation">
                                                <option value="1">Normale</option>
                                                <option value="2">Moyenne</option>
                                                <option value="3">Haute</option>
                                                <option value="4">Critique</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirmation</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="confirmationNotification">
                                                <label class="form-check-label" for="confirmationNotification">
                                                    Envoyer une notification au demandeur
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="button" class="btn btn-primary" id="btnConfirmerValidation">
                                        Confirmer la validation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                            // Créer et afficher la modal
                            const modalContainer = document.getElementById('modalContainer');
                            modalContainer.innerHTML = modalHtml;

                            const modal = new bootstrap.Modal(document.getElementById('confirmationReservationModal'));
                            modal.show();

                            // Gestion de la confirmation
                            document.getElementById('btnConfirmerValidation').addEventListener('click', () => {
                                const vehiculeSelectionne = document.getElementById('vehiculeSelect').value;
                                const chauffeurSelectionne = document.getElementById('chauffeurSelect').value;
                                const commentaire = document.getElementById('commentaireValidation').value;
                                const priorite = document.getElementById('prioriteReservation').value;
                                const envoyerNotification = document.getElementById('confirmationNotification').checked;

                                // Envoi de la validation finale
                                fetch('api/valider-reservation.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        id_reservation: idReservation,
                                        id_vehicule: vehiculeSelectionne,
                                        id_chauffeur: chauffeurSelectionne,
                                        commentaire: commentaire,
                                        priorite: priorite,
                                        notification: envoyerNotification
                                    })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            afficherNotification('Réservation validée avec succès', 'success');
                                            modal.hide();
                                            chargerDemandes(recupererFiltres());

                                            setTimeout(() => {
                                                window.location.reload();
                                            }, 1000);

                                        } else {
                                            afficherNotification(data.message || 'Erreur lors de la validation', 'danger');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Erreur:', error);
                                        afficherNotification('Erreur de communication avec le serveur', 'danger');
                                    });
                            });
                        } else {
                            // Gérer le cas où aucune ressource n'est disponible
                            afficherNotification(data.message || 'Aucune ressource disponible', 'warning');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        afficherNotification('Erreur de vérification des disponibilités', 'danger');
                    });
            } else {
                // Logique existante pour le refus
                fetch('api/traiter-demande.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_reservation: idReservation,
                        action: action
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            afficherNotification('Demande traitée avec succès', 'success');
                            chargerDemandes(recupererFiltres());

                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);

                        } else {
                            afficherNotification(data.message || 'Erreur lors du traitement', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        afficherNotification('Erreur de communication avec le serveur', 'danger');
                    });
            }
        }

        // Fonction pour ouvrir la modal de proposition de véhicule
        function ouvrirModalPropositionVehicule(idReservation) {
            // Créer et afficher une modal Bootstrap dynamiquement
            const modalHtml = `
            <div class="modal fade" id="propositionVehiculeModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Proposer un autre véhicule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formPropositionVehicule">
                                <input type="hidden" name="id_reservation" value="${idReservation}">
                                <div class="mb-3">
                                    <label class="form-label">Véhicule disponible *</label>
                                    <select class="form-select" id="vehiculePropose" name="id_vehicule" required>
                                        <option value="">Chargement des véhicules...</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Raison de la proposition</label>
                                    <textarea class="form-control" name="raison" rows="3" placeholder="Expliquez pourquoi vous proposez ce véhicule"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Soumettre la proposition</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

            // Ajouter la modal au DOM
            const modalContainer = document.getElementById('modalContainer');
            modalContainer.innerHTML = modalHtml;

            // Charger les véhicules disponibles
            fetch(`api/vehicules-disponibles.php?id_reservation=${idReservation}`)
                .then(response => response.json())
                .then(data => {
                    const vehiculeSelect = document.getElementById('vehiculePropose');
                    vehiculeSelect.innerHTML = '<option value="">Sélectionnez un véhicule</option>';

                    if (data.success && data.vehicules && data.vehicules.length > 0) {
                        data.vehicules.forEach(vehicule => {
                            const option = document.createElement('option');
                            option.value = vehicule.id_vehicule;
                            option.textContent = `${vehicule.marque} ${vehicule.modele} (${vehicule.immatriculation})`;
                            vehiculeSelect.appendChild(option);
                        });
                    } else {
                        vehiculeSelect.innerHTML = '<option value="">Aucun véhicule disponible</option>';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des véhicules:', error);
                });

            // Gérer la soumission du formulaire
            const modal = new bootstrap.Modal(document.getElementById('propositionVehiculeModal'));
            modal.show();

            const formPropositionVehicule = document.getElementById('formPropositionVehicule');
            formPropositionVehicule.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(formPropositionVehicule);

                fetch('api/proposer-vehicule.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            afficherNotification('Proposition de véhicule envoyée avec succès', 'success');
                            modal.hide();
                            chargerDemandes(recupererFiltres());
                        } else {
                            afficherNotification(data.message || 'Erreur lors de la proposition', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        afficherNotification('Erreur de communication avec le serveur', 'danger');
                    });
            });
        }

        // Voici le code modifié pour la fonction ouvrirModalModificationDemande

        function ouvrirModalModificationDemande(idReservation) {
    // Récupérer les détails de la réservation
    fetch(`api/details-reservation.php?id=${idReservation}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const reservation = data.reservation;

                // Créer et afficher une modal Bootstrap dynamiquement
                const modalHtml = `
                <div class="modal fade" id="modificationDemandeModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier la demande de réservation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formModificationDemande">
                                    <input type="hidden" name="id_reservation" value="${idReservation}">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date de départ *</label>
                                            <input type="datetime-local" class="form-control" name="date_depart" 
                                                value="${formatDateForInput(reservation.date_depart)}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date de retour prévue *</label>
                                            <input type="datetime-local" class="form-control" name="date_retour_prevue" 
                                                value="${formatDateForInput(reservation.date_retour_prevue)}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Nombre de passagers *</label>
                                            <input type="number" class="form-control" name="nombre_passagers" 
                                                value="${reservation.nombre_passagers}" min="1" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Zone de véhicule *</label>
                                            <select class="form-select" name="zone_vehicule_id" id="zoneVehiculeModification" required>
                                                <option value="">Chargement des zones...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Véhicule</label>
                                            <select class="form-select" name="id_vehicule" id="vehiculeModification">
                                                <option value="">Chargement des véhicules...</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label">Motif de modification *</label>
                                            <textarea class="form-control" name="motif_modification" rows="3" 
                                                placeholder="Expliquez brièvement les raisons de la modification" required></textarea>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 mt-3">Enregistrer les modifications</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                `;

                // Ajouter la modal au DOM
                const modalContainer = document.getElementById('modalContainer');
                modalContainer.innerHTML = modalHtml;

                // Charger les zones disponibles
                fetch('api/zones-vehicules.php')
                    .then(response => response.json())
                    .then(data => {
                        const zoneSelect = document.getElementById('zoneVehiculeModification');
                        zoneSelect.innerHTML = '<option value="">Sélectionnez une zone</option>';

                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(zone => {
                                const option = document.createElement('option');
                                option.value = zone.id;
                                option.textContent = zone.nom_zone;
                                
                                // Sélectionner la zone actuelle
                                if (zone.id === reservation.zone_vehicule_id) {
                                    option.selected = true;
                                }
                                
                                zoneSelect.appendChild(option);
                            });
                            
                            // Charger les véhicules de la zone actuelle
                            chargerVehiculesParZone(reservation.zone_vehicule_id, reservation.id_vehicule);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des zones:', error);
                    });

                // Fonction pour charger les véhicules d'une zone spécifique
                function chargerVehiculesParZone(zoneId, vehiculeActuel = null) {
                    if (!zoneId) return;
                    
                    const params = new URLSearchParams({
                        dateDepart: new Date(reservation.date_depart).toISOString(),
                        dateArrivee: new Date(reservation.date_retour_prevue).toISOString(),
                        type: reservation.type_vehicule || '',
                        passagers: reservation.nombre_passagers || 1,
                        zone: zoneId,
                        id_reservation: idReservation
                    });
                    
                    fetch(`api/vehicules-disponibles.php?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            const vehiculeSelect = document.getElementById('vehiculeModification');
                            vehiculeSelect.innerHTML = '<option value="">Sélectionnez un véhicule</option>';

                            if (data.success && data.vehicules && data.vehicules.length > 0) {
                                data.vehicules.forEach(vehicule => {
                                    const option = document.createElement('option');
                                    option.value = vehicule.id_vehicule;
                                    option.textContent = `${vehicule.marque} ${vehicule.modele} (${vehicule.immatriculation})`;

                                    // Sélectionner le véhicule actuel
                                    if (vehicule.id_vehicule === vehiculeActuel) {
                                        option.selected = true;
                                    }

                                    vehiculeSelect.appendChild(option);
                                });
                            } else {
                                vehiculeSelect.innerHTML = '<option value="">Aucun véhicule disponible</option>';
                            }
                        })
                        .catch(error => {
                            console.error('Erreur lors du chargement des véhicules:', error);
                        });
                }

                // Écouteur pour le changement de zone
                document.getElementById('zoneVehiculeModification').addEventListener('change', function() {
                    chargerVehiculesParZone(this.value);
                });

                // Afficher la modal
                const modal = new bootstrap.Modal(document.getElementById('modificationDemandeModal'));
                modal.show();

                // Gérer la soumission du formulaire de modification
                const formModificationDemande = document.getElementById('formModificationDemande');
                formModificationDemande.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(formModificationDemande);

                    // Afficher un message de chargement
                    const loadingMessage = document.createElement('div');
                    loadingMessage.className = 'alert alert-info text-center position-fixed top-50 start-50 translate-middle';
                    loadingMessage.style.zIndex = '9999';
                    loadingMessage.innerHTML = '<strong>Traitement en cours...</strong> <div class="spinner-border spinner-border-sm" role="status"></div>';
                    document.body.appendChild(loadingMessage);

                    fetch('api/modifier-demande.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Supprimer le message de chargement
                        if (document.body.contains(loadingMessage)) {
                            document.body.removeChild(loadingMessage);
                        }
                        
                        if (data.success) {
                            afficherNotification('Demande modifiée avec succès', 'success');
                            modal.hide();
                            
                            // Recharger les données (si fonction existe)
                            if (typeof chargerDemandes === 'function') {
                                chargerDemandes(recupererFiltres());
                            }

                            // Recharger la page après un court délai
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);

                        } else {
                            afficherNotification(data.message || 'Erreur lors de la modification', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        // Supprimer le message de chargement
                        if (document.body.contains(loadingMessage)) {
                            document.body.removeChild(loadingMessage);
                        }
                        afficherNotification('Erreur de communication avec le serveur', 'danger');
                    });
                });
            } else {
                afficherNotification(data.message || 'Impossible de charger les détails de la réservation', 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            afficherNotification('Erreur de communication avec le serveur', 'danger');
        });
}

// Fonction utilitaire pour formater les dates pour les inputs datetime-local
function formatDateForInput(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

        // Fonction pour afficher des notifications
        function afficherNotification(message, type = 'info') {
            // Créer un conteneur de notifications s'il n'existe pas
            let notificationContainer = document.getElementById('notification-container');
            if (!notificationContainer) {
                notificationContainer = document.createElement('div');
                notificationContainer.id = 'notification-container';
                notificationContainer.className = 'position-fixed top-0 end-0 p-3 z-3';
                document.body.appendChild(notificationContainer);
            }

            // Créer l'élément de notification
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-bg-${type} border-0`;
            toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

            // Ajouter la notification
            notificationContainer.appendChild(toast);

            // Initialiser et montrer le toast avec Bootstrap
            const toastInstance = new bootstrap.Toast(toast);
            toastInstance.show();

            // Supprimer le toast après qu'il ait été caché
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Récupérer les filtres actuels
        function recupererFiltres() {
            return {
                statut: filterStatut.value,
                periode: filterPeriode.value,
                priorite: filterPriorite.value,
                type_vehicule: filterTypeVehicule.value
            };
        }

        // Événement de rafraîchissement
        btnRefresh.addEventListener('click', () => {
            chargerDemandes(recupererFiltres());
        });

        // Événements de filtrage
        [filterStatut, filterPeriode, filterPriorite, filterTypeVehicule].forEach(filter => {
            filter.addEventListener('change', () => {
                chargerDemandes(recupererFiltres());
            });
        });

        // Charger les demandes initiales
        chargerDemandes();
    });
</script>

<!-- suivie des course -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tableau pour le suivi des déplacements en cours
        const ongoingTripsTable = document.querySelector('#tracking .table');

        // Fonction pour charger les déplacements en cours
        function chargerDeplacementsEnCours() {
            fetch('api/charger-deplacements-en-cours.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = ongoingTripsTable.querySelector('tbody');
                    tbody.innerHTML = ''; // Vider le tableau existant

                    if (data.success && data.deplacements && data.deplacements.length > 0) {
                        data.deplacements.forEach(deplacement => {
                            const row = document.createElement('tr');

                            // Formater les dates
                            const dateDepart = new Date(deplacement.date_depart).toLocaleString('fr-FR');
                            const dateRetourPrevue = new Date(deplacement.date_retour_prevue).toLocaleString('fr-FR');

                            // Déterminer le statut et les boutons à afficher
                            let statutHtml = '';
                            let boutonsHtml = '';

                            switch (deplacement.statut) {
                                case 'validee':
                                    statutHtml = '<span class="badge bg-success">Validée</span>';
                                    boutonsHtml = `
        <button class="btn btn-primary btn-debuter" data-id="${deplacement.id_reservation}" title="Débuter la course">
            <i class="fas fa-play"></i>
        </button>`;

                                    // Ajouter le bouton d'annulation seulement pour admin et gestionnaire
                                    if ('<?php echo $_SESSION['role']; ?>' === 'administrateur' || '<?php echo $_SESSION['role']; ?>' === 'gestionnaire') {
                                        boutonsHtml += `
            <button class="btn btn-danger btn-annuler" data-id="${deplacement.id_reservation}" title="Annuler la course">
                <i class="fas fa-times"></i>
            </button>`;
                                    }
                                    break;
                                case 'en_cours':
                                    statutHtml = '<span class="badge bg-info">En cours</span>';
                                    boutonsHtml = `
        <button class="btn btn-success btn-terminer" data-id="${deplacement.id_reservation}" title="Terminer la course">
            <i class="fas fa-check-circle"></i>
        </button>`;

                                    // Ajouter le bouton d'annulation seulement pour admin et gestionnaire
                                    if ('<?php echo $_SESSION['role']; ?>' === 'administrateur' || '<?php echo $_SESSION['role']; ?>' === 'gestionnaire') {
                                        boutonsHtml += `
            <button class="btn btn-danger btn-annuler" data-id="${deplacement.id_reservation}" title="Annuler la course">
                <i class="fas fa-times"></i>
            </button>`;
                                    }
                                    break;
                            }
                            // Construire la ligne du tableau
                            row.innerHTML = `
                        <td>
                            <div class="card-img logo_marque_vehicule">
                                <img src="uploads/vehicules/logo_marque/${deplacement.logo_marque_vehicule || 'default.png'}"
                                    class="img-fluid rounded-circle" alt="Logo du véhicule">
                            </div>
                        </td>
                        <td>${deplacement.marque} - ${deplacement.modele} | ${deplacement.immatriculation}</td>
                        <td>${deplacement.chauffeur_nom || '---'}</td>
                        <td>${deplacement.point_depart} - ${deplacement.point_arrivee}</td>
                        <td>${dateDepart}</td>
                        <td>${dateRetourPrevue}</td>
                        <td>${statutHtml}</td>
                        <td>${boutonsHtml}</td>
                    `;

                            tbody.appendChild(row);
                        });

                        // Ajouter les écouteurs d'événements pour les boutons
                        ajouterEcouteursDeplacements();
                    } else {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td colspan="8" class="text-center">Aucun déplacement en cours</td>`;
                        tbody.appendChild(row);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des déplacements:', error);
                    const tbody = ongoingTripsTable.querySelector('tbody');
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="8" class="text-center text-danger">Erreur de chargement des données</td>`;
                    tbody.appendChild(row);
                });
        }

        // Fonction pour ajouter les écouteurs d'événements aux boutons
        function ajouterEcouteursDeplacements() {
            // Débuter une course
            document.querySelectorAll('.btn-debuter').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    verifierEtDebuterCourse(idReservation);
                });
            });

            // Terminer une course
            document.querySelectorAll('.btn-terminer').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    terminerCourse(idReservation);
                });
            });

            // Annuler une course
            document.querySelectorAll('.btn-annuler').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    confirmerAnnulationCourse(idReservation);
                });
            });
        }

        // Fonction pour vérifier si la date prévue est atteinte avant de débuter la course
        function verifierEtDebuterCourse(idReservation) {
            fetch(`api/verifier-date-depart.php?id_reservation=${idReservation}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (!data.date_atteinte) {
                            // La date prévue n'est pas encore atteinte, demander confirmation
                            const datePrevue = new Date(data.date_prevue).toLocaleString('fr-FR');
                            afficherConfirmation(
                                `La date de départ prévue (${datePrevue}) n'est pas encore atteinte. Voulez-vous quand même débuter cette course ?`,
                                () => debuterCourse(idReservation)
                            );
                        } else {
                            // La date est atteinte, débuter la course
                            debuterCourse(idReservation);
                        }
                    } else {
                        afficherNotification(data.message || 'Erreur lors de la vérification de la date', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    afficherNotification('Erreur de communication avec le serveur', 'danger');
                });
        }

        // Fonction pour débuter une course
        function debuterCourse(idReservation) {
            fetch('api/debuter-course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_reservation: idReservation,
                    kilometrage_depart: null // Sera demandé dans une modal
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        afficherKilometrageModal(idReservation, data.vehicule);
                    } else {
                        afficherNotification(data.message || 'Erreur lors du démarrage de la course', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    afficherNotification('Erreur de communication avec le serveur', 'danger');
                });
        }

        // Remplacer la fonction afficherKilometrageModal avec celle-ci:

function afficherKilometrageModal(idReservation, vehicule) {
    const kilometrageActuel = vehicule?.kilometrage_actuel || 0;

    const modalHtml = `
    <div class="modal fade" id="kilometrageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Informations de départ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formKilometrage">
                        <input type="hidden" name="id_reservation" value="${idReservation}">
                        <div class="mb-3">
                            <label class="form-label">Kilométrage actuel du véhicule: ${kilometrageActuel} km</label>
                            <input type="number" class="form-control" id="kilometrage_depart" name="kilometrage_depart" 
                                min="${kilometrageActuel}" value="${kilometrageActuel}" required>
                            <div class="form-text">
                                Le kilométrage de départ doit être supérieur ou égal au kilométrage actuel du véhicule.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Matériel emporté</label>
                            <textarea class="form-control" id="materiel" name="materiel" 
                                rows="3" placeholder="Listez le matériel emporté pour cette course"></textarea>
                            <div class="form-text">
                                Décrivez le matériel embarqué pour cette course (équipements, outils, etc.).
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Acteurs accompagnant le chauffeur</label>
                            <textarea class="form-control" id="acteurs" name="acteurs" 
                                rows="3" placeholder="Listez les personnes qui accompagnent le chauffeur (un nom par ligne)"></textarea>
                            <div class="form-text">
                                Renseignez les noms des personnes qui participent à la course (un par ligne).
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Confirmer et démarrer la course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    `;

    // Ajouter la modal au DOM
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.innerHTML = modalHtml;

    // Afficher la modal
    const modal = new bootstrap.Modal(document.getElementById('kilometrageModal'));
    modal.show();

    // Gérer la soumission du formulaire
    const formKilometrage = document.getElementById('formKilometrage');
    formKilometrage.addEventListener('submit', function (e) {
        e.preventDefault();

        const kilometrageDepart = parseInt(document.getElementById('kilometrage_depart').value);
        const materiel = document.getElementById('materiel').value;
        const acteurs = document.getElementById('acteurs').value;

        // Valider le kilométrage
        if (kilometrageDepart < kilometrageActuel) {
            afficherNotification('Le kilométrage de départ ne peut pas être inférieur au kilométrage actuel du véhicule', 'warning');
            return;
        }

        // Enregistrer le kilométrage et démarrer la course
        fetch('api/confirmer-debut-course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id_reservation: idReservation,
                kilometrage_depart: kilometrageDepart,
                materiel: materiel,
                acteurs: acteurs
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    afficherNotification('Course démarrée avec succès', 'success');
                    modal.hide();
                    chargerDeplacementsEnCours();

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } else {
                    afficherNotification(data.message || 'Erreur lors du démarrage de la course', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
    });
}
        // Fonction pour terminer une course
        function terminerCourse(idReservation) {
            // Récupérer les informations du véhicule pour le kilométrage
            fetch(`api/info-vehicule-reservation.php?id_reservation=${idReservation}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        afficherModalFinCourse(idReservation, data.vehicule);
                    } else {
                        afficherNotification(data.message || 'Erreur lors de la récupération des informations du véhicule', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    afficherNotification('Erreur de communication avec le serveur', 'danger');
                });
        }

        // Afficher la modal de fin de course
        // Remplacer la fonction afficherModalFinCourse avec celle-ci:

function afficherModalFinCourse(idReservation, vehicule) {
    const kilometrageDepart = vehicule.km_depart || 0;

    const modalHtml = `
    <div class="modal fade" id="finCourseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terminer la course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formFinCourse">
                        <input type="hidden" name="id_reservation" value="${idReservation}">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kilométrage de départ</label>
                                <input type="number" class="form-control" value="${kilometrageDepart}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kilométrage de retour *</label>
                                <input type="number" class="form-control" id="kilometrage_retour" name="kilometrage_retour" 
                                    min="${kilometrageDepart + 1}" required>
                                <div class="form-text">
                                    Le kilométrage de retour doit être supérieur au kilométrage de départ.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Matériel de retour</label>
                            <textarea class="form-control" name="materiel_retour" rows="3" 
                                placeholder="Listez le matériel rapporté à la fin de la course"></textarea>
                            <div class="form-text">
                                Indiquez tous les équipements et matériels rapportés à la fin de la mission.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Commentaires sur le trajet</label>
                            <textarea class="form-control" name="commentaires" rows="3" 
                                placeholder="Commentaires sur le déroulement de la course, incidents éventuels, etc."></textarea>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="notifier_fin" name="notifier_fin" checked>
                            <label class="form-check-label" for="notifier_fin">
                                Notifier le demandeur de la fin de la course
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">Confirmer la fin de la course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
`;

    // Ajouter la modal au DOM
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.innerHTML = modalHtml;

    // Afficher la modal
    const modal = new bootstrap.Modal(document.getElementById('finCourseModal'));
    modal.show();

    // Gérer la soumission du formulaire
    const formFinCourse = document.getElementById('formFinCourse');
    formFinCourse.addEventListener('submit', function (e) {
        e.preventDefault();

        const kilometrageRetour = parseInt(document.getElementById('kilometrage_retour').value);

        // Valider le kilométrage de retour
        if (kilometrageRetour <= kilometrageDepart) {
            afficherNotification('Le kilométrage de retour doit être supérieur au kilométrage de départ', 'warning');
            return;
        }

        // Récupérer les données du formulaire
        const formData = new FormData(formFinCourse);

        // Enregistrer la fin de la course
        fetch('api/terminer-course.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    afficherNotification('Course terminée avec succès', 'success');
                    modal.hide();
                    chargerDeplacementsEnCours();

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } else {
                    afficherNotification(data.message || 'Erreur lors de la finalisation de la course', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
    });
}

        // Fonction pour confirmer l'annulation d'une course
        function confirmerAnnulationCourse(idReservation) {
            afficherConfirmation(
                'Êtes-vous sûr de vouloir annuler cette course ? Cette action est irréversible.',
                () => afficherModalAnnulation(idReservation)
            );
        }

        // Afficher la modal d'annulation de course
        function afficherModalAnnulation(idReservation) {
            const modalHtml = `
            <div class="modal fade" id="annulationCourseModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Annulation de la course</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formAnnulationCourse">
                                <input type="hidden" name="id_reservation" value="${idReservation}">
                                
                                <div class="mb-3">
                                    <label class="form-label">Motif d'annulation *</label>
                                    <select class="form-select" name="motif_annulation" required>
                                        <option value="">Sélectionnez un motif</option>
                                        <option value="demandeur">Annulation par le demandeur</option>
                                        <option value="vehicule">Problème de véhicule</option>
                                        <option value="chauffeur">Indisponibilité du chauffeur</option>
                                        <option value="meteo">Conditions météorologiques</option>
                                        <option value="autre">Autre raison</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Détails du motif d'annulation *</label>
                                    <textarea class="form-control" name="details_annulation" rows="3" 
                                        placeholder="Précisez les raisons de l'annulation" required></textarea>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="notifier_annulation" name="notifier_annulation" checked>
                                    <label class="form-check-label" for="notifier_annulation">
                                        Notifier le demandeur de l'annulation
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-danger w-100">Confirmer l'annulation</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

            // Ajouter la modal au DOM
            const modalContainer = document.getElementById('modalContainer');
            modalContainer.innerHTML = modalHtml;

            // Afficher la modal
            const modal = new bootstrap.Modal(document.getElementById('annulationCourseModal'));
            modal.show();

            // Gérer la soumission du formulaire
            const formAnnulationCourse = document.getElementById('formAnnulationCourse');
            formAnnulationCourse.addEventListener('submit', function (e) {
                e.preventDefault();

                // Récupérer les données du formulaire
                const formData = new FormData(formAnnulationCourse);

                // Enregistrer l'annulation
                fetch('api/annuler-course.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            afficherNotification('Course annulée avec succès', 'success');
                            modal.hide();
                            chargerDeplacementsEnCours();

                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);


                        } else {
                            afficherNotification(data.message || 'Erreur lors de l\'annulation de la course', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        afficherNotification('Erreur de communication avec le serveur', 'danger');
                    });
            });
        }

        // Fonction utilitaire pour afficher des notifications
        function afficherNotification(message, type = 'info') {
            // Créer un conteneur de notifications s'il n'existe pas
            let notificationContainer = document.getElementById('notification-container');
            if (!notificationContainer) {
                notificationContainer = document.createElement('div');
                notificationContainer.id = 'notification-container';
                notificationContainer.className = 'position-fixed top-0 end-0 p-3 z-3';
                document.body.appendChild(notificationContainer);
            }

            // Créer l'élément de notification
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-bg-${type} border-0`;
            toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

            // Ajouter la notification
            notificationContainer.appendChild(toast);

            // Initialiser et montrer le toast avec Bootstrap
            const toastInstance = new bootstrap.Toast(toast);
            toastInstance.show();

            // Supprimer le toast après qu'il ait été caché
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Fonction pour afficher une boîte de dialogue de confirmation
        function afficherConfirmation(message, onConfirm) {
            const confirmationId = 'confirmationModal' + Date.now();
            const modalHtml = `
            <div class="modal fade" id="${confirmationId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary" id="btnConfirmer">Confirmer</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

            // Ajouter la modal au DOM
            const modalContainer = document.getElementById('modalContainer');
            modalContainer.innerHTML = modalHtml;

            // Afficher la modal
            const modal = new bootstrap.Modal(document.getElementById(confirmationId));
            modal.show();

            // Gérer la confirmation
            document.getElementById('btnConfirmer').addEventListener('click', () => {
                modal.hide();
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            });
        }

        // Écouteur d'événement pour l'onglet "Suivi"
        document.getElementById('tracking-tab').addEventListener('click', () => {
            chargerDeplacementsEnCours();
        });

        // Si l'onglet "Suivi" est déjà actif au chargement de la page, charger les données
        if (document.querySelector('#tracking-tab.active')) {
            chargerDeplacementsEnCours();
        }
    });
    // Ajoutez ce bloc à la fin du script existant
    document.addEventListener('DOMContentLoaded', function () {
        const userRole = '<?php echo $_SESSION['role']; ?>';

        if (userRole === 'validateur') {
            // Charger les déplacements en cours immédiatement pour les validateurs
            chargerDeplacementsEnCours();
        }
    });
</script>

<!-- historique -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tableaux pour l'historique des courses terminées et annulées
        const courseTermineeTable = document.querySelector('#courseTerminee .table');
        const courseAnnuleeTable = document.querySelector('#courseAnnulee .table');

        // Variables pour stocker les instances DataTable
        let dataTableTerminee;
        let dataTableAnnulee;

        // Fonction pour charger l'historique des déplacements
        function chargerHistorique(type) {
    fetch(`api/charger-historique.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            const table = type === 'terminee' ? courseTermineeTable : courseAnnuleeTable;
            const tbody = table.querySelector('tbody');
            tbody.innerHTML = ''; // Vider le tableau existant

            if (data.success && data.historique && data.historique.length > 0) {
                data.historique.forEach(course => {
                    const row = document.createElement('tr');
                    
                    if (type === 'terminee') {
                        row.innerHTML = `
                            <td>
                                <div class="card-img logo_marque_vehicule">
                                    <img src="uploads/vehicules/logo_marque/${course.logo_marque_vehicule}"
                                        class="img-fluid rounded-circle" alt="Logo du véhicule">
                                </div>
                            </td>
                            <td>
                                <p>${course.marque} - ${course.modele} | ${course.immatriculation}</p>
                            </td>
                            <td>${course.chauffeur_nom || '---'}</td>
                            <td>
                                <span class="badge bg-success" title="Date de départ effective">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    ${course.date_debut_effective_formatee || '---'}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success" title="Date de retour effective">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    ${course.date_retour_effective_formatee || '---'}
                                </span>
                            </td>
                            <td><span class="badge bg-success">${course.statut_libelle}</span></td>
                            <td>
                                <a href="view_detail_course_terminee_historique.php?id=${course.id_reservation}" 
                                   class="btn btn-info m-1" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                ${course.peut_supprimer ? `
                                <button class="btn btn-danger m-1 btn-supprimer-historique" 
                                        data-id="${course.id_reservation}" title="Effacer de l'historique">
                                    <i class="fas fa-trash"></i>
                                </button>
                                ` : ''}
                            </td>
                        `;
                            } else {
                                // Pour les courses annulées
                                row.innerHTML = `
                                <td>${course.date_demande_formatee || '---'}</td>
                                <td>${course.demandeur_nom || '---'}</td>
                                <td>
                                    <div class="card-img logo_marque_vehicule">
                                        <img src="uploads/vehicules/logo_marque/${course.logo_marque_vehicule}"
                                            class="img-fluid rounded-circle" alt="Logo du véhicule">
                                    </div>
                                    <p>${course.marque} - ${course.modele} | ${course.immatriculation}</p>
                                </td>
                                <td>${course.chauffeur_nom || '---'}</td>
                                <td>${course.point_depart || '---'} - ${course.point_arrivee || '---'}</td>
                                <td>${course.date_depart_formatee || '---'}</td>
                                <td>${course.date_retour_prevue_formatee || '---'}</td>
                                <td><span class="badge bg-danger">${course.statut_libelle}</span></td>
                                <td>
                                    <a href="view_detail_course_terminee_historique.php?id=${course.id_reservation}" class="btn btn-info m-1" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    ${course.peut_supprimer ? `
                                    <button class="btn btn-danger m-1 btn-supprimer-historique" data-id="${course.id_reservation}" title="Effacer de l'historique">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    ` : ''}
                                </td>
                            `;
                            }
                            tbody.appendChild(row);
                        });

                        // Initialiser ou détruire/réinitialiser DataTable
                        if (type === 'terminee') {
                            if (dataTableTerminee) {
                                dataTableTerminee.destroy();
                            }
                            dataTableTerminee = $(table).DataTable({
                                responsive: true,
                                language: {
                                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
                                },
                                dom: 'Bfrtip',
                                buttons: [
                                    'copy', 'csv', 'print'
                                ],
                                order: [[5, 'desc']] // Tri par date de départ (colonne 5) décroissant
                            });
                        } else {
                            if (dataTableAnnulee) {
                                dataTableAnnulee.destroy();
                            }
                            dataTableAnnulee = $(table).DataTable({
                                responsive: true,
                                language: {
                                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
                                },
                                dom: 'Bfrtip',
                                buttons: [
                                    'copy', 'csv', 'print'
                                ],
                                order: [[0, 'desc']] // Tri par date de demande (colonne 0) décroissant
                            });
                        }

                        // Ajouter des écouteurs d'événements pour la suppression
                        ajouterEcouteursSuppressionHistorique();
                    } else {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td colspan="9" class="text-center">Aucun déplacement trouvé dans l'historique</td>`;
                        tbody.appendChild(row);

                        // Initialiser quand même DataTable pour l'interface cohérente
                        if (type === 'terminee') {
                            if (dataTableTerminee) {
                                dataTableTerminee.destroy();
                            }
                            dataTableTerminee = $(table).DataTable({
                                language: {
                                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
                                },
                                dom: 'Bfrtip',
                                buttons: [
                                    'copy', 'csv', 'print'
                                ]
                            });
                        } else {
                            if (dataTableAnnulee) {
                                dataTableAnnulee.destroy();
                            }
                            dataTableAnnulee = $(table).DataTable({
                                language: {
                                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
                                },
                                dom: 'Bfrtip',
                                buttons: [
                                    'copy', 'csv', 'print'
                                ]
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error(`Erreur lors du chargement de l'historique (${type}):`, error);
                    const table = type === 'terminee' ? courseTermineeTable : courseAnnuleeTable;
                    const tbody = table.querySelector('tbody');
                    tbody.innerHTML = ''; // Vider le tableau existant

                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="9" class="text-center text-danger">Erreur de chargement des données</td>`;
                    tbody.appendChild(row);

                    // Initialiser quand même DataTable pour l'interface cohérente
                    if (type === 'terminee') {
                        if (dataTableTerminee) {
                            dataTableTerminee.destroy();
                        }
                        dataTableTerminee = $(table).DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
                            }
                        });
                    } else {
                        if (dataTableAnnulee) {
                            dataTableAnnulee.destroy();
                        }
                        dataTableAnnulee = $(table).DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
                            }
                        });
                    }
                });
        }

        // Ajouter des écouteurs pour la suppression de l'historique
        function ajouterEcouteursSuppressionHistorique() {
            document.querySelectorAll('.btn-supprimer-historique').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idReservation = this.dataset.id;
                    confirmerSuppressionHistorique(idReservation);
                });
            });
        }

        // Confirmer la suppression d'une entrée de l'historique
        function confirmerSuppressionHistorique(idReservation) {
            afficherConfirmation(
                'Êtes-vous sûr de vouloir supprimer cette entrée de l\'historique ? Cette action est irréversible.',
                () => supprimerEntreeHistorique(idReservation)
            );
        }

        // Supprimer une entrée de l'historique
        function supprimerEntreeHistorique(idReservation) {
            fetch('api/supprimer-historique.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_reservation: idReservation
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        afficherNotification('Entrée supprimée de l\'historique avec succès', 'success');
                        // Recharger les deux onglets de l'historique
                        chargerHistorique('terminee');
                        chargerHistorique('annulee');
                    } else {
                        afficherNotification(data.message || 'Erreur lors de la suppression', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    afficherNotification('Erreur de communication avec le serveur', 'danger');
                });
        }

        // Fonction utilitaire pour afficher des notifications
        function afficherNotification(message, type = 'info') {
            // Créer un conteneur de notifications s'il n'existe pas
            let notificationContainer = document.getElementById('notification-container');
            if (!notificationContainer) {
                notificationContainer = document.createElement('div');
                notificationContainer.id = 'notification-container';
                notificationContainer.className = 'position-fixed top-0 end-0 p-3 z-3';
                document.body.appendChild(notificationContainer);
            }
            // Créer l'élément de notification
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-bg-${type} border-0`;
            toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
            // Ajouter la notification
            notificationContainer.appendChild(toast);
            // Initialiser et montrer le toast avec Bootstrap
            const toastInstance = new bootstrap.Toast(toast);
            toastInstance.show();
            // Supprimer le toast après qu'il ait été caché
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Fonction pour afficher une boîte de dialogue de confirmation
        function afficherConfirmation(message, onConfirm) {
            const confirmationId = 'confirmationModal' + Date.now();
            const modalHtml = `
            <div class="modal fade" id="${confirmationId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary" id="btnConfirmer">Confirmer</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
            // Ajouter la modal au DOM
            const modalContainer = document.createElement('div');
            modalContainer.id = 'modalContainer';
            document.body.appendChild(modalContainer);
            modalContainer.innerHTML = modalHtml;

            // Afficher la modal
            const modal = new bootstrap.Modal(document.getElementById(confirmationId));
            modal.show();

            // Gérer la confirmation
            document.getElementById('btnConfirmer').addEventListener('click', () => {
                modal.hide();
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
                // Suppression de la modal du DOM après sa fermeture
                document.getElementById(confirmationId).addEventListener('hidden.bs.modal', function () {
                    modalContainer.remove();
                });
            });

            // Suppression de la modal du DOM si annulation
            document.querySelector(`#${confirmationId} .btn-close, #${confirmationId} .btn-secondary`).addEventListener('click', function () {
                document.getElementById(confirmationId).addEventListener('hidden.bs.modal', function () {
                    modalContainer.remove();
                });
            });
        }

        // Écouteurs d'événement pour les onglets d'historique
        document.getElementById('courseTerminee-tab').addEventListener('click', () => {
            chargerHistorique('terminee');
        });

        document.getElementById('courseAnnulee-tab').addEventListener('click', () => {
            chargerHistorique('annulee');
        });

        // Écouteur d'événement pour l'onglet principal "Historique"
        document.getElementById('historique-tab').addEventListener('click', () => {
            // Charger les deux types d'historique
            chargerHistorique('terminee');
        });

        // Si l'onglet "Historique" est déjà actif au chargement de la page, charger les données
        if (document.querySelector('#historique-tab.active')) {
            chargerHistorique('terminee');
        }
    });
</script>

<!-- modifier demande -->
<script>
    // Fonction pour ouvrir la modal de modification de demande
    function ouvrirModalModificationDemande(idReservation) {
        // Récupérer les détails de la réservation
        fetch(`api/details-reservation.php?id=${idReservation}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const reservation = data.reservation;

                    // Créer et afficher une modal Bootstrap dynamiquement
                    const modalHtml = `
                <div class="modal fade" id="modificationDemandeModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier la demande de réservation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formModificationDemande">
                                    <input type="hidden" name="id_reservation" value="${idReservation}">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date de départ *</label>
                                            <input type="datetime-local" class="form-control" name="date_depart" 
                                                value="${formatDateForInput(reservation.date_depart)}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date de retour prévue *</label>
                                            <input type="datetime-local" class="form-control" name="date_retour_prevue" 
                                                value="${formatDateForInput(reservation.date_retour_prevue)}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombre de passagers *</label>
                                            <input type="number" class="form-control" name="nombre_passagers" 
                                                value="${reservation.nombre_passagers}" min="1" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Véhicule</label>
                                            <select class="form-select" name="id_vehicule" id="vehiculeModification">
                                                <option value="">Chargement des véhicules...</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Itinéraire</label>
                                            <div class="input-group">
                                                <span class="input-group-text">De</span>
                                                <input type="text" class="form-control" 
                                                    value="${reservation.itineraire.point_depart || ''}" readonly>
                                                <span class="input-group-text">À</span>
                                                <input type="text" class="form-control" 
                                                    value="${reservation.itineraire.point_arrivee || ''}" readonly>
                                            </div>
                                            <div class="form-text">L'itinéraire ne peut pas être modifié directement. Pour le changer, veuillez annuler et créer une nouvelle demande.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Statut actuel</label>
                                            <input type="text" class="form-control" 
                                                value="${getStatusLabel(reservation.statut)}" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Motif de modification *</label>
                                        <textarea class="form-control" name="motif_modification" rows="3" 
                                            placeholder="Expliquez brièvement les raisons de la modification" required></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">Enregistrer les modifications</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                    // Ajouter la modal au DOM
                    const modalContainer = document.getElementById('modalContainer');
                    modalContainer.innerHTML = modalHtml;

                    // Charger les véhicules disponibles
                    fetch(`api/vehicules-disponibles.php?id_reservation=${idReservation}`)
                        .then(response => response.json())
                        .then(data => {
                            const vehiculeSelect = document.getElementById('vehiculeModification');
                            vehiculeSelect.innerHTML = '<option value="">Sélectionnez un véhicule</option>';

                            if (data.success && data.vehicules && data.vehicules.length > 0) {
                                data.vehicules.forEach(vehicule => {
                                    const option = document.createElement('option');
                                    option.value = vehicule.id_vehicule;
                                    option.textContent = `${vehicule.marque} ${vehicule.modele} (${vehicule.immatriculation})`;

                                    // Sélectionner le véhicule actuel
                                    if (vehicule.id_vehicule === reservation.id_vehicule) {
                                        option.selected = true;
                                    }

                                    vehiculeSelect.appendChild(option);
                                });
                            } else {
                                vehiculeSelect.innerHTML = '<option value="">Aucun véhicule disponible</option>';
                            }
                        })
                        .catch(error => {
                            console.error('Erreur lors du chargement des véhicules:', error);
                        });

                    // Afficher la modal
                    const modal = new bootstrap.Modal(document.getElementById('modificationDemandeModal'));
                    modal.show();

                    // Gérer la soumission du formulaire de modification
                    const formModificationDemande = document.getElementById('formModificationDemande');
                    formModificationDemande.addEventListener('submit', function (e) {
                        e.preventDefault();

                        const formData = new FormData(formModificationDemande);

                        fetch('api/modifier-demande.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    afficherNotification('Demande modifiée avec succès', 'success');
                                    modal.hide();
                                    chargerDemandes(recupererFiltres());

                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);

                                } else {
                                    afficherNotification(data.message || 'Erreur lors de la modification', 'danger');
                                }
                            })
                            .catch(error => {
                                console.error('Erreur:', error);
                                afficherNotification('Erreur de communication avec le serveur', 'danger');
                            });
                    });
                } else {
                    afficherNotification(data.message || 'Impossible de charger les détails de la réservation', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
    }

    // Fonction utilitaire pour formater les dates pour les inputs datetime-local
    function formatDateForInput(dateString) {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Fonction pour obtenir un libellé de statut plus lisible
    function getStatusLabel(status) {
        const statusMap = {
            'en_attente': 'En attente de validation',
            'validee': 'Validée',
            'en_cours': 'En cours',
            'terminee': 'Terminée',
            'annulee': 'Annulée'
        };

        return statusMap[status] || status;
    }
</script>

<!-- forcer affichage calendrier -->
<script>
    // Fonction améliorée pour forcer le chargement du calendrier
    function forceCalendarRender() {
        // Vérifier si l'élément du calendrier existe
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        // Vérifier si l'objet calendar est défini
        if (typeof calendar !== 'undefined') {
            // Forcer le rafraîchissement en plusieurs étapes

            // 1. Ajuster la taille de la fenêtre
            window.dispatchEvent(new Event('resize'));

            // 2. Actualiser les événements
            calendar.refetchEvents();

            // 3. Forcer un rerendu complet
            calendar.updateSize();

            console.log('Calendrier rafraîchi avec succès');
        } else {
            console.log('L\'objet calendar n\'est pas défini');

            // Initialiser le calendrier s'il n'existe pas
            initializeCalendar();
        }
    }

    // Fonction pour initialiser le calendrier si nécessaire
    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        // Créer ou recréer l'objet calendrier
        window.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            // Autres options...
            events: function (fetchInfo, successCallback, failureCallback) {
                fetch(`api/charger-evenements-calendrier.php?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            failureCallback(new Error(data.message));
                        } else {
                     successCallback(data);
                        }
                    })
                    .catch(error => {
                        failureCallback(error);
                    });
            },
            // Autres options...
        });

        window.calendar.render();
        console.log('Calendrier initialisé');
    }

    // Ajouter un écouteur d'événement MutationObserver pour surveiller les changements d'onglet
    document.addEventListener('DOMContentLoaded', function () {
        // Récupérer l'onglet calendrier
        const calendarTab = document.getElementById('calendar-tab');
        const calendarPane = document.getElementById('calendar');

        if (!calendarTab || !calendarPane) return;

        // Observer les changements de classe sur l'onglet calendrier
        const tabObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.attributeName === 'class') {
                    const isActive = calendarTab.classList.contains('active');
                    if (isActive) {
                        console.log('Onglet calendrier actif détecté');
                        // Exécuter plusieurs fois avec des délais différents pour s'assurer du fonctionnement
                        setTimeout(forceCalendarRender, 10);
                        setTimeout(forceCalendarRender, 100);
                        setTimeout(forceCalendarRender, 500);
                    }
                }
            });
        });

        // Observer le panneau du calendrier
        const paneObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.attributeName === 'class') {
                    const isActive = calendarPane.classList.contains('active');
                    if (isActive) {
                        console.log('Panneau calendrier actif détecté');
                        // Exécuter plusieurs fois avec des délais différents
                        setTimeout(forceCalendarRender, 10);
                        setTimeout(forceCalendarRender, 100);
                        setTimeout(forceCalendarRender, 500);
                    }
                }
            });
        });

        // Configurer les observations
        tabObserver.observe(calendarTab, { attributes: true });
        paneObserver.observe(calendarPane, { attributes: true });

        // Forcer le chargement immédiat si l'onglet calendrier est déjà actif au chargement
        if (calendarTab.classList.contains('active')) {
            console.log('Onglet calendrier actif au chargement');
            initializeCalendar();
            setTimeout(forceCalendarRender, 100);
        }

        // Ajouter un écouteur d'événement au clic sur l'onglet calendrier
        calendarTab.addEventListener('click', function () {
            console.log('Clic sur l\'onglet calendrier');
            setTimeout(forceCalendarRender, 100);
        });
    });

    // Exécuter le rendu forcé lorsque la page est complètement chargée
    window.addEventListener('load', function () {
        if (document.querySelector('#calendar-tab.active')) {
            console.log('Rendu forcé au chargement complet de la page');
            setTimeout(forceCalendarRender, 100);
            setTimeout(forceCalendarRender, 500);
        }
    });
</script>

<!-- // Ajouter ce script à la fin du fichier planification.php -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Vérifier si on doit activer l'onglet suivi
        const urlParams = new URLSearchParams(window.location.search);
        const tabToShow = urlParams.get('tab');

        if (tabToShow === 'tracking') {
            // Désactiver tous les onglets
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
                const tabPane = document.querySelector(tab.getAttribute('data-bs-target'));
                if (tabPane) {
                    tabPane.classList.remove('show', 'active');
                }
            });

            // Activer l'onglet suivi
            const trackingTab = document.getElementById('tracking-tab');
            const trackingPane = document.getElementById('tracking');

            if (trackingTab && trackingPane) {
                trackingTab.classList.add('active');
                trackingPane.classList.add('show', 'active');
            }
        }

        if (tabToShow === 'validation') {
            // Désactiver tous les onglets
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
                const tabPane = document.querySelector(tab.getAttribute('data-bs-target'));
                if (tabPane) {
                    tabPane.classList.remove('show', 'active');
                }
            });

            // Activer l'onglet suivi
            const validationTab = document.getElementById('validation-tab');
            const validationPane = document.getElementById('validation');

            if (validationTab && validationPane) {
                validationTab.classList.add('active');
                validationPane.classList.add('show', 'active');
            }
        }


    });

</script>

<script>
    document.body.classList.add('initializing');

    document.addEventListener('DOMContentLoaded', function () {
        const userRole = '<?php echo $_SESSION['role']; ?>';

        if (userRole === 'validateur') {
            // Utilisation de l'API Bootstrap pour garantir l'activation du bon onglet
            const trackingTab = document.getElementById('calendar-tab');
            if (trackingTab) {
                const trackingTabInstance = new bootstrap.Tab(trackingTab);
                trackingTabInstance.show();
            }
        }

        // Retirer la classe d'initialisation
        document.body.classList.remove('initializing');
    });
</script>

<!-- // Fonction pour afficher les détails d'une réservation -->
<script>
    // Fonction pour afficher les détails d'une réservation
    function afficherDetailsReservation(idReservation) {
        // Récupérer les détails de la réservation via l'API
        fetch(`api/details-reservation.php?id=${idReservation}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const reservation = data.reservation;

                    // Formater les dates pour l'affichage
                    const dateDemandeFormatee = new Date(reservation.date_demande).toLocaleString('fr-FR');
                    const dateDepartFormatee = new Date(reservation.date_depart).toLocaleString('fr-FR');
                    const dateRetourPrevueFormatee = new Date(reservation.date_retour_prevue).toLocaleString('fr-FR');

                    // Construire le contenu de la modal
                    const modalHtml = `
                <div class="modal fade" id="detailsReservationModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-info-circle me-2"></i>Détails de la demande #${idReservation}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Informations générales</h6>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Statut :</strong> <span class="badge ${getStatusBadgeClass(reservation.statut)}">${reservation.statut_libelle || reservation.statut}</span></p>
                                                <p><strong>Demandeur :</strong> ${reservation.demandeur || '---'}</p>
                                                <p><strong>Date de demande :</strong> ${dateDemandeFormatee}</p>
                                                <p><strong>Nombre de passagers :</strong> ${reservation.nombre_passagers}</p>
                                                <p><strong>Priorité :</strong> ${getPrioriteLabel(reservation.priorite)}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Horaires</h6>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Date de départ prévue :</strong> ${dateDepartFormatee}</p>
                                                <p><strong>Date de retour prévue :</strong> ${dateRetourPrevueFormatee}</p>
                                                ${reservation.date_debut_effective ?
                            `<p><strong>Date de départ effective :</strong> ${new Date(reservation.date_debut_effective).toLocaleString('fr-FR')}</p>` :
                            ''}
                                                ${reservation.date_retour_effective ?
                            `<p><strong>Date de retour effective :</strong> ${new Date(reservation.date_retour_effective).toLocaleString('fr-FR')}</p>` :
                            ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Véhicule</h6>
                                            </div>
                                            <div class="card-body">
                ${reservation.vehicule && reservation.vehicule.id ?
                            `<div class="d-flex align-items-center mb-2">
                ${reservation.vehicule.logo_marque_vehicule ?
                                `<div class="me-3">
                <img src="uploads/vehicules/logo_marque/${reservation.vehicule.logo_marque_vehicule}" class="img-fluid rounded-circle" style="width: 50px;" alt="Logo véhicule">
            </div>` :
                                ''}
        <div>
            <p class="mb-0"><strong>${reservation.vehicule.marque || ''} ${reservation.vehicule.modele || ''}</strong></p>
            <p class="mb-0 text-muted">${reservation.vehicule.immatriculation || ''}</p>
        </div>
    </div>
    <p><strong>Type :</strong> ${reservation.vehicule.type || '---'}</p>
    <p><strong>Capacité :</strong> ${reservation.vehicule.capacite || '---'} passagers</p>` :
                            '<p class="text-muted">Aucun véhicule assigné</p>'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Chauffeur</h6>
                                            </div>
                                            <div class="card-body">
                                                ${reservation.chauffeur_nom ?
                            `<p><strong>Nom :</strong> ${reservation.chauffeur_nom || '---'}</p>
                                                <p><strong>Téléphone :</strong> ${reservation.chauffeur_telephone || '---'}</p>
                                                <p><strong>Email :</strong> ${reservation.chauffeur_email || '---'}</p>` :
                            '<p class="text-muted">Aucun chauffeur assigné</p>'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Trajet</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="bg-primary text-white rounded-circle p-2 me-2"><i class="fas fa-map-marker-alt"></i></span>
                                                    <strong>Point de départ :</strong> ${reservation.itineraire?.point_depart || '---'}
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <span class="bg-success text-white rounded-circle p-2 me-2"><i class="fas fa-flag-checkered"></i></span>
                                                    <strong>Point d'arrivée :</strong> ${reservation.itineraire?.point_arrivee || '---'}
                                                </div>
                                            </div>
                                            <div class="ms-3 text-center">
                                                <p class="mb-0"><i class="fas fa-road me-1"></i> <strong>${reservation.itineraire?.distance_prevue || '---'}</strong> km</p>
                                                <p class="mb-0"><i class="fas fa-clock me-1"></i> <strong>${reservation.itineraire?.temps_trajet_prevu || '---'}</strong> min</p>
                                            </div>
                                        </div>
                                        ${reservation.itineraire?.points_intermediaires ?
                            `<div class="mt-2">
                                            <strong>Points intermédiaires :</strong>
                                            <p>${reservation.itineraire.points_intermediaires}</p>
                                        </div>` :
                            ''}
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Informations complémentaires</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Objet de la demande :</strong> ${reservation.objet_demande || '---'}</p>
                                        ${reservation.materiel ?
                            `<p><strong>Matériel emporté :</strong> ${reservation.materiel}</p>` :
                            ''}
                                        ${reservation.note ?
                            `<p><strong>Notes :</strong> ${reservation.note}</p>` :
                            ''}
                                        ${(reservation.km_depart && reservation.km_retour) ?
                            `<div class="mt-3 border-top pt-3">
                                            <h6>Kilométrage</h6>
                                            <p><strong>Kilométrage au départ :</strong> ${reservation.km_depart} km</p>
                                            <p><strong>Kilométrage au retour :</strong> ${reservation.km_retour} km</p>
                                            <p><strong>Distance parcourue :</strong> ${reservation.km_retour - reservation.km_depart} km</p>
                                        </div>` :
                            ''}
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                ${reservation.statut === 'en_attente' ?
                            `
                                ` :
                            ''}
                            </div>
                        </div>
                    </div>
                </div>
                `;

                    // Ajouter la modal au DOM
                    const modalContainer = document.getElementById('modalContainer');
                    modalContainer.innerHTML = modalHtml;

                    // Initialiser et afficher la modal
                    const modal = new bootstrap.Modal(document.getElementById('detailsReservationModal'));
                    modal.show();

                    // Ajouter des écouteurs d'événements aux boutons de la modal
                    if (reservation.statut === 'en_attente') {
                        document.querySelector('.btn-accepter-modal')?.addEventListener('click', function () {
                            modal.hide();
                            traiterDemande(idReservation, 'valider');
                        });

                        document.querySelector('.btn-refuser-modal')?.addEventListener('click', function () {
                            modal.hide();
                            traiterDemande(idReservation, 'refuser');
                        });
                    }
                } else {
                    afficherNotification(data.message || 'Impossible de charger les détails de la réservation', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
    }

    // Fonction pour obtenir la classe de badge appropriée en fonction du statut
    function getStatusBadgeClass(statut) {
        switch (statut) {
            case 'en_attente':
                return 'bg-warning';
            case 'validee':
                return 'bg-success';
            case 'en_cours':
                return 'bg-info';
            case 'terminee':
                return 'bg-secondary';
            case 'annulee':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }

    // Fonction pour obtenir le libellé de priorité
    function getPrioriteLabel(priorite) {
        switch (parseInt(priorite)) {
            case 4:
                return '<span class="badge bg-danger">Critique</span>';
            case 3:
                return '<span class="badge bg-warning">Haute</span>';
            case 2:
                return '<span class="badge bg-info">Moyenne</span>';
            case 1:
                return '<span class="badge bg-secondary">Normale</span>';
            default:
                return '<span class="badge bg-secondary">Non définie</span>';
        }
    }
</script>

<!-- Script pour la fonctionnalité Vue Détail -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour afficher les détails d'une réservation dans l'onglet suivi
    window.afficherDetailsSuivi = function(idReservation) {
        // Récupérer les détails de la réservation via l'API
        fetch(`api/details-reservation.php?id=${idReservation}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const reservation = data.reservation;

                    // Formater les dates pour l'affichage
                    const dateDepartFormatee = new Date(reservation.date_depart).toLocaleString('fr-FR');
                    const dateRetourPrevueFormatee = new Date(reservation.date_retour_prevue).toLocaleString('fr-FR');

                    // Construire le contenu de la modal
                    const modalHtml = `
                    <div class="modal fade" id="detailsSuiviModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-route me-2"></i>Détails du trajet
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Carte du trajet -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Trajet</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <span class="bg-primary text-white rounded-circle p-2 me-2"><i class="fas fa-map-marker-alt"></i></span>
                                                        <strong>Point de départ :</strong> ${reservation.itineraire?.point_depart || '---'}
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <span class="bg-success text-white rounded-circle p-2 me-2"><i class="fas fa-flag-checkered"></i></span>
                                                        <strong>Point d'arrivée :</strong> ${reservation.itineraire?.point_arrivee || '---'}
                                                    </div>
                                                </div>
                                                <div class="ms-3 text-center">
                                                    <p class="mb-0"><i class="fas fa-road me-1"></i> <strong>${reservation.itineraire?.distance_prevue || '---'}</strong> km</p>
                                                    <p class="mb-0"><i class="fas fa-clock me-1"></i> <strong>${reservation.itineraire?.temps_trajet_prevu || '---'}</strong> min</p>
                                                </div>
                                            </div>
                                            ${reservation.itineraire?.points_intermediaires ?
                        `<div class="mt-2">
                                                <strong>Points intermédiaires :</strong>
                                                <p>${reservation.itineraire.points_intermediaires}</p>
                                            </div>` :
                        ''}
                                        </div>
                                    </div>
                                    
                                    <!-- Objet de la demande et informations complémentaires -->
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Informations de la demande</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Demandeur :</strong> ${reservation.demandeur || '---'}</p>
                                                    <p><strong>Nombre de passagers :</strong> ${reservation.nombre_passagers}</p>
                                                    <p><strong>Départ :</strong> ${dateDepartFormatee}</p>
                                                    <p><strong>Retour prévu :</strong> ${dateRetourPrevueFormatee}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Objet de la demande :</strong></p>
                                                    <div class="p-2 bg-light rounded mb-3">
                                                        ${reservation.objet_demande || '---'}
                                                    </div>
                                                    ${reservation.materiel ?
                            `<p><strong>Matériel emporté :</strong></p>
                                                        <div class="p-2 bg-light rounded">
                                                            ${reservation.materiel}
                                                        </div>` :
                            ''}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;

                    // Ajouter la modal au DOM
                    const modalContainer = document.getElementById('modalContainer');
                    modalContainer.innerHTML = modalHtml;

                    // Initialiser et afficher la modal
                    const modal = new bootstrap.Modal(document.getElementById('detailsSuiviModal'));
                    modal.show();
                } else {
                    afficherNotification(data.message || 'Impossible de charger les détails de la réservation', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
    };

    // Fonction pour ajouter les écouteurs d'événements pour les boutons de vue détaillée
    function ajouterEcouteursVueDetail() {
        document.querySelectorAll('.btn-vue-detail').forEach(btn => {
            btn.addEventListener('click', function() {
                const idReservation = this.dataset.id;
                afficherDetailsSuivi(idReservation);
            });
        });
    }

    // Si la fonction chargerDeplacementsEnCours existe déjà, la sauvegarder
    const originalChargerDeplacementsEnCours = window.chargerDeplacementsEnCours || null;

    // Définir ou redéfinir la fonction chargerDeplacementsEnCours
    window.chargerDeplacementsEnCours = function() {
        // Si la fonction originale existe, l'appeler d'abord
        if (originalChargerDeplacementsEnCours && typeof originalChargerDeplacementsEnCours === 'function') {
            originalChargerDeplacementsEnCours();
            // Ajouter les écouteurs après que la fonction originale soit terminée
            setTimeout(ajouterEcouteursVueDetail, 500);
            return;
        }

        // Sinon, implémenter la fonction complète
        fetch('api/charger-deplacements-en-cours.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#tracking .table tbody');
                tbody.innerHTML = ''; // Vider le tableau existant

                if (data.success && data.deplacements && data.deplacements.length > 0) {
                    data.deplacements.forEach(deplacement => {
                        const row = document.createElement('tr');

                        // Formater les dates
                        const dateDepart = new Date(deplacement.date_depart).toLocaleString('fr-FR');
                        const dateRetourPrevue = new Date(deplacement.date_retour_prevue).toLocaleString('fr-FR');

                        // Déterminer le statut et les boutons à afficher
                        let statutHtml = '';
                        let boutonsHtml = '';

                        switch (deplacement.statut) {
                            case 'validee':
                                statutHtml = '<span class="badge bg-success">Validée</span>';
                                boutonsHtml = `
                                <button class="btn btn-info btn-sm btn-vue-detail m-1" data-id="${deplacement.id_reservation}" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-primary btn-debuter" data-id="${deplacement.id_reservation}" title="Débuter la course">
                                    <i class="fas fa-play"></i>
                                </button>`;

                                // Ajouter le bouton d'annulation seulement pour admin et gestionnaire
                                if ('<?php echo $_SESSION['role']; ?>' === 'administrateur' || '<?php echo $_SESSION['role']; ?>' === 'gestionnaire') {
                                    boutonsHtml += `
                                    <button class="btn btn-danger btn-annuler" data-id="${deplacement.id_reservation}" title="Annuler la course">
                                        <i class="fas fa-times"></i>
                                    </button>`;
                                }
                                break;
                            case 'en_cours':
                                statutHtml = '<span class="badge bg-info">En cours</span>';
                                boutonsHtml = `
                                <button class="btn btn-info btn-sm btn-vue-detail m-1" data-id="${deplacement.id_reservation}" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-success btn-terminer" data-id="${deplacement.id_reservation}" title="Terminer la course">
                                    <i class="fas fa-check-circle"></i>
                                </button>`;

                                // Ajouter le bouton d'annulation seulement pour admin et gestionnaire
                                if ('<?php echo $_SESSION['role']; ?>' === 'administrateur' || '<?php echo $_SESSION['role']; ?>' === 'gestionnaire') {
                                    boutonsHtml += `
                                    <button class="btn btn-danger btn-annuler" data-id="${deplacement.id_reservation}" title="Annuler la course">
                                        <i class="fas fa-times"></i>
                                    </button>`;
                                }
                                break;
                        }
                        // Construire la ligne du tableau
                        row.innerHTML = `
                        <td>
                            <div class="card-img logo_marque_vehicule">
                                <img src="uploads/vehicules/logo_marque/${deplacement.logo_marque_vehicule || 'default.png'}"
                                    class="img-fluid rounded-circle" alt="Logo du véhicule">
                            </div>
                        </td>
                        <td>${deplacement.marque} - ${deplacement.modele} | ${deplacement.immatriculation}</td>
                        <td>${deplacement.chauffeur_nom || '---'}</td>
                        <td>${deplacement.point_depart} - ${deplacement.point_arrivee}</td>
                        <td>${dateDepart}</td>
                        <td>${dateRetourPrevue}</td>
                        <td>${statutHtml}</td>
                        <td>
                            ${deplacement.materiel ?
                            `<button class="btn btn-secondary btn-sm mb-1" type="button" data-bs-toggle="tooltip" title="${deplacement.materiel}">
                                <i class="fas fa-box"></i> Matériel
                            </button><br>` : ''}
                            ${boutonsHtml}
                        </td>
                    `;

                        tbody.appendChild(row);
                    });

                    // Ajouter les écouteurs d'événements pour les boutons
                    if (window.ajouterEcouteursDeplacements && typeof window.ajouterEcouteursDeplacements === 'function') {
                        window.ajouterEcouteursDeplacements();
                    }
                    ajouterEcouteursVueDetail();
                } else {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="8" class="text-center">Aucun déplacement en cours</td>`;
                    tbody.appendChild(row);
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des déplacements:', error);
                const tbody = document.querySelector('#tracking .table tbody');
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="8" class="text-center text-danger">Erreur de chargement des données</td>`;
                tbody.appendChild(row);
            });
    };

    // Ajouter un écouteur d'événement pour le chargement des déplacements lors du clic sur l'onglet Suivi
    document.getElementById('tracking-tab')?.addEventListener('click', function() {
        // Attendre un peu pour que l'onglet soit affiché avant de charger les données
        setTimeout(function() {
            chargerDeplacementsEnCours();
        }, 100);
    });

    // Si l'onglet Suivi est déjà actif au chargement de la page, charger les déplacements
    if (document.querySelector('#tracking-tab.active')) {
        setTimeout(function() {
            chargerDeplacementsEnCours();
        }, 500);
    }
});
</script>

<script>
// Script de correction pour les fonctionnalités de suivi
document.addEventListener('DOMContentLoaded', function() {
    // Rendre globales les fonctions essentielles
    window.ajouterEcouteursDeplacements = function() {
        console.log('Ajout des écouteurs pour les boutons de déplacements');
        
        // Débuter une course
        document.querySelectorAll('.btn-debuter').forEach(btn => {
            console.log('Bouton débuter trouvé:', btn);
            btn.addEventListener('click', function() {
                const idReservation = this.dataset.id;
                console.log('Démarrage de la course ID:', idReservation);
                window.verifierEtDebuterCourse(idReservation);
            });
        });

        // Terminer une course
        document.querySelectorAll('.btn-terminer').forEach(btn => {
            console.log('Bouton terminer trouvé:', btn);
            btn.addEventListener('click', function() {
                const idReservation = this.dataset.id;
                console.log('Terminer la course ID:', idReservation);
                window.terminerCourse(idReservation);
            });
        });

        // Annuler une course
        document.querySelectorAll('.btn-annuler').forEach(btn => {
            console.log('Bouton annuler trouvé:', btn);
            btn.addEventListener('click', function() {
                const idReservation = this.dataset.id;
                console.log('Annulation de la course ID:', idReservation);
                window.confirmerAnnulationCourse(idReservation);
            });
        });
    };

    // Définir les fonctions de traitement au niveau global
    window.verifierEtDebuterCourse = function(idReservation) {
        fetch(`api/verifier-date-depart.php?id_reservation=${idReservation}`)
            .then(response => response.json())
            .then(data => {
                console.log('Vérification de la date:', data);
                if (data.success) {
                    if (!data.date_atteinte) {
                        // La date prévue n'est pas encore atteinte, demander confirmation
                        const datePrevue = new Date(data.date_prevue).toLocaleString('fr-FR');
                        window.afficherConfirmation(
                            `La date de départ prévue (${datePrevue}) n'est pas encore atteinte. Voulez-vous quand même débuter cette course ?`,
                            () => window.debuterCourse(idReservation)
                        );
                    } else {
                        // La date est atteinte, débuter la course
                        window.debuterCourse(idReservation);
                    }
                } else {
                    window.afficherNotification(data.message || 'Erreur lors de la vérification de la date', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                window.afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
    };

    window.debuterCourse = function(idReservation) {
        fetch('api/debuter-course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id_reservation: idReservation,
                kilometrage_depart: null
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Réponse débuter course:', data);
            if (data.success) {
                window.afficherKilometrageModal(idReservation, data.vehicule);
            } else {
                window.afficherNotification(data.message || 'Erreur lors du démarrage de la course', 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            window.afficherNotification('Erreur de communication avec le serveur', 'danger');
        });
    };

    window.terminerCourse = function(idReservation) {
        fetch(`api/info-vehicule-reservation.php?id_reservation=${idReservation}`)
            .then(response => response.json())
            .then(data => {
                console.log('Info véhicule pour terminer:', data);
                if (data.success) {
                    window.afficherModalFinCourse(idReservation, data.vehicule);
                } else {
                    window.afficherNotification(data.message || 'Erreur lors de la récupération des informations du véhicule', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                window.afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
    };

    window.confirmerAnnulationCourse = function(idReservation) {
        window.afficherConfirmation(
            'Êtes-vous sûr de vouloir annuler cette course ? Cette action est irréversible.',
            () => window.afficherModalAnnulation(idReservation)
        );
    };

    // Définir les fonctions auxiliaires au niveau global
    window.afficherConfirmation = function(message, onConfirm) {
        const confirmationId = 'confirmationModal' + Date.now();
        const modalHtml = `
        <div class="modal fade" id="${confirmationId}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="btnConfirmer">Confirmer</button>
                    </div>
                </div>
            </div>
        </div>`;

        // Ajouter la modal au DOM
        const modalContainer = document.getElementById('modalContainer');
        modalContainer.innerHTML = modalHtml;

        // Afficher la modal
        const modal = new bootstrap.Modal(document.getElementById(confirmationId));
        modal.show();

        // Gérer la confirmation
        document.getElementById('btnConfirmer').addEventListener('click', () => {
            modal.hide();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });
    };

    window.afficherKilometrageModal = function(idReservation, vehicule) {
        const kilometrageActuel = vehicule?.kilometrage_actuel || 0;

        const modalHtml = `
        <div class="modal fade" id="kilometrageModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Informations de départ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formKilometrage">
                            <input type="hidden" name="id_reservation" value="${idReservation}">
                            <div class="mb-3">
                                <label class="form-label">Kilométrage actuel du véhicule: ${kilometrageActuel} km</label>
                                <input type="number" class="form-control" id="kilometrage_depart" name="kilometrage_depart" 
                                    min="${kilometrageActuel}" value="${kilometrageActuel}" required>
                                <div class="form-text">
                                    Le kilométrage de départ doit être supérieur ou égal au kilométrage actuel du véhicule.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Matériel emporté</label>
                                <textarea class="form-control" id="materiel" name="materiel" 
                                    rows="3" placeholder="Listez le matériel emporté pour cette course"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Acteurs accompagnant le chauffeur</label>
                                <textarea class="form-control" id="acteurs" name="acteurs" 
                                    rows="3" placeholder="Listez les personnes qui accompagnent le chauffeur"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Confirmer et démarrer la course</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>`;

        // Ajouter la modal au DOM
        const modalContainer = document.getElementById('modalContainer');
        modalContainer.innerHTML = modalHtml;

        // Afficher la modal
        const modal = new bootstrap.Modal(document.getElementById('kilometrageModal'));
        modal.show();

        // Gérer la soumission du formulaire
        const formKilometrage = document.getElementById('formKilometrage');
        formKilometrage.addEventListener('submit', function(e) {
            e.preventDefault();

            const kilometrageDepart = parseInt(document.getElementById('kilometrage_depart').value);
            const materiel = document.getElementById('materiel').value;
            const acteurs = document.getElementById('acteurs').value;

            // Valider le kilométrage
            if (kilometrageDepart < kilometrageActuel) {
                window.afficherNotification('Le kilométrage de départ ne peut pas être inférieur au kilométrage actuel du véhicule', 'warning');
                return;
            }

            // Enregistrer le kilométrage et démarrer la course
            fetch('api/confirmer-debut-course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_reservation: idReservation,
                    kilometrage_depart: kilometrageDepart,
                    materiel: materiel,
                    acteurs: acteurs
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Confirmation de début de course:', data);
                if (data.success) {
                    window.afficherNotification('Course démarrée avec succès', 'success');
                    modal.hide();
                    window.chargerDeplacementsEnCours();
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    window.afficherNotification(data.message || 'Erreur lors du démarrage de la course', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                window.afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
        });
    };

    window.afficherModalFinCourse = function(idReservation, vehicule) {
        const kilometrageDepart = vehicule.km_depart || 0;

        const modalHtml = `
        <div class="modal fade" id="finCourseModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Terminer la course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formFinCourse">
                            <input type="hidden" name="id_reservation" value="${idReservation}">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Kilométrage de départ</label>
                                    <input type="number" class="form-control" value="${kilometrageDepart}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kilométrage de retour *</label>
                                    <input type="number" class="form-control" id="kilometrage_retour" name="kilometrage_retour" 
                                        min="${kilometrageDepart + 1}" required>
                                    <div class="form-text">
                                        Le kilométrage de retour doit être supérieur au kilométrage de départ.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Matériel de retour</label>
                                <textarea class="form-control" name="materiel_retour" rows="3" 
                                    placeholder="Listez le matériel rapporté à la fin de la course"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Commentaires sur le trajet</label>
                                <textarea class="form-control" name="commentaires" rows="3" 
                                    placeholder="Commentaires sur le déroulement de la course"></textarea>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="notifier_fin" name="notifier_fin" checked>
                                <label class="form-check-label" for="notifier_fin">
                                    Notifier le demandeur de la fin de la course
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">Confirmer la fin de la course</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>`;

        // Ajouter la modal au DOM
        const modalContainer = document.getElementById('modalContainer');
        modalContainer.innerHTML = modalHtml;

        // Afficher la modal
        const modal = new bootstrap.Modal(document.getElementById('finCourseModal'));
        modal.show();

        // Gérer la soumission du formulaire
        const formFinCourse = document.getElementById('formFinCourse');
        formFinCourse.addEventListener('submit', function(e) {
            e.preventDefault();

            const kilometrageRetour = parseInt(document.getElementById('kilometrage_retour').value);

            // Valider le kilométrage de retour
            if (kilometrageRetour <= kilometrageDepart) {
                window.afficherNotification('Le kilométrage de retour doit être supérieur au kilométrage de départ', 'warning');
                return;
            }

            // Récupérer les données du formulaire
            const formData = new FormData(formFinCourse);

            // Enregistrer la fin de la course
            fetch('api/terminer-course.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Terminer course:', data);
                if (data.success) {
                    window.afficherNotification('Course terminée avec succès', 'success');
                    modal.hide();
                    window.chargerDeplacementsEnCours();
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    window.afficherNotification(data.message || 'Erreur lors de la finalisation de la course', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                window.afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
        });
    };

    window.afficherModalAnnulation = function(idReservation) {
        const modalHtml = `
        <div class="modal fade" id="annulationCourseModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Annulation de la course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formAnnulationCourse">
                            <input type="hidden" name="id_reservation" value="${idReservation}">
                            
                            <div class="mb-3">
                                <label class="form-label">Motif d'annulation *</label>
                                <select class="form-select" name="motif_annulation" required>
                                    <option value="">Sélectionnez un motif</option>
                                    <option value="demandeur">Annulation par le demandeur</option>
                                    <option value="vehicule">Problème de véhicule</option>
                                    <option value="chauffeur">Indisponibilité du chauffeur</option>
                                    <option value="meteo">Conditions météorologiques</option>
                                    <option value="autre">Autre raison</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Détails du motif d'annulation *</label>
                                <textarea class="form-control" name="details_annulation" rows="3" 
                                    placeholder="Précisez les raisons de l'annulation" required></textarea>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="notifier_annulation" name="notifier_annulation" checked>
                                <label class="form-check-label" for="notifier_annulation">
                                    Notifier le demandeur de l'annulation
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-danger w-100">Confirmer l'annulation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>`;

        // Ajouter la modal au DOM
        const modalContainer = document.getElementById('modalContainer');
        modalContainer.innerHTML = modalHtml;

        // Afficher la modal
        const modal = new bootstrap.Modal(document.getElementById('annulationCourseModal'));
        modal.show();

        // Gérer la soumission du formulaire
        const formAnnulationCourse = document.getElementById('formAnnulationCourse');
        formAnnulationCourse.addEventListener('submit', function(e) {
            e.preventDefault();

            // Récupérer les données du formulaire
            const formData = new FormData(formAnnulationCourse);

            // Enregistrer l'annulation
            fetch('api/annuler-course.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Annulation course:', data);
                if (data.success) {
                    window.afficherNotification('Course annulée avec succès', 'success');
                    modal.hide();
                    window.chargerDeplacementsEnCours();
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    window.afficherNotification(data.message || 'Erreur lors de l\'annulation de la course', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                window.afficherNotification('Erreur de communication avec le serveur', 'danger');
            });
        });
    };

    window.afficherNotification = function(message, type = 'info') {
        // Créer un conteneur de notifications s'il n'existe pas
        let notificationContainer = document.getElementById('notification-container');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'notification-container';
            notificationContainer.className = 'position-fixed top-0 end-0 p-3 z-3';
            document.body.appendChild(notificationContainer);
        }

        // Créer l'élément de notification
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;

        // Ajouter la notification
        notificationContainer.appendChild(toast);

        // Initialiser et montrer le toast avec Bootstrap
        const toastInstance = new bootstrap.Toast(toast);
        toastInstance.show();

        // Supprimer le toast après qu'il ait été caché
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    };

    // Forcer le rechargement après l'initialisation
    if (document.querySelector('#tracking-tab.active')) {
        console.log('Onglet suivi actif, chargement des déplacements...');
        setTimeout(() => {
            if (window.chargerDeplacementsEnCours) {
                window.chargerDeplacementsEnCours();
            }
        }, 500);
    }
});
</script>