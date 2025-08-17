<?php
// Démarrer la session pour pouvoir stocker les messages d'erreur
session_start();

// Inclure le fichier de configuration de la base de données
include_once("../../database" . DIRECTORY_SEPARATOR . "config.php");

// Initialiser les variables
$errors = [];
$id = null;

// Vérifier que l'ID du chauffeur est fourni
if (isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
} else {
    $errors[] = "ID de chauffeur invalide ou manquant";
    $_SESSION['form_errors'] = $errors;
    header("Location: ../../gestion_chauffeurs.php?error=delete");
    exit();
}

try {
    // Début de la transaction
    $pdo->beginTransaction();

    // 1. Vérifier si le chauffeur existe
    $query = "SELECT id_chauffeur, nom, prenoms, statut, photo_profil, photo_permis FROM chauffeurs WHERE id_chauffeur = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chauffeur) {
        throw new Exception("Le chauffeur demandé n'existe pas");
    }

    // 2. Vérifier si le chauffeur est actuellement utilisé dans une réservation active
    $query_reservations = "SELECT COUNT(*) FROM reservations_vehicules 
                          WHERE id_chauffeur = :id 
                          AND statut IN ('en_attente', 'validee', 'en_cours')";
    $stmt_reservations = $pdo->prepare($query_reservations);
    $stmt_reservations->execute(['id' => $id]);
    $active_reservations = $stmt_reservations->fetchColumn();

    if ($active_reservations > 0) {
        throw new Exception("Impossible de supprimer ce chauffeur car il est assigné à " . $active_reservations . " réservation(s) active(s)");
    }

    // 3. Supprimer les documents administratifs associés
    $query_documents = "DELETE FROM documents_administratifs WHERE id_chauffeur = :id";
    $stmt_documents = $pdo->prepare($query_documents);
    $stmt_documents->execute(['id' => $id]);

    // 4. Nettoyer les références dans les approvisionnements
    // Option 1: Suppression des approvisionnements
    // $query_approvisionnements = "DELETE FROM approvisionnements_carburant WHERE id_chauffeur = :id";
    // Option 2: Mettre à NULL la référence au chauffeur (préférable pour conserver l'historique)
    $query_approvisionnements = "UPDATE approvisionnements_carburant SET id_chauffeur = NULL WHERE id_chauffeur = :id";
    $stmt_approvisionnements = $pdo->prepare($query_approvisionnements);
    $stmt_approvisionnements->execute(['id' => $id]);

    // 5. Effectuer la suppression du chauffeur
    $query_delete = "DELETE FROM chauffeurs WHERE id_chauffeur = :id";
    $stmt_delete = $pdo->prepare($query_delete);
    $result = $stmt_delete->execute(['id' => $id]);

    if (!$result) {
        throw new Exception("Erreur lors de la suppression du chauffeur");
    }

    // 6. Supprimer les fichiers associés au chauffeur
    $photo_profil = $chauffeur['photo_profil'];
    $photo_permis = $chauffeur['photo_permis'];

    if (!empty($photo_profil) && file_exists("../../uploads/chauffeurs/profils/" . $photo_profil)) {
        unlink("../../uploads/chauffeurs/profils/" . $photo_profil);
    }

    if (!empty($photo_permis) && file_exists("../../uploads/chauffeurs/permis/" . $photo_permis)) {
        unlink("../../uploads/chauffeurs/permis/" . $photo_permis);
    }

    // 7. Enregistrement de l'activité dans le journal
    // $user_id = 1; // À remplacer par l'ID de l'utilisateur connecté quand l'authentification sera implémentée
    // $nom_complet = $chauffeur['nom'] . ' ' . $chauffeur['prenoms'];
    // $description = "Suppression du chauffeur : $nom_complet (ID: $id)";

    // $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address)
    //               VALUES (:id_utilisateur, 'suppression_chauffeur', :description, :ip_address)";
    // $log_stmt = $pdo->prepare($log_query);
    // $log_stmt->execute([
    //     'id_utilisateur' => $user_id,
    //     'description' => $description,
    //     'ip_address' => $_SERVER['REMOTE_ADDR']
    // ]);

    // Valider la transaction
    $pdo->commit();

    // Rediriger avec un message de succès
    header("Location: ../../gestion_chauffeurs.php?success=delete");
    exit();

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    $errors[] = "Erreur de base de données : " . $e->getMessage();
    $_SESSION['form_errors'] = $errors;
    header("Location: ../../gestion_chauffeurs.php?error=delete");
    exit();
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    $errors[] = $e->getMessage();
    $_SESSION['form_errors'] = $errors;
    header("Location: ../../gestion_chauffeurs.php?error=delete");
    exit();
}