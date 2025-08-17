<?php
//  supprimer 
// Vérifier si le paramètre "success_delete" est présent dans l'URL
if (isset($_GET['success_maintenance_terminer']) && $_GET['success_maintenance_terminer'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Maintenance du véhicule terminée avec succès",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_maintenance_not_found']) && $_GET['error_maintenance_not_found'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Erreur le véhicule est introuvable.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_maintenance_not_found']) && $_GET['error_maintenance_not_found'] == 2) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Erreur l\'ID du véhicule n\'existe pas.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_maintenance']) && $_GET['error_maintenance'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Erreur lors de la planification de la maintenance du véhicule",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

//  message modification  
// Vérifier si le paramètre "success_delete" est présent dans l'URL
if (isset($_GET['success_maintenance_edit']) && $_GET['success_maintenance_edit'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Maintenance modifiée avec succès",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_maintenance_edit']) && $_GET['error_maintenance_edit'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Erreur lors de la modification de la maintenance",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// supprimer 

if (isset($_GET['error_maintenance_del']) && $_GET['error_maintenance_del'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Erreur lors de la suppression de la maintenance",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// id invalid 
if (isset($_GET['error_invalid_id_maintenance']) && $_GET['error_invalid_id_maintenance'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Erreur ID n\'est pas passé ou n\'est pas valide",
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
                text: "Désolé le véhicule est en maintenance",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}


?>