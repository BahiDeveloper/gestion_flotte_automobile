document.addEventListener("DOMContentLoaded", function () {
    // Gère la suppression de l'assignation
    document.querySelectorAll(".btnSupprimerAssignation").forEach(button => {
        button.addEventListener("click", function () {
            let assignationId = this.getAttribute("data-assignation-id");

            // Confirmation avant de supprimer
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette demande sera supprimée de manière irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer cette demande !'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Envoyer la requête AJAX pour supprimer l'assignation
                    fetch(`actions/supprimer_assignation.php?id=${assignationId}`, {
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
                                    location.reload(); // Recharge la page pour mettre à jour la liste
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
