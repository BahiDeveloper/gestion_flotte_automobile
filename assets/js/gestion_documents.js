// Script pour masquer/afficher le champ "Date de fin"
document.addEventListener("DOMContentLoaded", function () {
    const frequenceRenouvellement = document.getElementById("frequence_renouvellement");
    const dateFinContainer = document.getElementById("date_fin_container");

    // Fonction pour gérer la visibilité du champ "Date de fin"
    function toggleDateFinVisibility() {
        if (frequenceRenouvellement.value === "permanent") {
            dateFinContainer.style.display = "none"; // Masquer le champ
        } else {
            dateFinContainer.style.display = "block"; // Afficher le champ
        }
    }

    // Écouter les changements de la fréquence de renouvellement
    frequenceRenouvellement.addEventListener("change", toggleDateFinVisibility);

    // Appliquer la logique au chargement de la page
    toggleDateFinVisibility();
});