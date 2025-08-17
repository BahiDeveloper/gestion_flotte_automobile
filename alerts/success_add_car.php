<?php
if (isset($_GET['success_add']) && $_GET['success_add'] == 1) {
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
?>