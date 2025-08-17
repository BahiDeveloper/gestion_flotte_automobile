<!-- Modal Approvisionnement -->
<div class="modal fade" id="fuelModal" tabindex="-1" aria-labelledby="fuelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fuelModalLabel">Approvisionnement du véhicule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fuelForm" method="POST" action="actions/approvisionner_vehicule.php">
                    <!-- Champ caché pour l'ID du véhicule -->
                    <input type="hidden" id="vehicleId" name="vehicleId" value="">

                    <!-- Coût du carburant -->
                    <div class="mb-3">
                        <label for="fuelCost" class="form-label">Coût du carburant (FCFA)</label>
                        <input type="number" class="form-control" id="fuelCost" placeholder="Entrez le coût total"
                            required>
                        <small id="fuelCostHelp" class="form-text text-muted">
                            Coût unitaire du litre : <span id="unitFuelCost">0</span> FCFA
                        </small>
                    </div>

                    <!-- Quantité de carburant (calculée automatiquement) -->
                    <div class="mb-3">
                        <label for="fuelAmount" class="form-label">Quantité de carburant (en litres)</label>
                        <input type="text" class="form-control" id="fuelAmount" value="0" disabled>
                    </div>

                    <!-- Affichage du type de carburant dans la modal -->
                    <p><strong>Type de carburant :</strong> <span id="fuelTypeDisplay"></span></p>

                    <!-- Bouton de soumission -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-success">Valider l'approvisionnement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>