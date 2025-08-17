<!-- gestionnaire_reservation.php -->
<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->

<div class="container mt-4">
    <!-- En-tête de page -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-clipboard-list"></i> Demande de déplacement (Gestionnaire)</h2>
            <p class="text-muted">Ce formulaire suit le processus uniforme de demande de déplacement pour les
                gestionnaires.</p>
        </div>
    </div>

    <div class="row">
        <!-- Formulaire principal -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Formulaire de demande</h5>
                </div>
                <div class="card-body">
                    <form id="gestionnaireReservationForm">
                        <!-- Type de demande -->
                        <div class="mb-3">
                            <label class="form-label">Type de demande *</label>
                            <select class="form-select" id="typeDemande" name="typeDemande" required>
                                <option value="standard">Déplacement standard</option>
                                <option value="urgence">Déplacement urgent</option>
                                <option value="maintenance">Transport pour maintenance</option>
                                <option value="special">Transport spécial</option>
                            </select>
                        </div>

                        <!-- Justification si urgence -->
                        <div class="mb-3" id="justificationUrgence" style="display: none;">
                            <label class="form-label">Justification de l'urgence *</label>
                            <textarea class="form-control" name="justification" rows="3"
                                placeholder="Expliquez la raison de l'urgence..."></textarea>
                        </div>

                        <!-- Période de réservation -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date et heure de départ *</label>
                                <input type="datetime-local" class="form-control" id="dateDepart" name="dateDepart"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Durée estimée (heures) *</label>
                                <input type="number" class="form-control" id="dureeEstimee" name="dureeEstimee" min="1"
                                    required>
                            </div>
                        </div>

                        <!-- Véhicule et priorité -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type de véhicule requis *</label>
                                <select class="form-select" id="typeVehicule" name="typeVehicule" required>
                                    <option value="">Sélectionnez un type</option>
                                    <option value="utilitaire">Utilitaire</option>
                                    <option value="berline">Berline</option>
                                    <option value="camion">Camion</option>
                                    <option value="bus">Bus</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Niveau de priorité *</label>
                                <select class="form-select" id="priorite" name="priorite" required>
                                    <option value="1">Normale</option>
                                    <option value="2">Moyenne</option>
                                    <option value="3">Haute</option>
                                    <option value="4">Critique</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sélection du véhicule -->
                        <div class="mb-3">
                            <label class="form-label">Véhicule souhaité *</label>
                            <select class="form-select" id="vehicule" name="vehicule" required disabled>
                                <option value="">Sélectionnez d'abord un type</option>
                            </select>
                        </div>

                        <!-- Chauffeur requis -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="chauffeurRequis"
                                    name="chauffeurRequis">
                                <label class="form-check-label" for="chauffeurRequis">
                                    Chauffeur requis
                                </label>
                            </div>
                        </div>

                        <!-- Sélection du chauffeur (conditionnelle) -->
                        <div class="mb-3" id="divChauffeur" style="display: none;">
                            <label class="form-label">Chauffeur *</label>
                            <select class="form-select" id="chauffeur" name="chauffeur">
                                <option value="">Sélectionnez un chauffeur</option>
                            </select>
                        </div>

                        <!-- Détails du déplacement -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de passagers *</label>
                                <input type="number" class="form-control" id="nbPassagers" name="nbPassagers" min="1"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type de chargement *</label>
                                <input type="text" class="form-control" id="typeChargement" name="typeChargement"
                                    required>
                            </div>
                        </div>

                        <!-- Itinéraire -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Lieu de départ *</label>
                                <input type="text" class="form-control" id="lieuDepart" name="lieuDepart" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lieu d'arrivée *</label>
                                <input type="text" class="form-control" id="lieuArrivee" name="lieuArrivee" required>
                            </div>
                        </div>

                        <!-- Motif du déplacement -->
                        <div class="mb-3">
                            <label class="form-label">Motif du déplacement *</label>
                            <textarea class="form-control" id="motif" name="motif" rows="3" required
                                placeholder="Décrivez le motif du déplacement..."></textarea>
                        </div>

                        <!-- Documents justificatifs -->
                        <div class="mb-3">
                            <label class="form-label">Documents justificatifs</label>
                            <input type="file" class="form-control" id="documents" name="documents[]" multiple>
                            <small class="text-muted">Vous pouvez joindre plusieurs documents si nécessaire.</small>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                <i class="fas fa-arrow-left"></i> Retour
                            </button>
                            <div>
                                <button type="button" class="btn btn-info me-2" id="btnSauvegarder">
                                    <i class="fas fa-save"></i> Sauvegarder brouillon
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Soumettre la demande
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panneau latéral d'informations -->
        <div class="col-md-4">
            <!-- Informations du véhicule -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Informations véhicule</h5>
                </div>
                <div class="card-body" id="vehiculeInfo" style="display: none;">
                    <ul class="list-unstyled mb-0">
                        <li><strong>Modèle:</strong> <span id="modeleVehicule"></span></li>
                        <li><strong>Capacité:</strong> <span id="capaciteVehicule"></span></li>
                        <li><strong>Kilométrage:</strong> <span id="kilometrageVehicule"></span></li>
                        <li><strong>État:</strong> <span id="etatVehicule"></span></li>
                    </ul>
                </div>
            </div>

            <!-- Historique des demandes -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Mes dernières demandes</h5>
                </div>
                <div class="card-body">
                    <div id="historiqueDemandesLoader" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div id="historiqueDemandes"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script de gestion du formulaire -->
