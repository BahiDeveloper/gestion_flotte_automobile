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
if (isset($_GET['success_vehicule_up']) && $_GET['success_vehicule_up'] == 1) {
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
// Vérifier si le paramètre "success" est présent dans l'URL
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
if (isset($_GET['error_vehicule_del']) && $_GET['error_vehicule_del'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Une erreur s\'est produite lors de la suppression du véhicule.",
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

// Succès de modification d'approvisionnement
if (isset($_GET['success_approvisionnement_edit']) && $_GET['success_approvisionnement_edit'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Modification approvisionnement - script executé");
            Swal.fire({
                title: "Succès !",
                text: "L\'approvisionnement a été modifié avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}


?>