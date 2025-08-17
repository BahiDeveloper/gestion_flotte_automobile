<?php
// Inclure le fichier de configuration de la base de données
include_once("../database/config.php");

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $id_maintenance = $_POST['id_maintenance'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $description = $_POST['description'];
    $cout = $_POST['cout'];

    // Valider les données (vous pouvez ajouter plus de validations si nécessaire)
    if (empty($id_maintenance)) {
        die("L'ID de la maintenance est obligatoire.");
    }

    // Préparer la requête SQL pour mettre à jour la maintenance
    $sql = "UPDATE maintenance 
            SET date_debut = COALESCE(:date_debut, date_debut), 
                date_fin = COALESCE(:date_fin, date_fin), 
                description = COALESCE(:description, description), 
                cout = COALESCE(:cout, cout) 
            WHERE id = :id_maintenance";

    // Préparer et exécuter la requête
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin,
            ':description' => $description,
            ':cout' => $cout,
            ':id_maintenance' => $id_maintenance
        ]);

        // Rediriger vers la page de gestion des véhicules avec un message de succès
        header("Location: ../gestion_vehicules.php?success_maintenance_edit=1");
        exit();
    } catch (PDOException $e) {
        // En cas d'erreur, rediriger avec un message d'erreur
        header("Location: ../gestion_vehicules.php?error_maintenance_edit=1");
        exit();
    }
} else {
    // Si le formulaire n'a pas été soumis, rediriger vers la page de gestion des véhicules
    header("Location: ../gestion_vehicules.php");
    exit();
}
?>