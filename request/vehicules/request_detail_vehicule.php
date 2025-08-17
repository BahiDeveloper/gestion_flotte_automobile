<?php
// Vérification de la présence de l'ID du véhicule
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirection vers la liste des véhicules si l'ID est manquant ou invalide
    header('Location: gestion_vehicules.php');
    exit;
}

$vehiculeId = intval($_GET['id']);

// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Récupération des détails du véhicule
try {
    $query = "SELECT v.*, z.nom_zone 
              FROM vehicules v 
              LEFT JOIN zone_vehicules z ON v.id_zone = z.id 
              WHERE v.id_vehicule = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $vehiculeId]);

    if ($stmt->rowCount() === 0) {
        // Redirection si le véhicule n'existe pas
        $_SESSION['error'] = "Le véhicule demandé n'existe pas.";
        header('Location: gestion_vehicules.php');
        exit;
    }

    $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

    // Formater les données pour l'affichage
    $vehicule['etat'] = ucfirst($vehicule['statut']);
    $vehicule['capacite'] = $vehicule['capacite_passagers'];

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des détails du véhicule.";
    header('Location: gestion_vehicules.php');
    exit;
}

// Récupération des documents associés
try {
    $query = "SELECT * FROM documents_administratifs 
              WHERE id_vehicule = :id_vehicule 
              ORDER BY date_expiration DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id_vehicule' => $vehiculeId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les données des documents
    foreach ($documents as &$doc) {
        $doc['date_debut'] = date('d/m/Y', strtotime($doc['date_emission']));
        $doc['date_fin'] = date('d/m/Y', strtotime($doc['date_expiration']));
        $doc['status'] = (strtotime($doc['date_expiration']) > time()) ? 'Actif' : 'Expiré';
        $doc['file_path'] = $doc['fichier_url'] ?? '';
    }

} catch (PDOException $e) {
    // En cas d'erreur, initialiser un tableau vide
    $documents = [];
}

