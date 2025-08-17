<!-- Modal pour terminer la course -->
<div class="modal fade" id="modalTerminerCourse" tabindex="-1" aria-labelledby="modalTerminerCourseLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTerminerCourseLabel">Terminer la course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTerminerCourse" action="actions/terminer_assignation.php" method="POST">
                    <input type="hidden" name="assignation_id" id="assignation_id">
                    <div class="mb-3">
                        <label for="kilometrage_fin" class="form-label">Kilométrage de fin de course</label>
                        <input type="number" class="form-control" id="kilometrage_fin" name="kilometrage_fin" required>
                        <small class="text-muted">Le kilométrage de fin doit être supérieur à <span id="kilometrage_actuel_vehicule"></span>.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="submit" form="formTerminerCourse" class="btn btn-primary">Valider</button>
            </div>
        </div>
    </div>
</div>