$(document).ready(function () {
    $('#chauffeursTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json" // Traduction en français
        },
        "dom": 'Bfrtip', // Ajoute des boutons d'exportation
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print' // Boutons d'exportation
        ],
        "responsive": true, // Rendre le tableau responsive
        "order": [[0, 'asc']], // Trier par la première colonne (ID) par défaut
        "paging": true, // Activer la pagination
        "lengthMenu": [10, 25, 50, 100], // Options de pagination
        "pageLength": 10, // Nombre de lignes par page
        "searching": true, // Activer la recherche
        "info": true // Afficher les informations de pagination
    });
});