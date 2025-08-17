<?php
// Inclure le fichier de configuration de la base de données
include_once("../../database/config.php");

// Configuration des en-têtes pour la réponse JSON
header("Content-Type: application/json");

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de l\'approvisionnement requis.'
    ]);
    exit;
}

$id_approvisionnement = intval($_GET['id']);

try {
    // Requête pour récupérer les détails complets de l'approvisionnement
    $query = "
        SELECT 
            ac.*,
            v.marque, 
            v.modele, 
            v.immatriculation,
            v.logo_marque_vehicule,
            c.nom AS chauffeur_nom, 
            c.prenoms AS chauffeur_prenoms
        FROM approvisionnements_carburant ac
        LEFT JOIN vehicules v ON ac.id_vehicule = v.id_vehicule
        LEFT JOIN chauffeurs c ON ac.id_chauffeur = c.id_chauffeur
        WHERE ac.id_approvisionnement = :id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $id_approvisionnement]);

    $approvisionnement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$approvisionnement) {
        echo json_encode([
            'success' => false,
            'message' => 'Approvisionnement non trouvé.'
        ]);
        exit;
    }

    // Formater les données pour la réponse
    $typeCarburantLabels = [
        'essence' => 'Super',
        'diesel' => 'Gasoil',
        'hybride' => 'Essence'
    ];

    $response = [
        'success' => true,
        'data' => [
            'marque' => $approvisionnement['marque'],
            'modele' => $approvisionnement['modele'],
            'immatriculation' => $approvisionnement['immatriculation'],
            'logo_marque_vehicule' => $approvisionnement['logo_marque_vehicule'],
            'chauffeur' => $approvisionnement['chauffeur_nom']
                ? $approvisionnement['chauffeur_nom'] . ' ' . $approvisionnement['chauffeur_prenoms']
                : null,
            'date_approvisionnement' => date('d/m/Y H:i', strtotime($approvisionnement['date_approvisionnement'])),
            'station_service' => $approvisionnement['station_service'],
            'quantite_litres' => number_format($approvisionnement['quantite_litres'], 2, ',', ' '),
            'type_carburant' => $approvisionnement['type_carburant'],
            'type_carburant_label' => $typeCarburantLabels[$approvisionnement['type_carburant']],
            'prix_unitaire' => number_format($approvisionnement['prix_unitaire'], 2, ',', ' '),
            'prix_total' => number_format($approvisionnement['prix_total'], 0, ',', ' '),
            'kilometrage' => number_format($approvisionnement['kilometrage'], 0, ',', ' ')
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    // Gérer les erreurs de base de données
    error_log('Erreur lors de la récupération des détails d\'approvisionnement : ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des détails.'
    ]);
    exit;
}
?>