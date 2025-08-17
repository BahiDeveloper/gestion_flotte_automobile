<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_document = $_POST['type_document'];
    $id_utilisateur = $_POST['id_utilisateur'];
    $id_vehicule = $_POST['id_vehicule'];
    $date_debut = $_POST['date_debut'];
    $date_fin = ($_POST['frequence_renouvellement'] === 'permanent') ? null : $_POST['date_fin']; // Gestion de la date de fin
    $fournisseur = $_POST['fournisseur'];
    $prix = $_POST['prix'];
    $frequence_renouvellement = $_POST['frequence_renouvellement'];

    // Traitement des fichiers uploadés
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['file']['name'];
        $file_tmp_name = $_FILES['file']['tmp_name'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_new_name = uniqid('logo_') . '.' . $file_extension;
        $file_dir = '../uploads/documents/';

        if (!is_dir($file_dir)) {
            mkdir($file_dir, 0777, true);
        }

        // Déplacement du fichier uploadé
        if (move_uploaded_file($file_tmp_name, $file_dir . $file_new_name)) {
            // Insertion dans la base de données
            $sql = "INSERT INTO documents (type_document, date_debut, date_fin, fournisseur, prix, frequence_renouvellement, file_path, id_utilisateur, id_vehicule) 
                    VALUES (:type_document, :date_debut, :date_fin, :fournisseur, :prix, :frequence_renouvellement, :file_path, :id_utilisateur, :id_vehicule)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':type_document' => $type_document,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin,
                ':fournisseur' => $fournisseur,
                ':prix' => $prix,
                ':frequence_renouvellement' => $frequence_renouvellement,
                ':file_path' => $file_new_name,
                ':id_utilisateur' => $id_utilisateur,
                ':id_vehicule' => $id_vehicule
            ]);

            header('Location: ../gestion_documents.php?success_document_add=1');
            exit();
        } else {
            echo "Erreur lors de l'upload du fichier.";
        }
    } else {
        echo "Aucun fichier téléchargé ou une erreur est survenue.";
    }
}
?>