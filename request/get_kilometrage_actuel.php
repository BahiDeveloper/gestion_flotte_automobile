<?php
// get_kilometrage_actuel.php

// Inclure la configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

if (isset($_GET['assignation_id'])) {
    $assignation_id = $_GET['assignation_id'];

    // Récupérer le kilométrage actuel du véhicule
    $sql = "SELECT v.kilometrage_actuel 
            FROM vehicules v 
            JOIN deplacements d ON v.id = d.id_vehicule 
            WHERE d.id = :assignation_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':assignation_id' => $assignation_id]);
    $kilometrage_actuel = $stmt->fetchColumn();

    // Retourner le kilométrage actuel en JSON
    echo json_encode(['kilometrage_actuel' => $kilometrage_actuel]);
}
?>