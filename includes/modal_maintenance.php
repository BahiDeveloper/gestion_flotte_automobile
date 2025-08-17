<!-- Modal pour terminer une maintenance -->
<div class="modal fade" id="modalTerminerMaintenance" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Terminer la maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="formTerminerMaintenance" action="actions/terminer_maintenance.php">
                    <input type="hidden" id="maintenance_id" name="maintenance_id">
                    <div class="mb-3">
                        <label for="date_fin" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                    </div>
                    <div class="mb-3">
                        <label for="cout" class="form-label">Coût de la maintenance (FCFA)</label>
                        <input type="number" class="form-control" id="cout" name="cout" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i
                            class="fas fa-check-circle me-2"></i>Confirmer</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        let today = new Date().toISOString().split('T')[0]; // Format YYYY-MM-DD
        let dateField = document.getElementById("date_fin");

        // Définit la valeur par défaut et empêche les dates passées
        dateField.value = today;
        dateField.min = today;

        // Vérifie si la date sélectionnée est valide
        dateField.addEventListener("change", function () {
            if (dateField.value < today) {
                // Affiche une alerte SweetAlert
                Swal.fire({
                    icon: 'warning',
                    title: 'Date invalide',
                    text: 'Veuillez choisir une date actuelle ou future.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });

                // Réinitialise à la date du jour
                dateField.value = today;
            }
        });
    });
</script>