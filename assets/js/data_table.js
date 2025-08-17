$(document).ready(function () {
    $('#documentTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json" // Pour la traduction en français
        },
        "order": [[2, "asc"]], // Tri par défaut sur la colonne "Date de début"
        "pageLength": 10, // Nombre de lignes par page
        "pagingType": "full_numbers", // Type de pagination
        "searching": true, // Activer la recherche
        "responsive": true // Activer le mode responsive
    });
});