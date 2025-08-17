$(document).ready(function () {
    // Gestion de l'affichage des détails de maintenance
    $('.view-maintenance-details').on('click', function () {
        const maintenanceId = $(this).data('id');

        // Requête AJAX pour récupérer les détails
        $.ajax({
            url: 'request/vehicules/get_maintenance_details.php',
            method: 'GET',
            data: { id: maintenanceId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Construire le contenu du modal avec les détails
                    let logoHtml = response.data.logo_marque_vehicule
                        ? `<img src="uploads/vehicules/logo_marque/${response.data.logo_marque_vehicule}" 
                                class="img-fluid rounded mb-3" 
                                style="max-height: 100px; max-width: 150px;" 
                                alt="Logo ${response.data.marque}">`
                        : '<div class="text-center text-muted mb-3"><i class="fas fa-car fa-3x"></i></div>';

                    let modalContent = `
                        <div class="text-center mb-3">
                            ${logoHtml}
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Véhicule :</strong> ${response.data.marque} ${response.data.modele} (${response.data.immatriculation})
                            </div>
                            <div class="col-md-6">
                                <strong>Zone :</strong> ${response.data.zone}
                            </div>
                            <div class="col-md-6">
                                <strong>Type de maintenance :</strong> ${response.data.type_maintenance}
                            </div>
                            <div class="col-md-6">
                                <strong>Statut :</strong> ${response.data.statut}
                            </div>
                            <div class="col-md-12 mt-3">
                                <strong>Description :</strong> 
                                <p class="text-muted">${response.data.description}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Date début :</strong> ${response.data.date_debut}
                            </div>
                            <div class="col-md-6">
                                <strong>Date fin prévue :</strong> ${response.data.date_fin_prevue}
                            </div>
                            <div class="col-md-6">
                                <strong>Date fin effective :</strong> ${response.data.date_fin_effective}
                            </div>
                            <div class="col-md-6">
                                <strong>Coût :</strong> ${response.data.cout}
                            </div>
                            <div class="col-md-6">
                                <strong>Kilométrage :</strong> ${response.data.kilometrage} km
                            </div>
                            <div class="col-md-6">
                                <strong>Prestataire :</strong> ${response.data.prestataire}
                            </div>
                        </div>
                    `;

                    $('#detailsMaintenanceContent').html(modalContent);
                    $('#detailsMaintenanceModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: response.message || 'Impossible de récupérer les détails.'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors de la récupération des détails.'
                });
            }
        });
    });
});