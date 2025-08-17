<?php
// Vérifier si l'ID du véhicule est passé dans l'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $vehiculeId = $_GET['id'];

    try {
        // Récupérer les détails du véhicule depuis la base de données
        $query = "SELECT * FROM vehicules WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $vehiculeId]);
        $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicule) {
            throw new Exception("Véhicule non trouvé.");
        }

        // Récupérer les documents associés au véhicule
        $queryDocuments = "SELECT * FROM documents WHERE id_vehicule = :id_vehicule";
        $stmtDocuments = $pdo->prepare($queryDocuments);
        $stmtDocuments->execute(['id_vehicule' => $vehiculeId]);
        $documents = $stmtDocuments->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les statistiques dynamiques
        $queryStats = "SELECT 
                        SUM(d.distance_trajet) AS kilometrage_total, 
                        SUM(a.quantite_litres) AS total_carburant, 
                        COUNT(d.id) AS nombre_trajets, 
                        SUM(TIMESTAMPDIFF(MINUTE, d.date_depart, d.date_arrivee)) AS duree_totale_minutes 
                      FROM deplacements d 
                      LEFT JOIN approvisionnements a ON d.id_vehicule = a.id_vehicule 
                      WHERE d.id_vehicule = :id_vehicule";
        $stmtStats = $pdo->prepare($queryStats);
        $stmtStats->execute(['id_vehicule' => $vehiculeId]);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

        $kilometrage_total = $stats['kilometrage_total'] ?? 0;
        $total_carburant = $stats['total_carburant'] ?? 0;
        $nombre_trajets = $stats['nombre_trajets'] ?? 0;
        $duree_totale_minutes = $stats['duree_totale_minutes'] ?? 0;

        $consommation_moyenne = ($kilometrage_total > 0) ? ($total_carburant / $kilometrage_total) * 100 : 0;
        $heures = floor($duree_totale_minutes / 60);
        $minutes = $duree_totale_minutes % 60;

        // Récupérer les données pour le graphique
        $queryGraphData = "SELECT MONTH(date_depart) AS mois, AVG(distance_trajet) AS consommation_moyenne 
                          FROM deplacements 
                          WHERE id_vehicule = :id_vehicule 
                          GROUP BY MONTH(date_depart)";
        $stmtGraphData = $pdo->prepare($queryGraphData);
        $stmtGraphData->execute(['id_vehicule' => $vehiculeId]);
        $graphData = $stmtGraphData->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les données de consommation par mois
        $queryGraph = "SELECT 
                        MONTH(d.date_depart) AS mois, 
                        AVG(a.quantite_litres / d.distance_trajet * 100) AS consommation_moyenne
                        FROM deplacements d
                        LEFT JOIN approvisionnements a ON d.id_vehicule = a.id_vehicule
                        WHERE d.id_vehicule = :id_vehicule
                        GROUP BY mois
                        ORDER BY mois ASC";
        $stmtGraph = $pdo->prepare($queryGraph);
        $stmtGraph->execute(['id_vehicule' => $vehiculeId]);
        $graphData = $stmtGraph->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Erreur de base de données : " . htmlspecialchars($e->getMessage()));
    }
} else {
    die("ID du véhicule non spécifié.");
}

?>