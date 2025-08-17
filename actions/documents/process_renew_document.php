<?php
// Inclure la configuration et la connexion à la base de données
require_once '../../database/config.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que la requête est bien de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// Pour le débogage - enregistrer les valeurs soumises
file_put_contents('../../logs/renew_debug.log', print_r($_POST, true) . "\n" . print_r($_FILES, true), FILE_APPEND);

// Vérifier les droits d'accès
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'administrateur' && $_SESSION['role'] !== 'gestionnaire')) {
    $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour effectuer cette action.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// Récupérer l'ID du document original
$original_document_id = filter_input(INPUT_POST, 'original_document_id', FILTER_VALIDATE_INT);
if (!$original_document_id) {
    $_SESSION['error'] = "ID du document original non valide.";
    header('Location: ../../gestion_documents.php');
    exit;
}

// Récupérer et valider les données du formulaire
$type_document = filter_input(INPUT_POST, 'type_document', FILTER_SANITIZE_SPECIAL_CHARS);
$id_utilisateur = filter_input(INPUT_POST, 'id_utilisateur', FILTER_VALIDATE_INT);
$id_vehicule = filter_input(INPUT_POST, 'id_vehicule', FILTER_VALIDATE_INT) ?: null;
$id_chauffeur = filter_input(INPUT_POST, 'id_chauffeur', FILTER_VALIDATE_INT) ?: null;
$frequence_renouvellement = filter_input(INPUT_POST, 'frequence_renouvellement', FILTER_SANITIZE_SPECIAL_CHARS);
$date_emission = filter_input(INPUT_POST, 'date_emission', FILTER_SANITIZE_SPECIAL_CHARS);
$date_expiration = filter_input(INPUT_POST, 'date_expiration', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$numero_document = filter_input(INPUT_POST, 'numero_document', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$fournisseur = filter_input(INPUT_POST, 'fournisseur', FILTER_SANITIZE_SPECIAL_CHARS);
$prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT);
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

// Validation des champs requis
if (!$type_document || !$id_utilisateur || !$date_emission || !$fournisseur || $prix === false) {
    $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis correctement.";
    header('Location: ../../renouveler_document.php?id=' . $original_document_id);
    exit;
}

// Si la fréquence n'est pas permanente, la date d'expiration est obligatoire
if ($frequence_renouvellement !== 'permanent' && !$date_expiration) {
    $_SESSION['error'] = "La date d'expiration est obligatoire pour les documents avec renouvellement.";
    header('Location: ../../renouveler_document.php?id=' . $original_document_id);
    exit;
}

// Gestion du fichier uploadé
if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Erreur lors de l'upload du fichier. Code: " . ($_FILES['fichier']['error'] ?? 'Inconnu');
    header('Location: ../../renouveler_document.php?id=' . $original_document_id);
    exit;
}

// Vérifier le type de fichier
$allowed_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
$file_type = $_FILES['fichier']['type'];
if (!in_array($file_type, $allowed_types)) {
    $_SESSION['error'] = "Type de fichier non autorisé. Formats acceptés: PDF, JPG, PNG.";
    header('Location: ../../renouveler_document.php?id=' . $original_document_id);
    exit;
}

// Vérifier la taille du fichier (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB en octets
if ($_FILES['fichier']['size'] > $max_size) {
    $_SESSION['error'] = "Le fichier est trop volumineux. Taille maximale: 5MB.";
    header('Location: ../../renouveler_document.php?id=' . $original_document_id);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Marquer l'ancien document comme expiré
    $update_old = "UPDATE documents_administratifs 
                   SET statut = 'expire', updated_at = NOW() 
                   WHERE id_document = :id_document";
    $stmt_old = $pdo->prepare($update_old);
    $stmt_old->bindParam(':id_document', $original_document_id, PDO::PARAM_INT);
    $stmt_old->execute();

    // 2. Créer le dossier de destination s'il n'existe pas
    $upload_dir = '../../uploads/documents/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // 3. Générer un nom de fichier unique
    $file_extension = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
    $unique_filename = uniqid('doc_') . '_' . date('Ymd') . '.' . $file_extension;
    $file_path = $upload_dir . $unique_filename;

    // 4. Déplacer le fichier téléchargé
    if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $file_path)) {
        throw new Exception("Erreur lors de l'enregistrement du fichier.");
    }

    // 5. Déterminer le statut du document
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

    // 6. Insérer le nouveau document
    $query = "INSERT INTO documents_administratifs (
        id_vehicule, id_chauffeur, id_utilisateur, 
        type_document, numero_document, date_emission, date_expiration,
        fournisseur, prix, frequence_renouvellement, fichier_url,
        statut, note
    ) VALUES (
        :id_vehicule, :id_chauffeur, :id_utilisateur,
        :type_document, :numero_document, :date_emission, :date_expiration,
        :fournisseur, :prix, :frequence_renouvellement, :fichier_url,
        :statut, :note
    )";

    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':id_vehicule', $id_vehicule, PDO::PARAM_INT);
    $stmt->bindParam(':id_chauffeur', $id_chauffeur, PDO::PARAM_INT);
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

    $new_document_id = $pdo->lastInsertId();

    // 7. Créer les alertes si nécessaire
    if ($frequence_renouvellement !== 'permanent' && $date_expiration) {
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
                    $alert_stmt->bindParam(':id_document', $new_document_id, PDO::PARAM_INT);
                    $alert_stmt->bindParam(':type_alerte', $alert_type, PDO::PARAM_STR);
                    $alert_stmt->bindParam(':date_alerte', $alert_date->format('Y-m-d'), PDO::PARAM_STR);
                    $alert_stmt->execute();
                }
            }
        }
    }

    // 8. Journaliser l'action
    if (isset($_SESSION['id_utilisateur'])) {
        $vehicle_info = '';
        if ($id_vehicule) {
            $veh_query = "SELECT marque, modele, immatriculation FROM vehicules WHERE id_vehicule = :id";
            $veh_stmt = $pdo->prepare($veh_query);
            $veh_stmt->bindParam(':id', $id_vehicule, PDO::PARAM_INT);
            $veh_stmt->execute();
            $vehicule = $veh_stmt->fetch(PDO::FETCH_ASSOC);

            if ($vehicule) {
                $vehicle_info = " pour le véhicule {$vehicule['marque']} {$vehicule['modele']} ({$vehicule['immatriculation']})";
            }
        }

        $chauffeur_info = '';
        if ($id_chauffeur) {
            $chauf_query = "SELECT nom, prenoms FROM chauffeurs WHERE id_chauffeur = :id";
            $chauf_stmt = $pdo->prepare($chauf_query);
            $chauf_stmt->bindParam(':id', $id_chauffeur, PDO::PARAM_INT);
            $chauf_stmt->execute();
            $chauffeur = $chauf_stmt->fetch(PDO::FETCH_ASSOC);

            if ($chauffeur) {
                $chauffeur_info = " pour le chauffeur {$chauffeur['nom']} {$chauffeur['prenoms']}";
            }
        }

        $action_description = "Renouvellement du document #{$original_document_id} - " .
            ucfirst(str_replace('_', ' ', $type_document)) .
            $vehicle_info . $chauffeur_info;

        $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                     VALUES (:id_utilisateur, 'renew_document', :description, :ip)";
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur'], PDO::PARAM_INT);
        $log_stmt->bindParam(':description', $action_description, PDO::PARAM_STR);
        $log_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $log_stmt->execute();
    }

    $pdo->commit();

    // Nettoyer la session
    unset($_SESSION['document_to_renew']);

    $_SESSION['success'] = "Le document a été renouvelé avec succès.";
    header('Location: ../../gestion_documents.php');

} catch (Exception $e) {
    $pdo->rollBack();

    // En cas d'erreur, supprimer le fichier uploadé si créé
    if (isset($file_path) && file_exists($file_path)) {
        unlink($file_path);
    }

    // Journaliser l'erreur
    error_log('Erreur de renouvellement de document: ' . $e->getMessage());

    $_SESSION['error'] = "Erreur lors du renouvellement du document: " . $e->getMessage();
    header('Location: ../../renouveler_document.php?id=' . $original_document_id);
}
exit;
?>