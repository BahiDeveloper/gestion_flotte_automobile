document.addEventListener("DOMContentLoaded", function () {
    // Gère l'affichage du modal avec l'ID de maintenance
    document.querySelectorAll(".btnTerminerMaintenance").forEach(button => {
        button.addEventListener("click", function () {
            let maintenanceId = this.getAttribute("data-maintenance-id");
            document.getElementById("maintenance_id").value = maintenanceId;
        });
    });

    // Gère la soumission du formulaire en AJAX
    document.getElementById("formTerminerMaintenance").addEventListener("submit", function (e) {
        e.preventDefault(); // Empêche la soumission classique

        let formData = new FormData(this);

        fetch('actions/terminer_maintenance.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: "Succès",
                        text: data.message,
                        icon: "success"
                    }).then(() => {
                        location.reload(); // Recharge la page pour mettre à jour les tables
                    });
                } else {
                    Swal.fire({
                        title: "Erreur",
                        text: data.message,
                        icon: "error"
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: "Erreur",
                    text: "Une erreur est survenue lors de la communication avec le serveur.",
                    icon: "error"
                });
            });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Gère l'annulation de la maintenance
    document.querySelectorAll(".btnAnnulerMaintenance").forEach(button => {
        button.addEventListener("click", function () {
            let maintenanceId = this.getAttribute("data-maintenance-id");

            // Confirmation avant d'annuler
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Vous ne pourrez pas revenir en arrière !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, annuler la maintenance !'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Envoyer la requête AJAX pour annuler la maintenance
                    fetch(`actions/annuler_maintenance.php?id=${maintenanceId}`, {
                        method: 'GET'
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: "Succès",
                                    text: data.message,
                                    icon: "success"
                                }).then(() => {
                                    location.reload(); // Recharge la page pour mettre à jour les tables
                                });
                            } else {
                                Swal.fire({
                                    title: "Erreur",
                                    text: data.message,
                                    icon: "error"
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: "Erreur",
                                text: "Une erreur est survenue lors de la communication avec le serveur.",
                                icon: "error"
                            });
                        });
                }
            });
        });
    });
});