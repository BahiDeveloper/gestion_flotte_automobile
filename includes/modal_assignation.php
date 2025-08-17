<!-- Modal pour assigner un chauffeur -->
<div class="modal fade" id="assignChauffeurModal" tabindex="-1" aria-labelledby="assignChauffeurModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignChauffeurModalLabel">Assignation du chauffeur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="actions/valider_demande_assignation.php" method="POST">
                    <input type="hidden" id="assignation_id" name="assignation_id">
                    <div class="mb-3">
                        <label for="chauffeur" class="form-label">Choisir un chauffeur</label>
                        <select class="form-select" id="chauffeur" name="chauffeur" required>
                            <option value="" disabled selected>Choisissez un chauffeur</option>
                            <!-- Exemple d'options dynamiques à partir de la base de données -->
                            <?php foreach ($chauffeurs as $chauffeur): ?>
                                <option value="<?= $chauffeur['id'] ?>"><?= htmlspecialchars($chauffeur['nom']) ?> -
                                    <?= htmlspecialchars($chauffeur['prenom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Assigner</button>
                </form>
            </div>
        </div>
    </div>
</div>