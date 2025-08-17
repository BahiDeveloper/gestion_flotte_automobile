document.addEventListener('DOMContentLoaded', function () {
    var modalTerminerCourse = document.getElementById('modalTerminerCourse');
    modalTerminerCourse.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Bouton qui a déclenché la modal
        var assignationId = button.getAttribute('data-assignation-id'); // Extraire l'ID de l'assignation
        var modalInput = modalTerminerCourse.querySelector('#assignation_id'); // Champ caché dans la modal
        modalInput.value = assignationId; // Remplir le champ caché avec l'ID de l'assignation

        // Récupérer le kilométrage actuel du véhicule via une requête AJAX
        fetch(`request/get_kilometrage_actuel.php?assignation_id=${assignationId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('kilometrage_actuel_vehicule').textContent = data.kilometrage_actuel;
            })
            .catch(error => console.error('Erreur:', error));
    });
});
