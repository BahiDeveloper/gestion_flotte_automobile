<?php
// Afficher la liste des véhicules 
$query = "SELECT * FROM vehicules";
$stmt = $pdo->query($query);
$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// var_dump($vehicules);
// exit;

// Récupérer les maintenances programmées depuis la base de données
$sql = "SELECT m.*, 
                v.marque, 
                v.modele, 
                v.logo_marque_vehicule 
        FROM maintenance m 
        JOIN vehicules v ON m.id_vehicule = v.id 
        WHERE m.date_fin IS NOT NULL
        ORDER BY m.date_debut ASC";
$result = $pdo->query($sql);
$maintenances_historique = $result->fetchAll(PDO::FETCH_ASSOC);

// les véhicules disponibles 
$query = "SELECT 
    v.id, 
    v.marque, 
    v.modele, 
    v.immatriculation, 
    v.type_vehicule, 
    v.capacite, 
    v.etat, 
    v.kilometrage_actuel,
    CASE 
        WHEN v.etat = 'En maintenance' THEN MAX(m.date_fin_prevue)
        WHEN v.etat = 'En déplacement' THEN MAX(d.date_arrivee_prevue)
        WHEN v.etat = 'Demandé' THEN MAX(d.date_arrivee_prevue)
        ELSE NULL
    END AS date_disponibilite,
    GROUP_CONCAT(DISTINCT CONCAT(d.date_depart_prevue, '|', d.date_arrivee_prevue) SEPARATOR ';') AS dates_assignees
FROM 
    vehicules v
LEFT JOIN 
    deplacements d ON v.id = d.id_vehicule
LEFT JOIN 
    maintenance m ON v.id = m.id_vehicule
WHERE 
    (v.etat != 'En maintenance' OR (v.etat = 'En maintenance' AND m.date_fin IS NULL AND m.date_fin_prevue <= NOW()))
GROUP BY 
    v.id";

$stmt = $pdo->query($query);
$vehicules_all = $stmt->fetchAll(PDO::FETCH_ASSOC);


// var_dump($vehicules_all);
// exit;

// $query = "SELECT * FROM deplacements";
// $stmt = $pdo->query($query);
// $vehicule_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($vehicule_all);
// exit;

// les chauffeurs disponibles 
$query = "SELECT * FROM chauffeurs WHERE disponibilite = 'Disponible'";
$stmt = $pdo->query($query);
$chauffeurs_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Exemple de requête pour récupérer les véhicules en maintenance
$sql = "SELECT  m.id as maintenance_id, 
                m.id_vehicule, 
                m.date_debut, 
                m.date_fin_prevue, 
                m.description, 
                m.cout, 
                v.* 
        FROM maintenance m 
        JOIN vehicules v ON m.id_vehicule = v.id 
        WHERE v.etat = 'En maintenance' AND m.date_fin IS NULL
        ORDER BY m.date_debut ASC";
$result = $pdo->query($sql);
$vehicules_en_maintenance = $result->fetchAll(PDO::FETCH_ASSOC);

// var_dump($vehicules_en_maintenance);
// exit;
// Récupérer les maintenances programmées depuis la base de données
// $sql = "SELECT m.*, v.* 
//         FROM maintenance m 
//         JOIN vehicules v ON m.id_vehicule = v.id 
//         WHERE v.etat = 'En maintenance'
//         ORDER BY m.date_debut ASC";

// $result = $pdo->query($sql);
// $vehicules_en_maintenance_ = $result->fetchAll(PDO::FETCH_ASSOC);

// ----------------- calendrier dynamique ------------------- 

// Récupérer les déplacements
$queryDeplacements = "SELECT 
    v.marque, 
    v.modele, 
    d.date_depart_prevue, 
    d.date_arrivee_prevue,
    d.id_chauffeur,
    c.nom AS chauffeur_nom, 
    c.prenom AS chauffeur_prenom
FROM 
    deplacements d
JOIN 
    vehicules v ON d.id_vehicule = v.id
LEFT JOIN 
    chauffeurs c ON d.id_chauffeur = c.id
WHERE 
    d.date_arrivee IS NULL";

