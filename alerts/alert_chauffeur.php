<?php
// ajouter 
// Vérifier si le paramètre "success_delete" est présent dans l'URL
if (isset($_GET['success_chauffeur']) && $_GET['success_chauffeur'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Le chauffeur a été ajouté avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_chauffeur_add']) && $_GET['error_chauffeur_add'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Une erreur s\'est produite lors de l\'ajout du chauffeur.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_chauffeur']) && $_GET['error_chauffeur'] == 2) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Veuillez remplir tous les champs du formaulaire.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// supprimer un chauffeur 
if (isset($_GET['success_chauffeur_delet']) && $_GET['success_chauffeur_delet'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Le chauffeur a été supprimé avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_chauffeur_delet']) && $_GET['error_chauffeur_delet'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Une erreur s\'est produite lors de la suppression du chauffeur",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// modifier un chauffeur 
if (isset($_GET['success_chauffeur_edit']) && $_GET['success_chauffeur_edit'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Les informations du chauffeur ont été mises à jour avec succès.",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_chauffeur_edit']) && $_GET['error_chauffeur_edit'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Une erreur s\'est produite lors de la mise à jour des informations du chauffeur.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_chauffeur_edit']) && $_GET['error_chauffeur_edit'] == 2) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Chauffeur non trouvé.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_chauffeur_edit']) && $_GET['error_chauffeur_edit'] == 3) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "ID du chauffeur non spécifié.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

if (isset($_GET['error_chauffeur']) && $_GET['error_chauffeur'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Cet email est déjà utilisé par un autre chauffeur.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
   
    ';
}

if (isset($_GET['success_permis_download']) && $_GET['success_permis_download'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Le permis du chauffeur a été téléchargé avec succès",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}



if (isset($_GET['error_permis_download']) && $_GET['error_permis_download'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Le fichier du permis n\'existe pas.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
   
    ';
}

if (isset($_GET['error_permis_download']) && $_GET['error_permis_download'] == 2) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Aucune photo de permis trouvée pour ce chauffeur.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
   
    ';
}

if (isset($_GET['error_permis_download']) && $_GET['error_permis_download'] == 3) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "ID du chauffeur non spécifié.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
   
    ';
}

?>
