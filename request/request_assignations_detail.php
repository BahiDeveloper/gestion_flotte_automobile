<?php
// Vérifier si l'ID de l'assignation est passé en paramètre
if (isset($_GET['id'])) {
    $id_assignation = $_GET['id'];

    // Récupérer les détails de l'assignation
    $sql = "SELECT * FROM deplacements WHERE id = :id_assignation";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_assignation' => $id_assignation]);
    $assignation = $stmt->fetch();

    // Vérifier si l'assignation existe
    if (!$assignation) {
        die("Assignation non trouvée.");
    }
} else {
    die("ID d'assignation non spécifié.");
}
?>