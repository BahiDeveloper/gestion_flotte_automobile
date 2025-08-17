<!-- Modal pour saisir le coût final de maintenance -->
<div class="modal fade" id="coutFinalModal" tabindex="-1" aria-labelledby="coutFinalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="coutFinalModalLabel">
                    <i class="fas fa-money-bill-wave me-2"></i>Finaliser la maintenance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="coutFinalForm" method="POST">

                <!-- Ajouter ces champs cachés -->
                <input type="hidden" name="action" value="terminer">
                <input type="hidden" name="maintenance_id" id="maintenance_id" value="">

                <div class="modal-body">
                    <p class="mb-3">
                        Veuillez saisir le coût final de la maintenance avant de la terminer.
                    </p>
                    <div class="mb-3">
                        <label for="cout_final" class="form-label">Coût final (FCFA) <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                            <input type="number" class="form-control" id="cout_final" name="cout_final" min="0"
                                required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes_finales" class="form-label">Notes ou observations</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-comment-alt"></i></span>
                            <textarea class="form-control" id="notes_finales" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-2"></i>Terminer la maintenance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>