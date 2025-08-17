document.addEventListener('DOMContentLoaded', function () {
    const fuelButtons = document.querySelectorAll('.btnApprovisionnement'); // Sélectionne tous les boutons d'approvisionnement

    fuelButtons.forEach(button => {
        button.addEventListener('click', function () {
            const vehicleId = this.getAttribute('data-vehicle-id'); // Récupère l'ID du véhicule
            const fuelType = this.getAttribute('data-vehicle-type-fuel'); // Récupère le type de carburant

            // Affichage du type de carburant dans la modal
            document.getElementById('fuelTypeDisplay').textContent = fuelType;

            // Vérification du type de carburant
            // if (fuelType !== 'super' && fuelType !== 'gasoil') {
            //     Swal.fire({
            //         icon: 'error',
            //         title: 'Erreur',
            //         text: 'Type de carburant inconnu pour ce véhicule.',
            //         confirmButtonText: 'OK'
            //     });
            //     return; // Empêche d'ouvrir la modal si le type de carburant est invalide
            // }

            // Définir le prix du litre en fonction du type de carburant
            let fuelCostPerLiter = 0;
            if (fuelType === 'super') {
                fuelCostPerLiter = 875;
            } else if (fuelType === 'gasoil') {
                fuelCostPerLiter = 715;
            }

            // Affiche la modal
            const modal = new bootstrap.Modal(document.getElementById('fuelModal'));
            modal.show();

            // Met à jour le champ caché avec l'ID du véhicule
            document.getElementById('vehicleId').value = vehicleId;

            // Afficher le prix unitaire du carburant
            document.getElementById('unitFuelCost').textContent = fuelCostPerLiter;

            // Gestion du coût et de la quantité de carburant
            const fuelCostInput = document.getElementById('fuelCost');
            const fuelAmountInput = document.getElementById('fuelAmount');

            fuelCostInput.addEventListener('input', function () {
                const fuelCost = parseFloat(fuelCostInput.value);
                if (!isNaN(fuelCost) && fuelCost > 0) {
                    const fuelAmount = fuelCost / fuelCostPerLiter;
                    fuelAmountInput.value = fuelAmount.toFixed(2); // Afficher 2 décimales
                } else {
                    fuelAmountInput.value = '0';
                }
            });

            // Soumission du formulaire avec AJAX
            const fuelForm = document.getElementById('fuelForm');
            fuelForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const fuelCost = parseFloat(fuelCostInput.value);
                const fuelAmount = parseFloat(fuelAmountInput.value);

                if (!isNaN(fuelCost) && fuelCost > 0 && !isNaN(fuelAmount) && fuelAmount > 0) {
                    // Créer un objet FormData
                    const formData = new FormData();
                    formData.append('vehicleId', vehicleId);
                    formData.append('fuelAmount', fuelAmount);
                    formData.append('fuelCost', fuelCost.toFixed(0)); // Ajouté `toFixed(0)` pour enlever les décimales

                    // Envoyer les données avec AJAX
                    fetch('actions/approvisionner_vehicule.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json()) // Convertir la réponse en JSON
                        .then(data => {
                            if (data.status === "success") {
                                // Afficher une SweetAlert de succès
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Succès',
                                    text: data.message,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    modal.hide(); // Fermer la modal
                                    window.location.reload(); // Recharger la page pour mettre à jour la liste
                                });
                            } else {
                                // Afficher une SweetAlert d'erreur
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erreur',
                                    text: data.message,
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            // Afficher une SweetAlert en cas d'erreur réseau
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: 'Une erreur s\'est produite lors de la communication avec le serveur.',
                                confirmButtonText: 'OK'
                            });
                        });
                } else {
                    // Afficher une SweetAlert si le coût ou la quantité est invalide
                    Swal.fire({
                        icon: 'warning',
                        title: 'Attention',
                        text: 'Veuillez entrer un coût valide.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });
});