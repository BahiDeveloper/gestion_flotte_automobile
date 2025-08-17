<?php
// ges_doc_request.php

// Inclure la configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Requête SQL pour récupérer les documents avec le modèle du véhicule
$sql = "SELECT d.*, v.modele AS modele_vehicule, u.nom AS nom_utilisateur, u.prenom AS prenom_utilisateur
        FROM documents d
        JOIN vehicules v ON d.id_vehicule = v.id
        JOIN utilisateurs u ON d.id_utilisateur = u.id";

// Exécuter la requête
$stmt = $pdo->query($sql);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>