$stmtDeplacements = $pdo->query($queryDeplacements);
$deplacements = $stmtDeplacements->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les maintenances
$queryMaintenance = "SELECT 
    v.marque, 
    v.modele, 
    m.date_debut, 
    m.date_fin_prevue,
    m.date_fin,
    m.cout 
FROM 
    maintenance m
JOIN 
    vehicules v ON m.id_vehicule = v.id";
$stmtMaintenance = $pdo->query($queryMaintenance);
$maintenances = $stmtMaintenance->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les approvisionnements
$queryApprovisionnements = "SELECT 
    a.id AS appro_id,
    v.marque, 
    v.modele, 
    a.quantite_litres, 
    a.cout_total, 
    a.date_approvisionnement 
FROM 
    approvisionnements a
JOIN 
    vehicules v ON a.id_vehicule = v.id
ORDER BY 
    a.date_approvisionnement DESC";

$stmtApprovisionnements = $pdo->query($queryApprovisionnements);
$approvisionnements = $stmtApprovisionnements->fetchAll(PDO::FETCH_ASSOC);

$events = [];

// Ajouter les déplacements
foreach ($deplacements as $deplacement) {
    $title = $deplacement['marque'] . ' ' . $deplacement['modele'] . ' - ';
    $title .= ($deplacement['id_chauffeur'] === null) ? 'Demandé' : 'En déplacement';

    $events[] = [
        'title' => $title,
        'start' => $deplacement['date_depart_prevue'],
        'end' => $deplacement['date_arrivee_prevue'],
        'color' => ($deplacement['id_chauffeur'] === null) ? '#ffcc00' : '#ff9f89', // Couleur différente
        'type' => 'deplacement',
        'details' => [
            'Marque' => $deplacement['marque'],
            'Modele' => $deplacement['modele'],
            'Chauffeur' => $deplacement['chauffeur_nom'] . ' ' . $deplacement['chauffeur_prenom'],
            'Date depart' => $deplacement['date_depart_prevue'],
            'Date arrivee' => $deplacement['date_arrivee_prevue'],
        ]
    ];
}

// Ajouter les maintenances
foreach ($maintenances as $maintenance) {
    // Vérifier si le coût est null
    if ($maintenance['date_fin'] === null) {
        $title = $maintenance['marque'] . ' ' . $maintenance['modele'] . ' - Maintenance en cours';
        $color = '#ff6347'; // Couleur pour maintenance en cours
    } else {
        $title = $maintenance['marque'] . ' ' . $maintenance['modele'] . ' - Maintenance terminée';
        $color = '#90ee90'; // Couleur pour maintenance terminée
    }

    $events[] = [
        'title' => $title,
        'start' => $maintenance['date_debut'],
        'end' => $maintenance['date_fin_prevue'],
        'color' => $color, // Couleur basée sur l'état de la maintenance
        'type' => 'maintenance', // Type d'événement
        'details' => [
            'marque' => $maintenance['marque'],
            'modele' => $maintenance['modele'],
            // 'date_debut' => $maintenance['date_debut'], 
            'date_fin_prevue' => $maintenance['date_fin_prevue'],
            'cout' => $maintenance['cout'],
        ]
    ];
}

// Ajouter les approvisionnements
foreach ($approvisionnements as $approvisionnement) {
    $title = $approvisionnement['marque'] . ' ' . $approvisionnement['modele'] . ' - Approvisionnement';
    $title .= ' (' . $approvisionnement['quantite_litres'] . ' L)';

    $events[] = [
        'title' => $title,
        'start' => $approvisionnement['date_approvisionnement'],
        'color' => '#4682B4', // Couleur bleue pour les approvisionnements
        'type' => 'approvisionnement',
        'details' => [
            'marque' => $approvisionnement['marque'],
            'modele' => $approvisionnement['modele'],
            'quantite_litres' => $approvisionnement['quantite_litres'],
            'cout_total' => $approvisionnement['cout_total'],
            'date_approvisionnement' => $approvisionnement['date_approvisionnement'],
        ]
    ];
}



// Convertir les événements en JSON pour JavaScript
echo '<script>const events = ' . json_encode($events) . ';</script>';
?>