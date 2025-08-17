<?php
// Inclusion du fichier de configuration de la base de données
include_once("../database/config.php");

// Vérification de l'existence de l'ID du véhicule
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_vehicule = intval($_GET['id']);

    
    // Début d'une transaction pour assurer l'intégrité des données
    $pdo->beginTransaction();
    
    try {
        // 1. Vérifier si le véhicule existe
        $check_sql = "SELECT * FROM vehicules WHERE id_vehicule = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$id_vehicule]);

        if ($check_stmt->rowCount() === 0) {
            // Le véhicule n'existe pas
            header("Location: ../gestion_vehicules.php?error_vehicule_del=3");
            exit;
        }

        $vehicule = $check_stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Vérifier si le véhicule est en cours d'utilisation
        if ($vehicule['statut'] === 'en_course') {
            header("Location: ../gestion_vehicules.php?error_vehicule_del=4");
            exit;
        }

        // 3. Vérifier si des réservations futures existent pour ce véhicule
        $reservations_sql = "SELECT COUNT(*) FROM reservations_vehicules 
                             WHERE id_vehicule = ? 
                             AND statut IN ('en_attente', 'validee') 
                             AND date_depart > NOW()";
        $reservations_stmt = $pdo->prepare($reservations_sql);
        $reservations_stmt->execute([$id_vehicule]);
        $future_reservations = $reservations_stmt->fetchColumn();

        if ($future_reservations > 0) {
            header("Location: ../gestion_vehicules.php?error_vehicule_del=5");
            exit;
        }

        // 5. Supprimer le logo du véhicule s'il existe
        if (!empty($vehicule['logo_marque_vehicule'])) {
            $logo_path = "../uploads/vehicules/logo_marque/" . $vehicule['logo_marque_vehicule'];
            if (file_exists($logo_path)) {
                unlink($logo_path);
            }
        }

        // 6. Supprimer le véhicule
        $delete_sql = "DELETE FROM vehicules WHERE id_vehicule = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$id_vehicule]);

        // 7. Journaliser l'action
        $user_id = $_SESSION['user_id'] ?? 0; // Si l'utilisateur est connecté 
        var_dump($user_id);
        exit;
        $log_sql = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                   VALUES (?, 'suppression_vehicule', ?, ?)";
        $log_stmt = $pdo->prepare($log_sql);
        $description = "Suppression du véhicule " . $vehicule['marque'] . " " . $vehicule['modele'] . " (" . $vehicule['immatriculation'] . ")";
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt->execute([$user_id, $description, $ip]);

        var_dump($id_vehicule);
        exit;

        // Validation de la transaction
        $pdo->commit();

        // Redirection avec message de succès
        header("Location: ../gestion_vehicules.php?success_vehicule_del=1");
        exit;

    } catch (PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $pdo->rollBack();

        // Journaliser l'erreur
        error_log("Erreur lors de la suppression du véhicule ID $id_vehicule: " . $e->getMessage());

        // Redirection avec message d'erreur
        header("Location: ../gestion_vehicules.php?error_vehicule_del=1");
        exit;
    }

} else {
    // ID non spécifié ou invalide
    header("Location: ../gestion_vehicules.php?error_vehicule_del=2");
    exit;
}