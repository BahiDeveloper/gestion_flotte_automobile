<?php
// Inclure la configuration et la connexion à la base de données
require_once '../../database/config.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'ID est fourni
$id_document = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_document) {
    $_SESSION['error'] = "ID du document non valide.";
    header('Location: ../../gestion_documents.php');
    exit;
}

try {
    // Récupérer les informations du document à renouveler
    $query = "SELECT * FROM documents_administratifs WHERE id_document = :id_document";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_document', $id_document, PDO::PARAM_INT);
    $stmt->execute();

    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        $_SESSION['error'] = "Document non trouvé.";
        header('Location: ../../gestion_documents.php');
        exit;
    }

    // Vérifier si l'utilisateur a le droit de renouveler le document
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'administrateur' && $_SESSION['role'] !== 'gestionnaire')) {
        $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour renouveler ce document.";
        header('Location: ../../gestion_documents.php');
        exit;
    }

    // Stocker les informations du document dans la session pour le formulaire de renouvellement
    $_SESSION['document_to_renew'] = [
        'id_document' => $document['id_document'],
        'id_vehicule' => $document['id_vehicule'],
        'id_chauffeur' => $document['id_chauffeur'],
        'id_utilisateur' => $document['id_utilisateur'],
        'type_document' => $document['type_document'],
        'numero_document' => $document['numero_document'],
        'fournisseur' => $document['fournisseur'],
        'frequence_renouvellement' => $document['frequence_renouvellement'],
        'fichier_url' => $document['fichier_url'],
        'note' => $document['note'],
        'prix' => $document['prix']
    ];

    // Déterminer le texte de la fréquence pour l'affichage
    $frequence_text = 'permanent';
    switch ($document['frequence_renouvellement']) {
        case 1:
            $frequence_text = 'mensuel';
            break;
        case 3:
            $frequence_text = 'trimestriel';
            break;
        case 6:
            $frequence_text = 'semestriel';
            break;
        case 12:
            $frequence_text = 'annuel';
            break;
    }
    $_SESSION['document_to_renew']['frequence_text'] = $frequence_text;

    // Ajouter dans le journal d'activités
    $action_description = "Consultation pour renouvellement du document #{$document['id_document']} - " .
        ucfirst(str_replace('_', ' ', $document['type_document']));

    $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                 VALUES (:id_utilisateur, 'view_renew_document', :description, :ip)";
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur'], PDO::PARAM_INT);
    $log_stmt->bindParam(':description', $action_description, PDO::PARAM_STR);
    $log_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
    $log_stmt->execute();

    // Rediriger vers la page de renouvellement avec un paramètre pour éviter les problèmes de cache
// Rediriger vers la page de renouvellement avec un paramètre pour éviter les problèmes de cache
header('Location: ../../renouveler_document.php?id=' . $id_document . '&timestamp=' . time());
exit;

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération du document: " . $e->getMessage();
    header('Location: ../../gestion_documents.php');
    exit;
}
?>