<script>
    $(document).ready(function () {
        // Gestion du type de demande
        $('#typeDemande').change(function () {
            if ($(this).val() === 'urgence') {
                $('#justificationUrgence').show();
                $('#justificationUrgence textarea').prop('required', true);
            } else {
                $('#justificationUrgence').hide();
                $('#justificationUrgence textarea').prop('required', false);
            }
        });

        // Gestion du chauffeur requis
        $('#chauffeurRequis').change(function () {
            if ($(this).is(':checked')) {
                $('#divChauffeur').show();
                $('#chauffeur').prop('required', true);
            } else {
                $('#divChauffeur').hide();
                $('#chauffeur').prop('required', false);
            }
        });

        // Chargement des véhicules selon le type
        $('#typeVehicule').change(function () {
            const type = $(this).val();
            if (type) {
                $.ajax({
                    url: 'get_vehicules_disponibles.php',
                    method: 'POST',
                    data: {
                        type: type,
                        dateDepart: $('#dateDepart').val(),
                        duree: $('#dureeEstimee').val()
                    },
                    success: function (response) {
                        $('#vehicule').html(response).prop('disabled', false);
                    }
                });
            } else {
                $('#vehicule').html('<option value="">Sélectionnez d\'abord un type</option>').prop('disabled', true);
                $('#vehiculeInfo').hide();
            }
        });

        // Chargement des informations du véhicule
        $('#vehicule').change(function () {
            const vehiculeId = $(this).val();
            if (vehiculeId) {
                $.ajax({
                    url: 'get_vehicule_details.php',
                    method: 'POST',
                    data: { id: vehiculeId },
                    success: function (response) {
                        const data = JSON.parse(response);
                        $('#modeleVehicule').text(data.modele);
                        $('#capaciteVehicule').text(data.capacite + ' passagers');
                        $('#kilometrageVehicule').text(data.kilometrage + ' km');
                        $('#etatVehicule').text(data.etat);
                        $('#vehiculeInfo').show();

                        // Mise à jour des chauffeurs disponibles si nécessaire
                        if ($('#chauffeurRequis').is(':checked')) {
                            updateChauffeursDisponibles();
                        }
                    }
                });
            } else {
                $('#vehiculeInfo').hide();
            }
        });

        // Mise à jour des chauffeurs disponibles
        function updateChauffeursDisponibles() {
            const vehiculeId = $('#vehicule').val();
            const dateDepart = $('#dateDepart').val();
            const duree = $('#dureeEstimee').val();

            if (vehiculeId && dateDepart && duree) {
                $.ajax({
                    url: 'get_chauffeurs_disponibles.php',
                    method: 'POST',
                    data: {
                        vehiculeId: vehiculeId,
                        dateDepart: dateDepart,
                        duree: duree
                    },
                    success: function (response) {
                        $('#chauffeur').html(response);
                    }
                });
            }
        }

        // Chargement de l'historique des demandes
        function loadHistoriqueDemandes() {
            $.ajax({
                url: 'get_historique_demandes.php',
                method: 'GET',
                success: function (response) {
                    $('#historiqueDemandesLoader').hide();
                    $('#historiqueDemandes').html(response);
                }
            });
        }

        // Sauvegarde du brouillon
        $('#btnSauvegarder').click(function () {
            const formData = new FormData($('#gestionnaireReservationForm')[0]);
            formData.append('action', 'sauvegarder');

            $.ajax({
                url: 'save_demande_brouillon.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Brouillon sauvegardé',
                            text: 'Votre demande a été sauvegardée en tant que brouillon'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.message
                        });
                    }
                }
            });
        });

        // Soumission du formulaire
        $('#gestionnaireReservationForm').submit(function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'soumettre');

            $.ajax({
                url: 'process_demande_gestionnaire.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Demande soumise',
                            text: 'Votre demande de déplacement a été soumise avec succès',
                            footer: 'Un autre gestionnaire validera votre demande'
                        }).then(() => {
                            window.location.href = 'mes_demandes.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.message
                        });
                    }
                }
            });
        });

        // Chargement initial
        loadHistoriqueDemandes();

        // Vérification de la priorité critique
        $('#priorite').change(function () {
            if ($(this).val() === '4') { // Priorité critique
                Swal.fire({
                    icon: 'warning',
                    title: 'Priorité critique',
                    text: 'La priorité critique nécessite une validation supplémentaire. Êtes-vous sûr ?',
                    showCancelButton: true,
                    confirmButtonText: 'Oui',
                    cancelButtonText: 'Non'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        $(this).val('3'); // Retour à priorité haute
                    }
                });
            }
        });

        // Vérification des dates
        $('#dateDepart, #dureeEstimee').change(function () {
            validateDates();
            if ($('#typeVehicule').val()) {
                $('#typeVehicule').trigger('change');
            }
        });

        function validateDates() {
            const dateDepart = new Date($('#dateDepart').val());
            const now = new Date();
            const duree = $('#dureeEstimee').val();

            if (dateDepart < now) {
                Swal.fire({
                    icon: 'error',
                    title: 'Date invalide',
                    text: 'La date de départ ne peut pas être dans le passé'
                });
                $('#dateDepart').val('');
                return false;
            }

            if (duree <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Durée invalide',
                    text: 'La durée doit être supérieure à 0'
                });
                $('#dureeEstimee').val('');
                return false;
            }

            return true;
        }
    });
</script>

<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer   -->