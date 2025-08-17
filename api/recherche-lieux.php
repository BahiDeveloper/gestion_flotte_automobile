<?php
// api/recherche-lieux.php
header('Content-Type: application/json');

// Récupérer le terme de recherche
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$countryCode = 'ci'; // Code ISO pour la Côte d'Ivoire

if (empty($query) || strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

// Paramètres pour Nominatim
$url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
    'q' => $query,
    'countrycodes' => $countryCode, // Limiter à la Côte d'Ivoire
    'format' => 'json',
    'addressdetails' => 1,
    'limit' => 10,
    'accept-language' => 'fr' // Résultats en français
]);

// Configuration de la requête
$options = [
    'http' => [
        'header' => [
            'User-Agent: GestionFlotteApp/1.0', // Important: Nominatim exige un User-Agent
            'Accept: application/json'
        ]
    ]
];

$context = stream_context_create($options);

try {
    // Exécuter la requête
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new Exception('Erreur lors de la connexion à l\'API de géocodage');
    }
    
    $data = json_decode($response, true);
    
    // Formater les résultats
    $results = [];
    foreach ($data as $place) {
        // Construire un nom formaté en fonction du type de lieu
        $nomFormate = $place['display_name'];
        
        // Simplifier le nom pour l'affichage (garder seulement les informations pertinentes)
        // Format: Nom principal, ville/quartier, Côte d'Ivoire
        $parts = explode(', ', $nomFormate);
        $simplified = $parts[0];
        
        // Ajouter la ville/commune si disponible
        if (isset($place['address']['city'])) {
            $simplified .= ', ' . $place['address']['city'];
        } elseif (isset($place['address']['town'])) {
            $simplified .= ', ' . $place['address']['town'];
        } elseif (isset($place['address']['suburb'])) {
            $simplified .= ', ' . $place['address']['suburb'];
        }
        
        $results[] = [
            'nom' => $simplified,
            'type' => $place['type'],
            'latitude' => $place['lat'],
            'longitude' => $place['lon'],
            'display_name' => $place['display_name'] // Nom complet pour référence
        ];
    }
    
    echo json_encode($results);
    
} catch (Exception $e) {
    error_log('Erreur de recherche de lieux: ' . $e->getMessage());
    echo json_encode(['error' => 'Erreur lors de la recherche. Veuillez réessayer.']);
}
?>