<?php
// ajouter 
if (isset($_GET['success_vehicule_add']) && $_GET['success_vehicule_add'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Le véhicule a été enregistré avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}
// approvisionnements 

if (isset($_GET['success_approvisionnement']) && $_GET['success_approvisionnement'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Approvisionnement enregistré avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// modifier 
if (isset($_GET['success_vehicule_edit']) && $_GET['success_vehicule_edit'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Le véhicule a été modifié avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}
// supprimer 
// Redirection avec message de succès
if (isset($_GET['success_vehicule_del']) && $_GET['success_vehicule_del'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Le véhicule a été supprimé avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error" est présent dans l'URL
// Redirection avec message d'erreur
if (isset($_GET['error_vehicule_del']) && $_GET['error_vehicule_del'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Une erreur est survenue lors de la suppression du véhicule. Veuillez réessayer plus tard.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error" est présent dans l'URL
if (isset($_GET['error_vehicule_add']) && $_GET['error_vehicule_add'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Données manquantes",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Si l'ID n'est pas valide, rediriger avec un message d'erreur
if (isset($_GET['error_vehicule_del']) && $_GET['error_vehicule_del'] == 2) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "l\'ID n\'est pas valide",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

 // Le véhicule n'existe pas
if (isset($_GET['error_vehicule_del']) && $_GET['error_vehicule_del'] == 3) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Le véhicule demandé n\'existe pas.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}


 // 2. Vérifier si le véhicule est en cours d'utilisation
if (isset($_GET['error_vehicule_del']) && $_GET['error_vehicule_del'] == 4) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Impossible de supprimer ce véhicule car il est actuellement en cours d\'utilisation.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// 3. Vérifier si des réservations futures existent pour ce véhicule
if (isset($_GET['error_vehicule_del']) && $_GET['error_vehicule_del'] == 5) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Impossible de supprimer ce véhicule car il possède des réservations futures.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Succès de modification d'approvisionnement
if (isset($_GET['success_approvisionnement_edit']) && $_GET['success_approvisionnement_edit'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var alertDiv = document.createElement("div");
            alertDiv.className = "alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x";
            alertDiv.style.zIndex = "1050";
            alertDiv.innerHTML = `
                L\'approvisionnement a été modifié avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.prepend(alertDiv);
            
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname + "#approvisionnements");
            }
        });
    </script>
    ';
}
?>