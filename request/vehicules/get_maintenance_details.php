<?php
// Inclure le fichier de configuration de la base de données
include_once("../../database/config.php");

// Configuration des en-têtes pour la réponse JSON
header("Content-Type: application/json");

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de la maintenance requis.'
    ]);
    exit;
}

$id_maintenance = intval($_GET['id']);

try {
    // Requête pour récupérer les détails complets de la maintenance
    $query = "
        SELECT 
            m.*,
            v.marque, 
            v.modele, 
            v.immatriculation,
            v.logo_marque_vehicule,
            z.nom_zone
        FROM maintenances m
        LEFT JOIN vehicules v ON m.id_vehicule = v.id_vehicule
        LEFT JOIN zone_vehicules z ON v.id_zone = z.id
        WHERE m.id_maintenance = :id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $id_maintenance]);

    $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$maintenance) {
        echo json_encode([
            'success' => false,
            'message' => 'Maintenance non trouvée.'
        ]);
        exit;
    }

    // Traductions et formatages
    $type_maintenance_labels = [
        'preventive' => 'Préventive',
        'corrective' => 'Corrective',
        'revision' => 'Révision'
    ];

    $statut_labels = [
        'planifiee' => 'Planifiée',
        'en_cours' => 'En cours',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée'
    ];

    $response = [
        'success' => true,
        'data' => [
            'marque' => $maintenance['marque'],
            'modele' => $maintenance['modele'],
            'immatriculation' => $maintenance['immatriculation'],
            'logo_marque_vehicule' => $maintenance['logo_marque_vehicule'],
            'zone' => $maintenance['nom_zone'] ?? 'Non définie',
            'type_maintenance' => $type_maintenance_labels[$maintenance['type_maintenance']] ?? $maintenance['type_maintenance'],
            'description' => $maintenance['description'],
            'date_debut' => date('d/m/Y', strtotime($maintenance['date_debut'])),
            'date_fin_prevue' => date('d/m/Y', strtotime($maintenance['date_fin_prevue'])),
            'date_fin_effective' => $maintenance['date_fin_effective']
                ? date('d/m/Y', strtotime($maintenance['date_fin_effective']))
                : 'Non terminée',
            'cout' => $maintenance['cout']
                ? number_format($maintenance['cout'], 0, ',', ' ') . ' FCFA'
                : 'Non renseigné',
            'kilometrage' => number_format($maintenance['kilometrage'] ?? 0, 0, ',', ' '),
            'prestataire' => $maintenance['prestataire'] ?? 'Non spécifié',
            'statut' => $statut_labels[$maintenance['statut']] ?? $maintenance['statut']
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    // Gérer les erreurs de base de données
    error_log('Erreur lors de la récupération des détails de maintenance : ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des détails.'
    ]);
    exit;
}
?>