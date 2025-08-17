<?php
// Inclure la configuration et la connexion à la base de données
require_once '../../database/config.php';

// Vérifier que l'ID est fourni
$id_document = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_document) {
    header('HTTP/1.0 400 Bad Request');
    echo "ID du document non valide.";
    exit;
}

try {
    // Récupérer les informations du fichier
    $query = "SELECT d.fichier_url, d.type_document, v.marque, v.modele, v.immatriculation 
              FROM documents_administratifs d
              LEFT JOIN vehicules v ON d.id_vehicule = v.id_vehicule
              WHERE d.id_document = :id_document";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_document', $id_document, PDO::PARAM_INT);
    $stmt->execute();

    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document || !$document['fichier_url']) {
        header('HTTP/1.0 404 Not Found');
        echo "Document non trouvé.";
        exit;
    }

    $file_path = '../../uploads/documents/' . $document['fichier_url'];

    if (!file_exists($file_path)) {
        header('HTTP/1.0 404 Not Found');
        echo "Fichier non trouvé sur le serveur.";
        exit;
    }

    // Déterminer le type MIME
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    switch ($file_extension) {
        case 'pdf':
            $mime_type = 'application/pdf';
            break;
        case 'jpg':
        case 'jpeg':
            $mime_type = 'image/jpeg';
            break;
        case 'png':
            $mime_type = 'image/png';
            break;
        default:
            $mime_type = 'application/octet-stream';
    }

    // Générer un nom de fichier significatif pour le téléchargement
    $document_type = str_replace('_', '-', $document['type_document']);
    $vehicle_info = !empty($document['immatriculation']) ? "-{$document['marque']}-{$document['modele']}-{$document['immatriculation']}" : "";
    $download_filename = "{$document_type}{$vehicle_info}." . $file_extension;

    // Journaliser l'action
    if (isset($_SESSION['id_utilisateur'])) {
        $action_description = "Téléchargement du document #{$id_document} - " .
            ucfirst(str_replace('_', ' ', $document['type_document']));

        $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                     VALUES (:id_utilisateur, 'download_document', :description, :ip)";
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur'], PDO::PARAM_INT);
        $log_stmt->bindParam(':description', $action_description, PDO::PARAM_STR);
        $log_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $log_stmt->execute();
    }

    // Envoyer les en-têtes pour le téléchargement
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $download_filename . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    header('Pragma: public');

    // Lire et envoyer le fichier
    readfile($file_path);

} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo "Erreur lors de la récupération du document: " . $e->getMessage();
}
?>