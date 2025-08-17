<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier les données reçues
// var_dump($_POST);

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer l'ID de l'assignation
    $id_assignation = $_POST['id_assignation'];

    // Récupérer les autres données du formulaire
    $id_vehicule = $_POST['id_vehicule'];
    $trajet = $_POST['trajet_A'] . " - " . $_POST['trajet_B'];
    $date_depart_prevue = $_POST['date_depart_prevue'];
    $date_arrivee_prevue = $_POST['date_arrivee_prevue'];

    // Mettre à jour l'assignation dans la base de données
    $sql_update = "UPDATE deplacements 
                   SET id_vehicule = :id_vehicule, 
                       trajet = :trajet, 
                       date_depart_prevue = :date_depart_prevue, 
                       date_arrivee_prevue = :date_arrivee_prevue 
                   WHERE id = :id_assignation";
    $stmt_update = $pdo->prepare($sql_update);

    try {
        $stmt_update->execute([
            ':id_vehicule' => $id_vehicule,
            ':trajet' => $trajet,
            ':date_depart_prevue' => $date_depart_prevue,
            ':date_arrivee_prevue' => $date_arrivee_prevue,
            ':id_assignation' => $id_assignation
        ]);

        // Rediriger ou afficher un message de succès
        header("Location: ../gestion_vehicules.php?success_assignation_edit=1");
        exit();
    } catch (\Throwable $th) {
        // Gérer l'erreur
        header("Location: ../gestion_vehicules.php?error_assignation_edit=1");
        exit();
    }
}
?>