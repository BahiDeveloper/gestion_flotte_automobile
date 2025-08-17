document.addEventListener("DOMContentLoaded", function () {
    let modal = document.getElementById("modifierAssignationModal");
    let buttons = document.querySelectorAll(".btnModifierAssignation");

    buttons.forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("edit-id-assignation").value = this.getAttribute("data-id");
            document.getElementById("edit-chauffeur").value = this.getAttribute("data-chauffeur");
            document.getElementById("edit-vehicule").value = this.getAttribute("data-vehicule");

            // Séparation du trajet en Point de départ et Point d'arrivée
            let trajet = this.getAttribute("data-trajet");
            let trajetParts = trajet.split(" - ");
            document.getElementById("edit-trajet-A").value = trajetParts[0] || "";
            document.getElementById("edit-trajet-B").value = trajetParts[1] || "";

            document.getElementById("edit-date-depart").value = this.getAttribute("data-date_depart");
            document.getElementById("edit-date-arrivee").value = this.getAttribute("data-date_arrivee");
        });
    });
});
