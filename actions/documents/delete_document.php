<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration et la connexion à la base de données
require_once '../../database/config.php';

// Vérifier que l'ID est fourni
$id_document = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_document) {
    $_SESSION['error'] = "ID du document non valide.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// var_dump($_SESSION);
// exit;   
// Vérifier que l'utilisateur est administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour supprimer un document.";
    header('Location: ../../gestion_documents.php');
    exit;
}

try {
    // Récupérer les informations du document avant suppression pour la journalisation
    $query_info = "SELECT d.id_document, d.type_document, d.fichier_url, 
                   v.marque, v.modele, v.immatriculation, 
                   c.nom as chauffeur_nom, c.prenoms as chauffeur_prenom
                   FROM documents_administratifs d
                   LEFT JOIN vehicules v ON d.id_vehicule = v.id_vehicule
                   LEFT JOIN chauffeurs c ON d.id_chauffeur = c.id_chauffeur
                   WHERE d.id_document = :id_document";
    $stmt_info = $pdo->prepare($query_info);
    $stmt_info->bindParam(':id_document', $id_document, PDO::PARAM_INT);
    $stmt_info->execute();
    $document_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    if (!$document_info) {
        $_SESSION['error'] = "Document non trouvé.";
        header('Location: ../../gestion_documents.php');
        exit;
    }

    // Supprimer les alertes associées au document
    $query_alerts = "DELETE FROM alertes_documents WHERE id_document = :id_document";
    $stmt_alerts = $pdo->prepare($query_alerts);
    $stmt_alerts->bindParam(':id_document', $id_document, PDO::PARAM_INT);
    $stmt_alerts->execute();

    // Supprimer le document de la base de données
    $query = "DELETE FROM documents_administratifs WHERE id_document = :id_document";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_document', $id_document, PDO::PARAM_INT);
    $stmt->execute();

    // Supprimer le fichier physique si existant
    if ($document_info['fichier_url']) {
        $file_path = '../../uploads/documents/' . $document_info['fichier_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Journaliser l'action
    if (isset($_SESSION['id_utilisateur'])) {
        $vehicle_info = '';
        if (!empty($document_info['immatriculation'])) {
            $vehicle_info = " pour le véhicule {$document_info['marque']} {$document_info['modele']} ({$document_info['immatriculation']})";
        }

        $chauffeur_info = '';
        if (!empty($document_info['chauffeur_nom'])) {
            $chauffeur_info = " pour le chauffeur {$document_info['chauffeur_nom']} {$document_info['chauffeur_prenom']}";
        }

        $action_description = "Suppression du document #{$document_info['id_document']} - " .
            ucfirst(str_replace('_', ' ', $document_info['type_document'])) .
            $vehicle_info . $chauffeur_info;

        $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                     VALUES (:id_utilisateur, 'delete_document', :description, :ip)";
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur'], PDO::PARAM_INT);
        $log_stmt->bindParam(':description', $action_description, PDO::PARAM_STR);
        $log_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $log_stmt->execute();
    }

    $_SESSION['success'] = "Le document a été supprimé avec succès.";

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression du document: " . $e->getMessage();
}

// Rediriger vers la page de gestion des documents
header('Location: ../../gestion_documents.php');
exit;
?>