
document.addEventListener('DOMContentLoaded', function() {
    var modalAssignChauffeur = document.getElementById('assignChauffeurModal');
    modalAssignChauffeur.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Bouton qui a déclenché la modal
        var assignationId = button.getAttribute('data-assignation-id'); // Extraire l'ID de l'assignation
        var modalInput = modalAssignChauffeur.querySelector('#assignation_id'); // Champ caché dans la modal
        modalInput.value = assignationId; // Remplir le champ caché avec l'ID de l'assignation
    });
});
