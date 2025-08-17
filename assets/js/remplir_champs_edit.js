
document.addEventListener('DOMContentLoaded', function () {
    // Écouter le clic sur les boutons "Éditer"
    document.querySelectorAll('.edit-vehicle').forEach(button => {
        button.addEventListener('click', function () {
            // Récupérer les données du véhicule depuis les attributs data
            const id = this.getAttribute('data-id');
            const marque = this.getAttribute('data-marque');
            const modele = this.getAttribute('data-modele');
            const immatriculation = this.getAttribute('data-immatriculation');
            const typeVehicule = this.getAttribute('data-type-vehicule');
            const capacite = this.getAttribute('data-capacite');
            const etat = this.getAttribute('data-etat');
            const kilometrage = this.getAttribute('data-kilometrage');

            // Pré-remplir les champs de la modal
            document.getElementById('editVehicleId').value = id;
            document.getElementById('editMarque').value = marque;
            document.getElementById('editModele').value = modele;
            document.getElementById('editImmatriculation').value = immatriculation;
            document.getElementById('editTypeVehicule').value = typeVehicule;
            document.getElementById('editCapacite').value = capacite;
            document.getElementById('editEtat').value = etat;
            document.getElementById('editKilometrage').value = kilometrage;
        });
    });
});
