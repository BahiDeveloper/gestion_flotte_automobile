document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".btn-debuter").forEach(button => {
        button.addEventListener("click", function (event) {
            const card = this.closest(".card");
            const assignationId = card.querySelector("[data-assignation-id]").getAttribute("data-assignation-id");

            // Récupération des dates de départ et d'arrivée prévues
            const dateDepartText = card.querySelector(".card-body p:nth-child(3)").textContent.replace("Date de départ prévue : ", "");
            const dateArriveeText = card.querySelector(".card-body p:nth-child(4)").textContent.replace("Date d'arrivée prévue : ", "");

            const dateDepart = new Date(dateDepartText).getTime();
            const dateArrivee = new Date(dateArriveeText).getTime();
            const now = new Date().getTime();

            // Vérifier si la date de départ est passée
            if (now >= dateDepart) {
                // Envoyer une requête AJAX pour mettre à jour la base de données
                fetch("actions/debuter_course.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        assignation_id: assignationId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {

                            // Afficher les éléments masqués
                            card.querySelector(".fin-course-kilometrage").style.display = "block";
                            card.querySelector(".course-terminee").style.display = "inline-block";

                            // Cacher le bouton "Débuter la course"
                            this.style.display = "none";

                            // Afficher une alerte de confirmation avec les dates de départ et d'arrivée
                            Swal.fire({
                                icon: "success",
                                title: "Course démarrée !",
                                text: `Bonne route 🚗💨\nDépart prévu le : ${dateDepartText}\nArrivée prévue le : ${dateArriveeText}`,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "OK"
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Erreur",
                                text: data.message,
                                confirmButtonColor: "#d33",
                                confirmButtonText: "OK"
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: "error",
                            title: "Erreur",
                            text: "Une erreur s'est produite lors de la mise à jour de la base de données.",
                            confirmButtonColor: "#d33",
                            confirmButtonText: "OK"
                        });
                    });
            } else {
                // Afficher une alerte si la date de départ n'est pas encore arrivée
                Swal.fire({
                    icon: "warning",
                    title: "Trop tôt !",
                    html: `Vous ne pouvez pas débuter la course avant la : <br> <strong> ${dateDepartText} </strong>`,
                    confirmButtonColor: "#d33",
                    confirmButtonText: "OK"
                });
            }
        });
    });
});