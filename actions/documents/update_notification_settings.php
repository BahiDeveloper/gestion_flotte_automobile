<?php
// Inclure la configuration et la connexion à la base de données
require_once '../../database/config.php';

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour modifier les paramètres.";
    header('Location: ../../gestion_documents.php#parametres');
    exit;
}

// Vérifier que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../../gestion_documents.php#parametres');
    exit;
}

// Récupérer les valeurs soumises
$delai_alerte_1 = filter_input(INPUT_POST, 'delai_alerte_document_1', FILTER_VALIDATE_INT);
$delai_alerte_2 = filter_input(INPUT_POST, 'delai_alerte_document_2', FILTER_VALIDATE_INT);
$delai_alerte_3 = filter_input(INPUT_POST, 'delai_alerte_document_3', FILTER_VALIDATE_INT);

// Valider les valeurs
if (
    $delai_alerte_1 === false || $delai_alerte_1 < 1 || $delai_alerte_1 > 365 ||
    $delai_alerte_2 === false || $delai_alerte_2 < 1 || $delai_alerte_2 > 60 ||
    $delai_alerte_3 === false || $delai_alerte_3 < 1 || $delai_alerte_3 > 30
) {
    $_SESSION['error'] = "Valeurs de délai d'alerte non valides.";
    header('Location: ../../gestion_documents.php#parametres');
    exit;
}

// Vérifier que les délais sont cohérents (du plus long au plus court)
if ($delai_alerte_1 <= $delai_alerte_2 || $delai_alerte_2 <= $delai_alerte_3) {
    $_SESSION['error'] = "Les délais d'alerte doivent être décroissants (premier > deuxième > troisième).";
    header('Location: ../../gestion_documents.php#parametres');
    exit;
}

try {
    // Préparer et exécuter la mise à jour des paramètres
    $pdo->beginTransaction();

    // Fonction pour mettre à jour un paramètre
    function updateParameter($pdo, $key, $value)
    {
        $query = "UPDATE parametres_systeme SET valeur = :valeur, date_modification = NOW() WHERE cle = :cle";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':valeur', $value, PDO::PARAM_STR);
        $stmt->bindParam(':cle', $key, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Mettre à jour les trois paramètres
    $updates = [
        'delai_alerte_document_1' => $delai_alerte_1,
        'delai_alerte_document_2' => $delai_alerte_2,
        'delai_alerte_document_3' => $delai_alerte_3
    ];

    foreach ($updates as $key => $value) {
        updateParameter($pdo, $key, $value);
    }

    // Journaliser l'action
    if (isset($_SESSION['id_utilisateur'])) {
        $action_description = "Mise à jour des paramètres de notification: " .
            "Première alerte: $delai_alerte_1 jours, " .
            "Deuxième alerte: $delai_alerte_2 jours, " .
            "Alerte urgente: $delai_alerte_3 jours";

        $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                     VALUES (:id_utilisateur, 'update_parameters', :description, :ip)";
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur'], PDO::PARAM_INT);
        $log_stmt->bindParam(':description', $action_description, PDO::PARAM_STR);
        $log_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $log_stmt->execute();
    }

    // Recalculer les alertes pour tous les documents non expirés
    $get_docs = "SELECT id_document, date_expiration FROM documents_administratifs 
                 WHERE date_expiration > CURDATE() 
                 AND statut != 'expire'";
    $docs_stmt = $pdo->query($get_docs);
    $documents = $docs_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($documents as $doc) {
        $exp_date = new DateTime($doc['date_expiration']);

        // Supprimer les anciennes alertes
        $delete_alerts = "DELETE FROM alertes_documents 
                         WHERE id_document = :id_document 
                         AND date_alerte > CURDATE() 
                         AND statut = 'active'";
        $del_stmt = $pdo->prepare($delete_alerts);
        $del_stmt->bindParam(':id_document', $doc['id_document'], PDO::PARAM_INT);
        $del_stmt->execute();

        // Créer les nouvelles alertes
        $alert_types = [
            ['days' => $delai_alerte_1, 'type' => '2_mois'],
            ['days' => $delai_alerte_2, 'type' => '1_mois'],
            ['days' => $delai_alerte_3, 'type' => '1_semaine']
        ];

        foreach ($alert_types as $alert) {
            $alert_date = clone $exp_date;
            $alert_date->modify("-{$alert['days']} days");

            if ($alert_date > new DateTime()) {
                $insert_alert = "INSERT INTO alertes_documents (id_document, type_alerte, date_alerte) 
                               VALUES (:id_document, :type_alerte, :date_alerte)";
                $ins_stmt = $pdo->prepare($insert_alert);
                $ins_stmt->bindParam(':id_document', $doc['id_document'], PDO::PARAM_INT);
                $ins_stmt->bindParam(':type_alerte', $alert['type'], PDO::PARAM_STR);
                $ins_stmt->bindParam(':date_alerte', $alert_date->format('Y-m-d'), PDO::PARAM_STR);
                $ins_stmt->execute();
            }
        }
    }

    $pdo->commit();
    $_SESSION['success'] = "Les paramètres de notification ont été mis à jour avec succès.";

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erreur lors de la mise à jour des paramètres: " . $e->getMessage();
}

// Rediriger vers l'onglet des paramètres
header('Location: ../../gestion_documents.php#parametres');
exit;
?>