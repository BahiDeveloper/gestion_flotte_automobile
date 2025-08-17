<?php
// Fichier pour obtenir les détails d'un approvisionnement via AJAX
// Inclure le fichier de configuration
include_once(__DIR__ . "/../../../database/config.php");

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID non fourni']);
    exit;
}

$id_approvisionnement = intval($_GET['id']);

try {
    // Récupérer les détails de l'approvisionnement
    $stmt = $pdo->prepare("
        SELECT a.*, 
               c.nom, c.prenoms,
               v.marque, v.modele, v.immatriculation
        FROM approvisionnements_carburant a
        LEFT JOIN chauffeurs c ON a.id_chauffeur = c.id_chauffeur
        LEFT JOIN vehicules v ON a.id_vehicule = v.id_vehicule
        WHERE a.id_approvisionnement = ?
    ");
    $stmt->execute([$id_approvisionnement]);
    $approvisionnement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$approvisionnement) {
        echo json_encode(['success' => false, 'error' => 'Approvisionnement non trouvé']);
        exit;
    }

    // Formater certaines données pour l'affichage
    $approvisionnement['date_formattee'] = date('d/m/Y H:i', strtotime($approvisionnement['date_approvisionnement']));
    $approvisionnement['prix_unitaire_formatte'] = number_format($approvisionnement['prix_unitaire'], 2, ',', ' ');
    $approvisionnement['prix_total_formatte'] = number_format($approvisionnement['prix_total'], 0, ',', ' ');
    $approvisionnement['quantite_litres_formattee'] = number_format($approvisionnement['quantite_litres'], 2, ',', ' ');
    $approvisionnement['kilometrage_formatte'] = number_format($approvisionnement['kilometrage'], 0, ',', ' ');
    $approvisionnement['nom_complet_chauffeur'] = $approvisionnement['nom'] ? $approvisionnement['nom'] . ' ' . $approvisionnement['prenoms'] : 'Non spécifié';
    $approvisionnement['vehicule_infos'] = $approvisionnement['marque'] . ' ' . $approvisionnement['modele'] . ' (' . $approvisionnement['immatriculation'] . ')';

    // Calculer le rendement si possible (distance parcourue depuis dernier approvisionnement)
    if (!empty($approvisionnement['id_vehicule'])) {
        $stmt = $pdo->prepare("
            SELECT kilometrage, date_approvisionnement
            FROM approvisionnements_carburant
            WHERE id_vehicule = ? AND date_approvisionnement < ?
            ORDER BY date_approvisionnement DESC
            LIMIT 1
        ");
        $stmt->execute([
            $approvisionnement['id_vehicule'],
            $approvisionnement['date_approvisionnement']
        ]);
        $precedent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($precedent) {
            $distance_parcourue = $approvisionnement['kilometrage'] - $precedent['kilometrage'];
            $approvisionnement['distance_parcourue'] = $distance_parcourue;
            $approvisionnement['distance_parcourue_formattee'] = number_format($distance_parcourue, 0, ',', ' ');

            if ($distance_parcourue > 0 && $approvisionnement['quantite_litres'] > 0) {
                // Calcul de la consommation pour ce ravitaillement (L/100km)
                $consommation = ($approvisionnement['quantite_litres'] / $distance_parcourue) * 100;
                $approvisionnement['consommation'] = $consommation;
                $approvisionnement['consommation_formattee'] = number_format($consommation, 2, ',', ' ');

                // Calcul du coût au km
                $cout_km = $approvisionnement['prix_total'] / $distance_parcourue;
                $approvisionnement['cout_km'] = $cout_km;
                $approvisionnement['cout_km_formatte'] = number_format($cout_km, 2, ',', ' ');
            }
        }
    }

    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $approvisionnement
    ]);

} catch (PDOException $e) {
    // En cas d'erreur, renvoyer un message d'erreur
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}