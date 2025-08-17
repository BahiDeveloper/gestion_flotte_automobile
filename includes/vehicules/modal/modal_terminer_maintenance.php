<!-- Modal pour terminer une maintenance -->
<div class="modal fade" id="terminerMaintenanceModal" tabindex="-1" aria-labelledby="terminerMaintenanceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="terminerMaintenanceModalLabel">Terminer la maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="terminerMaintenanceFormModal" method="POST"
                    action="actions/vehicules/maintenance_vehicule.php">
                    <input type="hidden" name="action" value="terminer">
                    <input type="hidden" id="maintenance_id_modal" name="maintenance_id" value="">
                    <input type="hidden" id="vehicule_id_modal" name="id" value="">

                    <div class="mb-3">
                        <label for="cout_final_modal" class="form-label">Coût final (FCFA)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-money-bill"></i></span>
                            <input type="number" class="form-control" id="cout_final_modal" name="cout_final" min="0"
                                required>
                        </div>
                    </div>

                    <!-- <div class="mb-3">
                        <label for="prestataire_modal" class="form-label">Prestataire</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                            <input type="text" class="form-control" id="prestataire_modal" name="prestataire">
                        </div>
                    </div> -->

                    <div class="mb-3">
                        <label for="notes_modal" class="form-label">Notes / Observations</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-comment"></i></span>
                            <textarea class="form-control" id="notes_modal" name="notes" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-2"></i>Terminer la maintenance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>