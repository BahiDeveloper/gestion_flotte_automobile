<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Inclure les requêtes nécessaires
include_once("request".DIRECTORY_SEPARATOR."request_assignations_detail.php");

?>

<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->
    <h1>Modifier l'Assignation</h1>
    <form method="POST" action="actions/modifier_assignation.php">

        <label for="id_chauffeur">Chauffeur:</label>
        <input type="text" name="id_chauffeur" value="<?php echo htmlspecialchars($assignation['id_chauffeur']); ?>" required><br>

        <label for="id_vehicule">Véhicule:</label>
        <input type="text" name="id_vehicule" value="<?php echo htmlspecialchars($assignation['id_vehicule']); ?>" required><br>

        <label for="trajet_A">Point de départ:</label>
        <input type="text" name="trajet_A" value="<?php echo htmlspecialchars(explode(' - ', $assignation['trajet'])[0]); ?>" required><br>

        <label for="trajet_B">Point d'arrivée:</label>
        <input type="text" name="trajet_B" value="<?php echo htmlspecialchars(explode(' - ', $assignation['trajet'])[1]); ?>" required><br>

        <label for="date_depart">Date de départ:</label>
        <input type="datetime-local" name="date_depart" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($assignation['date_depart']))); ?>" required><br>

        <label for="date_arrivee">Date d'arrivée:</label>
        <input type="datetime-local" name="date_arrivee" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($assignation['date_arrivee']))); ?>" required><br>

        <button type="submit">Mettre à jour l'assignation</button>

    </form>

<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer   -->