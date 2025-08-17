<!-- Modal Modifier Assignation -->
<div class="modal fade" id="modifierAssignationModal" tabindex="-1" aria-labelledby="modifierAssignationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierAssignationModalLabel">Modifier l'assignation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="actions/modifier_assignation.php" method="POST">
                    <input type="hidden" name="id_assignation" id="edit-id-assignation">
                    <input type="hidden" name="id_chauffeur" id="edit-chauffeur">
                    <input type="hidden" name="id_vehicule" id="edit-vehicule">

                    <div class="mb-3">
                        <label for="edit-trajet-A" class="form-label">Point de départ</label>
                        <input type="text" class="form-control" id="edit-trajet-A" name="trajet_A" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-trajet-B" class="form-label">Point d'arrivée</label>
                        <input type="text" class="form-control" id="edit-trajet-B" name="trajet_B" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-date-depart" class="form-label">Date de départ prévue</label>
                        <input type="datetime-local" class="form-control" id="edit-date-depart"
                            name="date_depart_prevue" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-date-arrivee" class="form-label">Date d'arrivée prévue</label>
                        <input type="datetime-local" class="form-control" id="edit-date-arrivee"
                            name="date_arrivee_prevue" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>