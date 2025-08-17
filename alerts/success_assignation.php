<?php
// Vérifier si le paramètre "success_delete" est présent dans l'URL
if (isset($_GET['success_assignation']) && $_GET['success_assignation'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Demande effectuée avec succès !",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Erreur lors de l\'assignation du véhicule",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 2) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Le chauffeur est déjà assigné à un autre trajet pendant cette période.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 3) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Le véhicule est déjà en déplacement pendant cette période.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 4) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Tous les champs sont obligatoires.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 5) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Les dates doivent être aujourd\'hui ou dans le futur.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 6) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "La date de départ doit être antérieure à la date d\'arrivée.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 7) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Le trajet doit contenir au moins 5 caractères.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 8) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Méthode de requête non autorisée.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 9) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Désolé le véhicule est en maintenance",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// modifier 
if (isset($_GET['success_assignation_edit']) && $_GET['success_assignation_edit'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Assignation mise à jour avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// demande accepte 
if (isset($_GET['success_demande_assignation']) && $_GET['success_demande_assignation'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "La demande d\'assignation a été acceptée avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// echec demande
if (isset($_GET['error_assignation']) && $_GET['error_assignation'] == 6) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Echec de la demande d\'assignation",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// cours terminée
if (isset($_GET['success_assignation_termine']) && $_GET['success_assignation_termine'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Cours terminée avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// supprimer  

if (isset($_GET['success_assignation_delete']) && $_GET['success_assignation_delete'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "La demande d\'assignation a été supprimer avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}


?>