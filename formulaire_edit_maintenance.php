<?php
// Inclure le fichier de configuration de la base de données
include_once("database/config.php");

// Vérifier si l'ID de la maintenance est passé en paramètre
if (isset($_GET['id'])) {
    $id_maintenance = $_GET['id'];

    // Récupérer les détails de la maintenance depuis la base de données
    $sql = "SELECT * FROM maintenance WHERE id = :id_maintenance";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_maintenance' => $id_maintenance]);
    $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$maintenance) {
        die("Maintenance non trouvée.");
    }
} else {
    die("ID de la maintenance non spécifié.");
}
?>

<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->

<h2><i class="fas fa-calendar-alt me-2"></i> Modifier la maintenance</h2>
<hr>
<form action="actions/modifier_maintenance.php" method="POST" class="my-3">
    <input type="hidden" name="id_maintenance" value="<?= htmlspecialchars($maintenance['id']) ?>">

    <div class="mb-3">
        <label for="date_debut" class="form-label">Date de début</label>
        <input type="date" class="form-control" id="date_debut" name="date_debut"
            value="<?= htmlspecialchars($maintenance['date_debut']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="date_fin" class="form-label">Date de fin</label>
        <input type="date" class="form-control" id="date_fin" name="date_fin"
            value="<?= htmlspecialchars($maintenance['date_fin']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description"
            required><?= htmlspecialchars($maintenance['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label for="cout" class="form-label">Coût</label>
        <input type="number" step="0.01" class="form-control" id="cout" name="cout"
            value="<?= htmlspecialchars(str_replace(' ', '', $maintenance['cout'])) ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    <a href="gestion_vehicules.php" class="btn btn-danger"><i class="fas fa-arrow-left me-2"></i> Annuler</a>
</form>

<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer   -->