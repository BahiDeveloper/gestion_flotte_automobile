document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");

    if (form) {
        form.addEventListener("submit", function (event) {
            if (!validateDates()) {
                event.preventDefault(); // Empêche l'envoi du formulaire si les dates sont incorrectes
            }
        });
    }

    function validateDates() {
        const date_delivrance = document.querySelector('input[name="date_delivrance"]').value;
        const date_expiration = document.querySelector('input[name="date_expiration"]').value;
        
        if (new Date(date_delivrance) >= new Date(date_expiration)) {
            alert("La date de délivrance doit être inférieure à la date d'expiration.");
            return false;
        }
        return true;
    }
    

    // Affichage des alertes depuis PHP
    if (typeof errorMessage !== "undefined" && errorMessage !== "") {
        Swal.fire("Erreur", errorMessage, "error");
    }

    if (typeof successMessage !== "undefined" && successMessage !== "") {
        Swal.fire("Succès", successMessage, "success");
    }
});
