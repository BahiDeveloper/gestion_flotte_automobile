
$(document).ready(function () {
    // Initialisation de DataTables avec options en français
    $('#vehiculesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
        },
        responsive: true,
        "order": [[1, 'asc']],
        "pageLength": 10,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Tous"]]
    });
});

// Fonction pour confirmer la suppression
function confirmerSuppression(idVehicule) {
    Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Cette action est irréversible!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer!',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirection vers script de suppression
            window.location.href = `actions/supprimer_vehicule.php?id=${idVehicule}`;
        }
    });
}