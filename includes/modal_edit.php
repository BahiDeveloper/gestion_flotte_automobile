<!-- Modal pour modifier un véhicule -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVehicleModalLabel">
                    <i class="fas fa-edit me-2"></i>Modifier un véhicule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulaire de modification -->
                <form id="editVehicleForm" action="actions/update_vehicle.php" method="POST">
                    <input type="hidden" id="editVehicleId" name="id">
                    <div class="mb-3">
                        <label for="editMarque" class="form-label">Marque</label>
                        <input type="text" class="form-control" id="editMarque" name="marque" required>
                    </div>
                    <div class="mb-3">
                        <label for="editModele" class="form-label">Modèle</label>
                        <input type="text" class="form-control" id="editModele" name="modele" required>
                    </div>
                    <div class="mb-3">
                        <label for="editImmatriculation" class="form-label">Immatriculation</label>
                        <input type="text" class="form-control" id="editImmatriculation" name="immatriculation"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="editTypeVehicule" class="form-label">Type de véhicule</label>
                        <input type="text" class="form-control" id="editTypeVehicule" name="type_vehicule" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCapacite" class="form-label">Capacité</label>
                        <input type="number" class="form-control" id="editCapacite" name="capacite" required>
                    </div>
                    <!--<div class="mb-3">
                        <label for="editEtat" class="form-label">État</label>
                        <select class="form-select" id="editEtat" name="etat" required>
                            <option value="Disponible">Disponible</option>
                            <option value="En maintenance">En maintenance</option>
                            <option value="En déplacement">En déplacement</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="editKilometrage" class="form-label">Kilométrage actuel</label>
                        <input type="number" class="form-control" id="editKilometrage" name="kilometrage_actuel"
                            required>
                    </div> -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Fermer
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>