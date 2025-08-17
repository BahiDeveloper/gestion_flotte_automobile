$(document).ready(function () {
    // Initialisation du DataTable pour les approvisionnements
    const approvisionnementsTable = $('#allApprovisionnementsTable').DataTable({
        responsive: true,
        language: {
            url: 'assets/plugins/dataTables/French.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copier',
                className: 'btn btn-secondary btn-sm'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimer',
                className: 'btn btn-info btn-sm'
            }
        ],
        order: [[2, 'desc']], // Tri par défaut sur la colonne de date (3ème colonne) en ordre décroissant
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [
            {
                targets: [1, 3, 4, 5, 6],
                className: 'text-center'
            }
        ]
    });

    // Filtrage par véhicule
    $('#filterVehiculeAppro').on('change', function () {
        const vehiculeId = $(this).val();

        approvisionnementsTable
            .column(0)
            .search(vehiculeId ? vehiculeId : '', true, false)
            .draw();
    });

    // Filtrage par type de carburant
    $('#filterTypeCarburant').on('change', function () {
        const typeCarburant = $(this).val();

        approvisionnementsTable
            .column(4)
            .search(typeCarburant ? typeCarburant : '', true, false)
            .draw();
    });

    // Filtrage par date de début
    $('#filterDateDebutAppro').on('change', function () {
        const dateDebut = $(this).val();

        // Personnaliser le filtrage par date
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'allApprovisionnementsTable') {
                    return true;
                }

                const dateApproString = data[2]; // Colonne de la date
                const dateAppro = moment(dateApproString, 'DD/MM/YYYY HH:mm');
                const filterDate = moment(dateDebut);

                return dateDebut === '' || dateAppro.isSameOrAfter(filterDate, 'day');
            }
        );

        approvisionnementsTable.draw();
    });

    // Filtrage par date de fin
    $('#filterDateFinAppro').on('change', function () {
        const dateFin = $(this).val();

        // Personnaliser le filtrage par date
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'allApprovisionnementsTable') {
                    return true;
                }

                const dateApproString = data[2]; // Colonne de la date
                const dateAppro = moment(dateApproString, 'DD/MM/YYYY HH:mm');
                const filterDate = moment(dateFin);

                return dateFin === '' || dateAppro.isSameOrBefore(filterDate, 'day');
            }
        );

        approvisionnementsTable.draw();
    });

    // Réinitialisation des filtres
    $('#approvisionnementFilterForm').on('reset', function () {
        // Effacer tous les filtres du DataTable
        approvisionnementsTable
            .search('')
            .columns().search('')
            .draw();

        // Supprimer les filtres personnalisés de date
        $.fn.dataTable.ext.search.pop();
    });

    // Détails de l'approvisionnement
    $('.view-appro-details').on('click', function () {
        const approId = $(this).data('id');

        // Requête AJAX pour récupérer les détails
        $.ajax({
            url: 'request/vehicules/get_approvisionnement_details.php',
            method: 'GET',
            data: { id: approId },
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
                                <strong>Chauffeur :</strong> ${response.data.chauffeur || 'Non spécifié'}
                            </div>
                            <div class="col-md-6">
                                <strong>Date :</strong> ${response.data.date_approvisionnement}
                            </div>
                            <div class="col-md-6">
                                <strong>Station-service :</strong> ${response.data.station_service || 'N/A'}
                            </div>
                            <div class="col-md-6">
                                <strong>Quantité :</strong> ${response.data.quantite_litres} L
                            </div>
                            <div class="col-md-6">
                                <strong>Type de carburant :</strong> ${response.data.type_carburant_label}
                            </div>
                            <div class="col-md-6">
                                <strong>Prix unitaire :</strong> ${response.data.prix_unitaire} FCFA/L
                            </div>
                            <div class="col-md-6">
                                <strong>Prix total :</strong> ${response.data.prix_total} FCFA
                            </div>
                            <div class="col-md-6">
                                <strong>Kilométrage :</strong> ${response.data.kilometrage} km
                            </div>
                        </div>
                    `;

                    $('#detailsContent').html(modalContent);
                    $('#detailsModal').modal('show');
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