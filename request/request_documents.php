<?php
// Récupérer la liste des documents
$sql = "SELECT d.*, u.nom AS nom_utilisateur, v.marque, v.modele 
        FROM documents d
        JOIN utilisateurs u ON d.id_utilisateur = u.id
        JOIN vehicules v ON d.id_vehicule = v.id";
$stmt = $pdo->query($sql);
$documents = $stmt->fetchAll();
?>