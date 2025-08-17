// <!-- Script pour le filtrage et la gestion des maintenances --> 
document.addEventListener('DOMContentLoaded', function () {
    // Initialisation de DataTables
    const maintenanceTable = new DataTable('#allMaintenancesTable', {
        language: {
            url: 'assets/js/dataTables.french.json'
        },
        order: [[3, 'desc']], // Trier par date de début (décroissant)
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        responsive: true
    });

    // Gestion du formulaire de filtrage
    document.getElementById('maintenanceFilterForm').addEventListener('submit', function (e) {
        e.preventDefault();
        filterMaintenances();
    });

    document.getElementById('maintenanceFilterForm').addEventListener('reset', function () {
        setTimeout(() => {
            filterMaintenances();
        }, 10);
    });

    function filterMaintenances() {
        const vehiculeFilter = document.getElementById('filterVehicule').value;
        const typeFilter = document.getElementById('filterType').value;
        const statutFilter = document.getElementById('filterStatut').value;
        const dateFilter = document.getElementById('filterDateDebut').value;

        // Réinitialiser le filtre DataTables
        maintenanceTable.search('').columns().search('').draw();

        // Filtrer par attributs de données personnalisés
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            const row = maintenanceTable.row(dataIndex).node();

            // Filtre véhicule
            if (vehiculeFilter && row.getAttribute('data-vehicule') !== vehiculeFilter) {
                return false;
            }

            // Filtre type
            if (typeFilter && row.getAttribute('data-type') !== typeFilter) {
                return false;
            }

            // Filtre statut
            if (statutFilter && row.getAttribute('data-statut') !== statutFilter) {
                return false;
            }

            // Filtre date
            if (dateFilter) {
                const rowDate = new Date(row.getAttribute('data-date'));
                const filterDate = new Date(dateFilter);
                if (rowDate < filterDate) {
                    return false;
                }
            }

            return true;
        });

        maintenanceTable.draw();

        // Supprimer le filtre personnalisé après utilisation
        $.fn.dataTable.ext.search.pop();
    }

    // Gestion des boutons d'action
    document.querySelectorAll('.btn-terminer-maintenance').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            if (confirm('Êtes-vous sûr de vouloir marquer cette maintenance comme terminée ?')) {
                // Rediriger vers la page de finalisation avec confirmation
                window.location.href = `actions/vehicules/terminer_maintenance.php?id=${id}`;
            }
        });
    });

    document.querySelectorAll('.btn-annuler-maintenance').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            if (confirm('Êtes-vous sûr de vouloir annuler cette maintenance ?')) {
                // Ouvrir un modal ou rediriger vers la page d'annulation
                window.location.href = `actions/vehicules/annuler_maintenance.php?id=${id}`;
            }
        });
    });
});