// Récupération des statistiques du véhicule
try {
    // Kilométrage total parcouru
    $queryKm = "SELECT 
                  COALESCE(SUM(km_retour - km_depart), 0) as kilometrage_total 
                FROM reservations_vehicules 
                WHERE id_vehicule = :id_vehicule 
                  AND statut = 'terminee' 
                  AND km_retour IS NOT NULL 
                  AND km_depart IS NOT NULL";
    $stmtKm = $pdo->prepare($queryKm);
    $stmtKm->execute(['id_vehicule' => $vehiculeId]);
    $kilometrage_total = $stmtKm->fetchColumn() ?: 0;

    // Consommation de carburant
    $queryConsommation = "SELECT 
                            SUM(quantite_litres) as total_carburant,
                            SUM(prix_total) as cout_total_carburant
                          FROM approvisionnements_carburant 
                          WHERE id_vehicule = :id_vehicule";
    $stmtConsommation = $pdo->prepare($queryConsommation);
    $stmtConsommation->execute(['id_vehicule' => $vehiculeId]);
    $consommationData = $stmtConsommation->fetch(PDO::FETCH_ASSOC);

    $total_carburant = $consommationData['total_carburant'] ?: 0;
    $cout_total_carburant = $consommationData['cout_total_carburant'] ?: 0;

    // Calcul de la consommation moyenne (L/100km)
    $consommation_moyenne = ($kilometrage_total > 0 && $total_carburant > 0)
        ? number_format(($total_carburant * 100) / $kilometrage_total, 2)
        : 'N/A';

    // Nombre de trajets
    $queryTrajets = "SELECT COUNT(*) FROM reservations_vehicules 
                     WHERE id_vehicule = :id_vehicule 
                     AND statut IN ('terminee', 'en_cours')";
    $stmtTrajets = $pdo->prepare($queryTrajets);
    $stmtTrajets->execute(['id_vehicule' => $vehiculeId]);
    $nombre_trajets = $stmtTrajets->fetchColumn() ?: 0;

    // Durée totale des déplacements (en heures)
    $queryDuree = "SELECT 
                     SUM(TIMESTAMPDIFF(MINUTE, date_depart, COALESCE(date_retour_effective, NOW()))) as duree_minutes
                   FROM reservations_vehicules 
                   WHERE id_vehicule = :id_vehicule 
                   AND statut IN ('terminee', 'en_cours')";
    $stmtDuree = $pdo->prepare($queryDuree);
    $stmtDuree->execute(['id_vehicule' => $vehiculeId]);
    $duree_minutes = $stmtDuree->fetchColumn() ?: 0;

    // Conversion en heures et minutes
    $heures = floor($duree_minutes / 60);
    $minutes = $duree_minutes % 60;

    // Coût total des maintenances
    $queryMaintenance = "SELECT COALESCE(SUM(cout), 0) as total_maintenance 
                         FROM maintenances 
                         WHERE id_vehicule = :id_vehicule 
                         AND statut = 'terminee'";
    $stmtMaintenance = $pdo->prepare($queryMaintenance);
    $stmtMaintenance->execute(['id_vehicule' => $vehiculeId]);
    $totalMaintenance = $stmtMaintenance->fetchColumn() ?: 0;

    // Coût total des documents administratifs
$queryDocuments = "SELECT COALESCE(SUM(prix), 0) as total_documents 
FROM documents_administratifs 
WHERE id_vehicule = :id_vehicule";
$stmtDocuments = $pdo->prepare($queryDocuments);
$stmtDocuments->execute(['id_vehicule' => $vehiculeId]);
$totalDocuments = $stmtDocuments->fetchColumn() ?: 0;


    // Données pour les graphiques
    // 1. Consommation par mois
    $queryGraphConsommation = "SELECT 
                                DATE_FORMAT(date_approvisionnement, '%Y-%m') as mois,
                                SUM(quantite_litres) as litres,
                                SUM(prix_total) as cout
                              FROM approvisionnements_carburant
                              WHERE id_vehicule = :id_vehicule
                              GROUP BY mois
                              ORDER BY mois ASC
                              LIMIT 12";
    $stmtGraphConsommation = $pdo->prepare($queryGraphConsommation);
    $stmtGraphConsommation->execute(['id_vehicule' => $vehiculeId]);
    $graphData = $stmtGraphConsommation->fetchAll(PDO::FETCH_ASSOC);

    // 2. Coûts de maintenance par type
    $queryGraphMaintenance = "SELECT 
                                type_maintenance,
                                COUNT(*) as nombre,
                                SUM(cout) as cout_total
                              FROM maintenances
                              WHERE id_vehicule = :id_vehicule
                              GROUP BY type_maintenance";
    $stmtGraphMaintenance = $pdo->prepare($queryGraphMaintenance);
    $stmtGraphMaintenance->execute(['id_vehicule' => $vehiculeId]);
    $maintenanceData = $stmtGraphMaintenance->fetchAll(PDO::FETCH_ASSOC);

    // 3. Évolution des coûts de carburant
    $queryFuelCost = "SELECT 
                        DATE_FORMAT(date_approvisionnement, '%Y-%m') as mois,
                        AVG(prix_unitaire) as prix_moyen,
                        SUM(prix_total) as cout_total
                      FROM approvisionnements_carburant
                      WHERE id_vehicule = :id_vehicule
                      GROUP BY mois
                      ORDER BY mois ASC
                      LIMIT 12";
    $stmtFuelCost = $pdo->prepare($queryFuelCost);
    $stmtFuelCost->execute(['id_vehicule' => $vehiculeId]);
    $fuelCostData = $stmtFuelCost->fetchAll(PDO::FETCH_ASSOC);

    // Initialiser les statistiques si elles sont vides
    if (empty($graphData) && empty($maintenanceData) && empty($fuelCostData)) {
        $stats = [];
    } else {
        $stats = [
            'kilometrage_total' => $kilometrage_total,
            'consommation_moyenne' => $consommation_moyenne,
            'cout_total_carburant' => $cout_total_carburant,
            'total_carburant' => $total_carburant,
            'nombre_trajets' => $nombre_trajets,
            'duree_heures' => $heures,
            'duree_minutes' => $minutes,
            'total_maintenance' => $totalMaintenance,
            'total_documents' => $totalDocuments
        ];
    }

} catch (PDOException $e) {
    // En cas d'erreur, initialiser des tableaux vides
    $stats = [];
    $graphData = [];
    $maintenanceData = [];
    $fuelCostData = [];
}
?>