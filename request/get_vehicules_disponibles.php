<?php
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = $data['id'];

    // Requête pour récupérer les détails du véhicule
    $query = "SELECT * FROM vehicules WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicule) {
        // Si le véhicule n'est pas disponible, récupérer la date de disponibilité
        if ($vehicule['etat'] !== 'Disponible') {
            // Exemple : récupérer la date de fin de déplacement ou de maintenance
            $query = "SELECT date_arrivee FROM deplacements WHERE id_vehicule = :id_vehicule ORDER BY date_arrivee DESC LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['id_vehicule' => $id]);
            $deplacement = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($deplacement) {
                $vehicule['date_disponibilite'] = $deplacement['date_arrivee'];
            } else {
                // Si aucun déplacement n'est trouvé, utiliser une date par défaut
                $vehicule['date_disponibilite'] = date('Y-m-d H:i:s', strtotime('+1 day'));
            }
        }

        // Retourner les détails du véhicule en JSON
        header('Content-Type: application/json');
        echo json_encode($vehicule);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Véhicule non trouvé']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID du véhicule manquant']);
}
?>