<?php
// Inclure la configuration et la connexion à la base de données
require_once '../../database/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que la requête est bien de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Rediriger vers la page de gestion des documents avec un message d'erreur
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../../gestion_documents.php');
    exit;
}


// Récupérer et valider les données du formulaire
$type_document = filter_input(INPUT_POST, 'type_document', FILTER_SANITIZE_SPECIAL_CHARS);
$id_utilisateur = filter_input(INPUT_POST, 'id_utilisateur', FILTER_VALIDATE_INT) ?: null; 
$id_vehicule = filter_input(INPUT_POST, 'id_vehicule', FILTER_VALIDATE_INT) ?: null;
// $id_chauffeur = filter_input(INPUT_POST, 'id_chauffeur', FILTER_VALIDATE_INT) ?: null; 
$frequence_renouvellement = filter_input(INPUT_POST, 'frequence_renouvellement', FILTER_SANITIZE_SPECIAL_CHARS);
$date_emission = filter_input(INPUT_POST, 'date_emission', FILTER_SANITIZE_SPECIAL_CHARS);
$date_expiration = filter_input(INPUT_POST, 'date_expiration', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$numero_document = filter_input(INPUT_POST, 'numero_document', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$fournisseur = filter_input(INPUT_POST, 'fournisseur', FILTER_SANITIZE_SPECIAL_CHARS);
$prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT) ?: null;
$note = filter_input(INPUT_POST, 'note', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;


// Déterminer la fréquence en mois pour stocker en base de données
$frequence_mois = 0; // Par défaut 0 = permanent
switch ($frequence_renouvellement) {
    case 'mensuel':
        $frequence_mois = 1;
        break;
    case 'trimestriel':
        $frequence_mois = 3;
        break;
    case 'semestriel':
        $frequence_mois = 6;
        break;
    case 'annuel':
        $frequence_mois = 12;
        break;
}


// Validation des champs requis || $prix === false
if (!$type_document || !$id_utilisateur || !$date_emission || !$fournisseur) {
    $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis correctement.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// var_dump($_POST);
// exit;

// Si la fréquence n'est pas permanente, la date d'expiration est obligatoire
if ($frequence_renouvellement !== 'permanent' && !$date_expiration) {
    $_SESSION['error'] = "La date d'expiration est obligatoire pour les documents avec renouvellement.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// Gestion du fichier uploadé
if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Erreur lors de l'upload du fichier. Code: " . ($_FILES['fichier']['error'] ?? 'Inconnu');
    header('Location: ../../gestion_documents.php');
    exit;
}

// Vérifier le type de fichier
$allowed_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
$file_type = $_FILES['fichier']['type'];
if (!in_array($file_type, $allowed_types)) {
    $_SESSION['error'] = "Type de fichier non autorisé. Formats acceptés: PDF, JPG, PNG.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// Vérifier la taille du fichier (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB en octets
if ($_FILES['fichier']['size'] > $max_size) {
    $_SESSION['error'] = "Le fichier est trop volumineux. Taille maximale: 5MB.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// Créer le dossier de destination s'il n'existe pas
$upload_dir = '../../uploads/documents/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Générer un nom de fichier unique
$file_extension = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
$unique_filename = uniqid('doc_') . '_' . date('Ymd') . '.' . $file_extension;
$file_path = $upload_dir . $unique_filename;

// Déplacer le fichier téléchargé
if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $file_path)) {
    $_SESSION['error'] = "Erreur lors de l'enregistrement du fichier.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// Déterminer le statut du document
$statut = 'valide';
if ($frequence_renouvellement !== 'permanent') {
    $date_expiration_obj = new DateTime($date_expiration);
    $today = new DateTime();
    $days_difference = $today->diff($date_expiration_obj)->days;

    if ($date_expiration_obj < $today) {
        $statut = 'expire';
    } elseif ($days_difference <= 30) {
        $statut = 'a_renouveler';
    }
}

try {
    // Préparer et exécuter la requête d'insertion
    $query = "INSERT INTO documents_administratifs (
        id_vehicule, id_utilisateur,
        type_document, numero_document, date_emission, date_expiration,
        fournisseur, prix, frequence_renouvellement, fichier_url,
        statut, note
    ) VALUES (
        :id_vehicule,
        :id_utilisateur,
        :type_document, 
        :numero_document, 
        :date_emission, 
        :date_expiration,
        :fournisseur, 
        :prix, 
        :frequence_renouvellement, 
        :fichier_url,
        :statut, :note
    )";

    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':id_vehicule', $id_vehicule, PDO::PARAM_INT);
    // $stmt->bindParam(':id_chauffeur', $id_chauffeur, PDO::PARAM_INT);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT); 
    $stmt->bindParam(':type_document', $type_document, PDO::PARAM_STR);
    $stmt->bindParam(':numero_document', $numero_document, PDO::PARAM_STR);
    $stmt->bindParam(':date_emission', $date_emission, PDO::PARAM_STR);
    $stmt->bindParam(':date_expiration', $date_expiration, PDO::PARAM_STR);
    $stmt->bindParam(':fournisseur', $fournisseur, PDO::PARAM_STR);
    $stmt->bindParam(':prix', $prix, PDO::PARAM_STR);
    $stmt->bindParam(':frequence_renouvellement', $frequence_mois, PDO::PARAM_INT);
    $stmt->bindParam(':fichier_url', $unique_filename, PDO::PARAM_STR);
    $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
    $stmt->bindParam(':note', $note, PDO::PARAM_STR);

    $stmt->execute();

    $document_id = $pdo->lastInsertId();


    // Journaliser l'action
    if (isset($_SESSION['id_utilisateur'])) {
        $action_description = "Ajout d'un document : $type_document";
        if ($id_vehicule)
            $action_description .= " pour le véhicule #$id_vehicule";
        if ($id_chauffeur)
            $action_description .= " pour le chauffeur #$id_chauffeur";

        $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                     VALUES (:id_utilisateur, 'ajout_document', :description, :ip)";
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur'], PDO::PARAM_INT);
        $log_stmt->bindParam(':description', $action_description, PDO::PARAM_STR);
        $log_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $log_stmt->execute();
    }

    // Créer les alertes si nécessaire
    if ($frequence_renouvellement !== 'permanent') {
        // Récupérer les paramètres d'alerte
        $params_query = "SELECT cle, valeur FROM parametres_systeme WHERE cle LIKE 'delai_alerte_document_%'";
        $params_stmt = $pdo->query($params_query);
        $alert_params = $params_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $date_expiration_obj = new DateTime($date_expiration);

        // Création des alertes selon les délais configurés
        $alert_types = [
            'delai_alerte_document_1' => '2_mois',
            'delai_alerte_document_2' => '1_mois',
            'delai_alerte_document_3' => '1_semaine'
        ];

        foreach ($alert_types as $param_key => $alert_type) {
            if (isset($alert_params[$param_key])) {
                $days_before = intval($alert_params[$param_key]);
                $alert_date = clone $date_expiration_obj;
                $alert_date->modify("-$days_before days");

                // N'ajouter l'alerte que si sa date est dans le futur
                if ($alert_date > new DateTime()) {
                    $alert_query = "INSERT INTO alertes_documents (id_document, type_alerte, date_alerte)
                                   VALUES (:id_document, :type_alerte, :date_alerte)";
                    $alert_stmt = $pdo->prepare($alert_query);
                    $alert_stmt->bindParam(':id_document', $document_id, PDO::PARAM_INT);
                    $alert_stmt->bindParam(':type_alerte', $alert_type, PDO::PARAM_STR);
                    $alert_stmt->bindParam(':date_alerte', $alert_date->format('Y-m-d'), PDO::PARAM_STR);
                    $alert_stmt->execute();
                }
            }
        }
    }

    $_SESSION['success'] = "Le document a été ajouté avec succès.";

} catch (PDOException $e) {
    // En cas d'erreur, supprimer le fichier uploadé
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    $_SESSION['error'] = "Erreur lors de l'ajout du document: " . $e->getMessage();
}

// Rediriger vers la page de gestion des documents
header('Location: ../../gestion_documents.php');
exit;