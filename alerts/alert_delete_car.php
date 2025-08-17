<?php
// Vérifier si le paramètre "success_delete" est présent dans l'URL
if (isset($_GET['success_delete']) && $_GET['success_delete'] == 1) {
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

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_delete']) && $_GET['error_delete'] == 1) {
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
?>