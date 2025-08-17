<?php
// Inclure le fichier de configuration de la base de données
include_once("../database/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_vehicule = $_POST['id_vehicule'];
    $description = $_POST['description'];
    $date_debut = $_POST['date_debut'];
    $date_fin_prevue = $_POST['date_fin_prevue']; 

    // Vérifier si la date de début est inférieure à la date de fin
    if ($date_debut >= $date_fin_prevue) {
        die("Erreur : La date de début doit être inférieure à la date de fin.");
    }

    // Vérifier si le véhicule existe
    $sql_check_vehicle = "SELECT * FROM vehicules WHERE id = :id_vehicule";
    $stmt_check = $pdo->prepare($sql_check_vehicle);
    $stmt_check->execute([':id_vehicule' => $id_vehicule]);

    if ($stmt_check->rowCount() == 0) {
        die("Erreur : Le véhicule avec l'ID $id_vehicule n'existe pas.");
    }

    // Insérer la maintenance dans la base de données
    $sql = "INSERT INTO maintenance (id_vehicule, description, date_debut, date_fin_prevue) VALUES (:id_vehicule, :description, :date_debut, :date_fin_prevue)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_vehicule' => $id_vehicule,
        ':description' => $description,
        ':date_debut' => $date_debut,
        ':date_fin_prevue' => $date_fin_prevue
    ]);

    // Mettre à jour le statut du véhicule
    $sql_update = "UPDATE vehicules SET etat = 'En maintenance' WHERE id = :id_vehicule";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([':id_vehicule' => $id_vehicule]);

    // Rediriger vers la page de gestion des véhicules avec un message de succès
    header("Location: ../gestion_vehicules.php?success_planification_maintenance=1");
    exit();
}
?>