<?php
// api/calculer-itineraire.php
header('Content-Type: application/json');

// Récupérer les paramètres
$depart = isset($_GET['depart']) ? $_GET['depart'] : '';
$arrivee = isset($_GET['arrivee']) ? $_GET['arrivee'] : '';

if (empty($depart) || empty($arrivee)) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

// Première étape: géocoder les adresses pour obtenir les coordonnées
function geocodeAddress($address) {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q' => $address . ', Côte d\'Ivoire', // Ajouter le pays pour plus de précision
        'format' => 'json',
        'limit' => 1,
        'accept-language' => 'fr'
    ]);
    
    $options = [
        'http' => [
            'header' => [
                'User-Agent: GestionFlotteApp/1.0',
                'Accept: application/json'
            ]
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (empty($data)) {
        return null;
    }
    
    return [
        'lat' => $data[0]['lat'],
        'lon' => $data[0]['lon']
    ];
}

// Géocoder les adresses
$departCoords = geocodeAddress($depart);
$arriveeCoords = geocodeAddress($arrivee);

if (!$departCoords || !$arriveeCoords) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible de géolocaliser une ou plusieurs adresses'
    ]);
    exit;
}

// Deuxième étape: calculer l'itinéraire avec OSRM
$osrmUrl = 'https://router.project-osrm.org/route/v1/driving/' . 
          $departCoords['lon'] . ',' . $departCoords['lat'] . ';' . 
          $arriveeCoords['lon'] . ',' . $arriveeCoords['lat'] . 
          '?overview=false';

$options = [
    'http' => [
        'header' => [
            'User-Agent: GestionFlotteApp/1.0',
            'Accept: application/json'
        ]
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($osrmUrl, false, $context);

if ($response === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du calcul de l\'itinéraire'
    ]);
    exit;
}

$routeData = json_decode($response, true);

if ($routeData['code'] !== 'Ok') {
    echo json_encode([
        'success' => false,
        'message' => 'Aucun itinéraire trouvé entre ces deux points'
    ]);
    exit;
}

// Extraire les informations de l'itinéraire
$distance = round($routeData['routes'][0]['distance'] / 1000, 1); // Conversion m -> km
$dureeSecondes = $routeData['routes'][0]['duration'];

// Convertir la durée en heures ou minutes selon sa valeur
if ($dureeSecondes < 3600) {
    // Moins d'une heure : convertir en minutes
    $duree = round($dureeSecondes / 60, 0);
    $uniteDuree = 'minutes';
} else {
    // Une heure ou plus : convertir en heures
    $duree = round($dureeSecondes / 3600, 2);
    $uniteDuree = 'heures';
}

echo json_encode([
    'success' => true,
    'distance' => $distance,
    'duree' => $duree,
    'unite_distance' => 'km',
    'unite_duree' => $uniteDuree
]);
?>