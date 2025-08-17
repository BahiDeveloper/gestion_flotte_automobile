<?php
// actions/vehicules/get_maintenance_info.php
include_once("../../../database" . DIRECTORY_SEPARATOR . "config.php");

if (isset($_GET['maintenance_id']) && !empty($_GET['maintenance_id'])) {
    $maintenanceId = (int) $_GET['maintenance_id'];

    try {
        $stmt = $pdo->prepare("SELECT id_vehicule FROM maintenances WHERE id_maintenance = :id_maintenance");
        $stmt->execute(['id_maintenance' => $maintenanceId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode([
                'success' => true,
                'vehicule_id' => $result['id_vehicule']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Maintenance non trouvée'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de maintenance non fourni'
    ]);
}
exit;