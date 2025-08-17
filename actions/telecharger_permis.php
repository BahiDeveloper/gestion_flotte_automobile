<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID du chauffeur est passé en paramètre
if (isset($_GET['id'])) {

    $chauffeur_id = intval($_GET['id']);

    // Récupérer les informations du chauffeur
    $sql = "SELECT photo_permis FROM chauffeurs WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $chauffeur_id]);
    $chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chauffeur && !empty($chauffeur['photo_permis'])) {
        $photo_permis_path = '../uploads/chauffeurs/permis_photo/' . $chauffeur['photo_permis'];


        // Vérifier si le fichier existe
        if (file_exists($photo_permis_path)) {

            // Définir les en-têtes pour forcer le téléchargement
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($photo_permis_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($photo_permis_path));

            // Lire le fichier
            readfile($photo_permis_path);
            exit;  // Terminer le script après avoir téléchargé le fichier
        } else {
            // Rediriger vers une page d'erreur avec SweetAlert
            header("Location: ../chauffeur_details.php?error_permis_download=1");
            exit;
        }
    } else {
        // Rediriger vers une page d'erreur si le permis est absent
        header("Location: ../chauffeur_details.php?error_permis_download=2");
        exit;
    }
} else {
    // Rediriger vers une page d'erreur si l'ID du chauffeur n'est pas spécifié
    header("Location: ../chauffeur_details.php?error_permis_download=3");
    exit;
}
?>