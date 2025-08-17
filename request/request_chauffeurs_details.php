<?php
// Vérifier si un ID de chauffeur est fourni
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $chauffeur_id = $_GET['id'];

    try {
        // Récupérer les détails du chauffeur
        $sql_chauffeur = "SELECT * FROM chauffeurs WHERE id = :id";
        $stmt_chauffeur = $pdo->prepare($sql_chauffeur);
        $stmt_chauffeur->execute(['id' => $chauffeur_id]);
        $chauffeur = $stmt_chauffeur->fetch(PDO::FETCH_ASSOC);

        if (!$chauffeur) {
            throw new Exception("Chauffeur non trouvé.");
        }

        // ---------------Start requête provenant du véhicule ---------- 
        // Récupérer les statistiques dynamiques
        $queryStats = "SELECT 
                SUM(d.kilometrage_total) AS kilometrage_total,
                SUM(a.quantite_litres) AS total_carburant, 
                SUM(a.cout_total) AS cout_total_carburant, 
                COUNT(d.id) AS nombre_trajets, 
                SUM(TIMESTAMPDIFF(MINUTE, d.date_depart, d.date_arrivee)) AS duree_totale_minutes 
              FROM deplacements d 
              LEFT JOIN approvisionnements a ON d.id_vehicule = a.id_vehicule 
              WHERE d.id_chauffeur = :id_chauffeur";
        $stmtStats = $pdo->prepare($queryStats);
        $stmtStats->execute(['id_chauffeur' => $chauffeur_id]);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);


        $kilometrage_total = $stats['kilometrage_total'] ?? 0;
        // var_dump($kilometrage_total);
        // exit();
        $total_carburant = $stats['total_carburant'] ?? 0;
        $cout_total_carburant = $stats['cout_total_carburant'] ?? 0;
        $nombre_trajets = $stats['nombre_trajets'] ?? 0;
        $duree_totale_minutes = $stats['duree_totale_minutes'] ?? 0;

        // Calculer la consommation moyenne
        $consommation_moyenne = ($kilometrage_total > 0) ? number_format(($total_carburant / $kilometrage_total) * 100, 2) : 0;

        // Convertir la durée totale en heures et minutes
        $heures = floor($duree_totale_minutes / 60);
        $minutes = $duree_totale_minutes % 60;
        // ---------------End requête provenant du véhicule ---------- 

        // Récupérer les derniers déplacements du chauffeur
        $sql_deplacements = "
            SELECT d.*, v.* 
            FROM deplacements d
            JOIN vehicules v ON d.id_vehicule = v.id
            WHERE d.id_chauffeur = :id_chauffeur
            ORDER BY d.date_depart DESC
            LIMIT 5";
        $stmt_deplacements = $pdo->prepare($sql_deplacements);
        $stmt_deplacements->execute(['id_chauffeur' => $chauffeur_id]);
        $deplacements = $stmt_deplacements->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Erreur de base de données : " . htmlspecialchars($e->getMessage()));
    }
} else {
    die("ID du chauffeur non spécifié.");
}
?>