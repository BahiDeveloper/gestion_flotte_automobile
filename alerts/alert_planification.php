<?php
// Vérifier si le paramètre "success_delete" est présent dans l'URL
if (isset($_GET['success_planification_maintenance']) && $_GET['success_planification_maintenance'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Succès !",
                text: "Maintenance planifiée avec succès",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

// Vérifier si le paramètre "error_delete" est présent dans l'URL
if (isset($_GET['error_planification']) && $_GET['error_planification'] == 1) {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Erreur !",
                text: "Erreur lors de la planification de la maintenance",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>
    ';
}

?>