function deleteChauffeur(chauffeurId) {
    // Afficher une alerte de confirmation avec SweetAlert
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Si confirmé, rediriger vers la page de suppression
            window.location.href = 'actions/supprimer_chauffeur.php?id=' + chauffeurId;
        }
    });
}


document.addEventListener("DOMContentLoaded", function () {
    // Sélection des éléments
    const telephoneInput = document.getElementById("telephone");
    const nomInput = document.getElementById("nom");
    const prenomInput = document.getElementById("prenom");
    const emailInput = document.getElementById("email");
    const adresseInput = document.getElementById("adresse");
    const categorie_permisInput = document.getElementById("categorie_permis");

    // Vérification du numéro de téléphone
    if (telephoneInput) {
        telephoneInput.addEventListener("input", function () {
            let value = telephoneInput.value.replace(/\D/g, ""); // Supprime tout sauf les chiffres
            value = value.substring(0, 10); // Limite à 10 chiffres

            // Formatage automatique en "07 67 37 69 20"
            let formattedValue = value.replace(/(\d{2})(?=\d)/g, "$1 ").trim();

            telephoneInput.value = formattedValue;
        });

        telephoneInput.addEventListener("blur", function () {
            if (telephoneInput.value.replace(/\D/g, "").length !== 10) {
                Swal.fire({
                    icon: "error",
                    title: "Format incorrect",
                    text: "Le numéro doit contenir exactement 10 chiffres.",
                    confirmButtonColor: "#d33",
                });
            }
        });
    }

    // Fonction pour convertir en majuscules (nom et prénom)
    function toUpperCaseInput(event) {
        event.target.value = event.target.value.toUpperCase();
    }

    // Ajout des écouteurs d'événements uniquement si les éléments existent
    if (nomInput) {
        nomInput.addEventListener("input", toUpperCaseInput);
    }
    if (prenomInput) {
        prenomInput.addEventListener("input", toUpperCaseInput);
    }
    if (categorie_permisInput) {
        categorie_permisInput.addEventListener("input", toUpperCaseInput);
    }

    // Vérification de l'email lorsque l'utilisateur quitte le champ (événement blur)
    if (emailInput) {
        emailInput.addEventListener("blur", function () {
            const emailValue = emailInput.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Vérification du format de l'email
            if (!emailRegex.test(emailValue)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Email incorrect',
                    text: 'L\'adresse email que vous avez saisie semble être invalide.',
                    confirmButtonColor: '#d33'
                });
            }
        });
    }


});
