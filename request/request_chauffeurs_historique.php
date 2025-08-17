<?php

// Récupérer l'historique des chauffeurs depuis la base de données
$sql = "SELECT c.nom, c.prenom, r.nombre_km_parcourus, r.consommation, r.periode, r.date_rapport 
                FROM rapports_flotte r
                JOIN chauffeurs c ON r.id_chauffeur = c.id
                ORDER BY r.date_rapport DESC";
$stmt = $pdo->query($sql);
$historique = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grouper les données par chauffeur
$chauffeurs_historique = [];
foreach ($historique as $row) {
    $chauffeur_key = $row['nom'] . ' ' . $row['prenom'];
    if (!isset($chauffeurs_historique[$chauffeur_key])) {
        $chauffeurs_historique[$chauffeur_key] = [
            'nom' => $row['nom'],
            'prenom' => $row['prenom'],
            'courses' => 0,
            'km_parcourus' => 0,
            'consommation_totale' => 0,
            'rapports_flotte' => []
        ];
    }
    $chauffeurs_historique[$chauffeur_key]['courses']++;
    $chauffeurs_historique[$chauffeur_key]['km_parcourus'] += $row['nombre_km_parcourus'];
    $chauffeurs_historique[$chauffeur_key]['consommation_totale'] += $row['consommation'];
    $chauffeurs_historique[$chauffeur_key]['rapports_flotte'][] = $row;
}

?>