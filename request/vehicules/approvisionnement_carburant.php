<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si la session est active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'ID du véhicule est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: gestion_vehicules.php');
    exit;
}

$id_vehicule = intval($_GET['id']);

// Récupérer les informations du véhicule
try {
    $stmt = $pdo->prepare("
        SELECT v.*, z.nom_zone 
        FROM vehicules v
        LEFT JOIN zone_vehicules z ON v.id_zone = z.id
        WHERE v.id_vehicule = ?
    ");
    $stmt->execute([$id_vehicule]);
    $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicule) {
        $_SESSION['error'] = "Véhicule non trouvé";
        header('Location: gestion_vehicules.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des données du véhicule: " . $e->getMessage();
    header('Location: gestion_vehicules.php');
    exit;
}

// Récupérer la liste des chauffeurs
try {
    $stmt = $pdo->prepare("
        SELECT id_chauffeur, nom, prenoms 
        FROM chauffeurs 
        WHERE statut != 'indisponible'
        ORDER BY nom, prenoms
    ");
    $stmt->execute();
    $chauffeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $chauffeurs = [];
}

// Récupérer l'historique des approvisionnements pour ce véhicule
try {
    $stmt = $pdo->prepare("
        SELECT a.*, c.nom, c.prenoms 
        FROM approvisionnements_carburant a
        LEFT JOIN chauffeurs c ON a.id_chauffeur = c.id_chauffeur
        WHERE a.id_vehicule = ?
        ORDER BY a.date_approvisionnement DESC
        LIMIT 20
    ");
    $stmt->execute([$id_vehicule]);
    $historique = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $historique = [];
}

// Traiter le formulaire d'approvisionnement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // S'assurer que la session est active
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    try {
        // Validation des données
        $date_approvisionnement = $_POST['date_approvisionnement'] ?? date('Y-m-d H:i:s');
        $id_chauffeur = !empty($_POST['id_chauffeur']) ? intval($_POST['id_chauffeur']) : null;
        $quantite_litres = floatval($_POST['quantite_litres']);

        // Déterminer le prix unitaire en fonction du type de carburant du véhicule
        $prix_carburants = [
            'Super' => 875, // Super sans plomb
            'Gasoil' => 715, // Gasoil
            'Essence' => 875  // Assimilé au Super
        ];
        $prix_unitaire = $prix_carburants[$vehicule['type_carburant']] ?? 0;

        // Calculer et arrondir le prix total à l'entier
        // $prix_total = round($quantite_litres * $prix_unitaire);
        $prix_total = round($_POST['cout_total']);
        // var_dump($prix_total);
        // exit;

        $kilometrage = intval($_POST['kilometrage']);
        $station_service = $_POST['station_service'] ?? null;

        // Utiliser le type de carburant du véhicule
        $type_carburant_mapping = [
            'Super' => 'essence',
            'Gasoil' => 'diesel',
            'Essence' => 'hybride'
        ];
        $type_carburant = $type_carburant_mapping[$vehicule['type_carburant']] ?? 'essence';

        // Vérifier que le kilométrage est supérieur à celui enregistré
        if ($kilometrage < $vehicule['kilometrage_actuel']) {
            throw new Exception("Le kilométrage ne peut pas être inférieur au kilométrage actuel (" . $vehicule['kilometrage_actuel'] . " km)");
        }

        // Insérer l'approvisionnement
        $stmt = $pdo->prepare("
            INSERT INTO approvisionnements_carburant (
                id_vehicule, id_chauffeur, date_approvisionnement, 
                quantite_litres, prix_unitaire, prix_total, 
                kilometrage, station_service, type_carburant
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_vehicule,
            $id_chauffeur,
            $date_approvisionnement,
            $quantite_litres,
            $prix_unitaire,
            $prix_total,
            $kilometrage,
            $station_service,
            $type_carburant
        ]);

        // Mettre à jour le kilométrage du véhicule
        $stmt = $pdo->prepare("
            UPDATE vehicules 
            SET kilometrage_actuel = ?, updated_at = NOW()
            WHERE id_vehicule = ?
        ");
        $stmt->execute([$kilometrage, $id_vehicule]);

        // Enregistrer l'activité dans le journal
        if (isset($_SESSION['id_utilisateur'])) {
            $stmt = $pdo->prepare("
                INSERT INTO journal_activites (
                    id_utilisateur, type_activite, description, ip_address
                ) VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['id_utilisateur'],
                'approvisionnement_carburant',
                "Approvisionnement de " . $quantite_litres . " litres pour le véhicule " . $vehicule['marque'] . " " . $vehicule['modele'] . " (" . $vehicule['immatriculation'] . ")",
                $_SERVER['REMOTE_ADDR']
            ]);
        }

        $_SESSION['success'] = "Approvisionnement enregistré avec succès";
        header('Location: approvisionnement_carburant.php?id=' . $id_vehicule);
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur lors de l'enregistrement de l'approvisionnement: " . $e->getMessage();
    }
}

// Calculer les statistiques de consommation
$statistiques = [
    'consommation_moyenne' => 0,
    'cout_moyen_km' => 0,
    'distance_totale' => 0,
    'carburant_total' => 0,
    'cout_total' => 0
];

if (count($historique) > 1) {
    // Trier l'historique par kilométrage croissant pour les calculs
    usort($historique, function ($a, $b) {
        return $a['kilometrage'] - $b['kilometrage'];
    });

    $distance_totale = end($historique)['kilometrage'] - $historique[0]['kilometrage'];
    $carburant_total = 0;
    $cout_total = 0;

    foreach ($historique as $appro) {
        $carburant_total += $appro['quantite_litres'];
        $cout_total += $appro['prix_total'];
    }

    $statistiques['distance_totale'] = $distance_totale;
    $statistiques['carburant_total'] = $carburant_total;
    $statistiques['cout_total'] = $cout_total;

    if ($distance_totale > 0) {
        // Consommation moyenne en L/100km
        $statistiques['consommation_moyenne'] = ($carburant_total / $distance_totale) * 100;
        // Coût moyen par km en FCFA/km
        $statistiques['cout_moyen_km'] = $cout_total / $distance_totale;
    }
}
?>