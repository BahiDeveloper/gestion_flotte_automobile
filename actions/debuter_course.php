<?php
include_once("../database/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer l'ID de l'assignation depuis la requête AJAX
    $assignationId = json_decode(file_get_contents("php://input"), true)["assignation_id"];
    

    if (empty($assignationId)) {
        echo json_encode(["status" => "error", "message" => "ID d'assignation manquant."]);
        exit();
    }

    try {
        // Mettre à jour la date de départ et l'état dans la base de données
        $sql = "UPDATE deplacements 
                SET date_depart = NOW(), etat = 1 
                WHERE id = :assignation_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":assignation_id" => $assignationId]);

        // Vérifier si la mise à jour a réussi
        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Course démarrée avec succès."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Aucune assignation trouvée avec cet ID."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Méthode de requête non autorisée."]);
}
?>