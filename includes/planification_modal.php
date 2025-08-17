<!-- Modal pour planifier une maintenance -->
<div class="modal fade" id="planMaintenanceModal" tabindex="-1" aria-labelledby="planMaintenanceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planMaintenanceModalLabel">Planifier une maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="maintenanceForm" action="actions/planifier_maintenance.php" method="POST">
                    <input type="hidden" name="id_vehicule" id="id_vehicule" value="">
                    <!-- Champ caché pour l'ID du véhicule -->
                    <div class="mb-3">
                        <label for="maintenanceDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="maintenanceDescription" name="description" required>
                    </div>
                    <div class="mb-3">
                        <label for="maintenanceDateDebut" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="maintenanceDateDebut" name="date_debut" required>
                    </div>

                    <div class="mb-3">
                        <label for="maintenanceDateFinPrevue" class="form-label">Date de fin prévue</label>
                        <input type="date" class="form-control" id="maintenanceDateFinPrevue" name="date_fin_prevue" 
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Planifier
                    </button>
                    <button type="reset" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Effacer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let today = new Date().toISOString().split('T')[0]; // Format YYYY-MM-DD
        let dateDebutField = document.getElementById("maintenanceDateDebut");
        let dateFinField = document.getElementById("maintenanceDateFinPrevue");

        // Empêcher la sélection de dates passées
        dateDebutField.min = today;
        dateFinField.min = today;

        // Vérifie si la date de début est valide
        dateDebutField.addEventListener("change", function () {
            if (dateDebutField.value < today) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Date invalide',
                    text: 'Veuillez choisir une date actuelle ou future.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                dateDebutField.value = today;
            }
        });

        // Vérifie si la date de fin est valide
        dateFinField.addEventListener("change", function () {
            if (dateFinField.value < dateDebutField.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Date invalide',
                    text: 'La date de fin doit être postérieure à la date de début.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                dateFinField.value = dateDebutField.value;
            }
        });

        // Validation avant soumission du formulaire
        document.getElementById("maintenanceForm").addEventListener("submit", function (event) {
            if (dateDebutField.value >= dateFinField.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de date',
                    text: 'La date de début doit être inférieure à la date de fin.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                event.preventDefault(); // Empêche la soumission du formulaire
            }
        });

        // Gère l'ouverture du modal et met à jour l'ID du véhicule
        document.querySelectorAll('.maintenance').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                document.getElementById('id_vehicule').value = id; // Mettre à jour le champ caché
            });
        });
    });
</script>