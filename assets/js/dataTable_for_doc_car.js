document.addEventListener('DOMContentLoaded', function () {
    $('#documentsTable').DataTable({
        dom: 'Bfrtip', // Ajoute des boutons d'exportation
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print' // Boutons disponibles
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json' // Traduction en français
        },
        responsive: true // Rend le tableau responsive
    });
});
