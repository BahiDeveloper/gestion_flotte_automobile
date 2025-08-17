<?php
// Démarrer la session
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../auth/views/login.php');
    exit;
}

// Vérifier les permissions
require_once '../../includes/RoleAccess.php';
$roleAccess = new RoleAccess($_SESSION['role']);
if (!$roleAccess->hasPermission('deleteHistorique')) {
    $_SESSION['error'] = "Vous n'avez pas les permissions nécessaires pour effectuer cette action.";
    header('Location: ../../gestion_zones_vehicules.php');
    exit;
}

// Inclure le fichier de configuration de la base de données
require_once '../../database/config.php';

// Vérifier qu'un ID a été fourni
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['error'] = "ID de zone invalide.";
    header('Location: ../../gestion_zones_vehicules.php');
    exit;
}

try {
    // Vérifier si la zone existe
    $stmt = $pdo->prepare("SELECT nom_zone FROM zone_vehicules WHERE id = ?");
    $stmt->execute([$id]);
    $zone = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$zone) {
        $_SESSION['error'] = "La zone demandée n'existe pas.";
        header('Location: ../../gestion_zones_vehicules.php');
        exit;
    }

    // Vérifier si des véhicules sont associés à cette zone
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicules WHERE id_zone = ?");
    $stmt->execute([$id]);
    $vehicle_count = $stmt->fetchColumn();

    if ($vehicle_count > 0) {
        $_SESSION['error'] = "Impossible de supprimer cette zone car {$vehicle_count} véhicule(s) y sont associés. Veuillez d'abord déplacer ces véhicules vers une autre zone.";
        header('Location: ../../gestion_zones_vehicules.php');
        exit;
    }

    // Procéder à la suppression
    $pdo->beginTransaction();

    // Stocker le nom pour le journal
    $nom_zone = $zone['nom_zone'];

    // Supprimer la zone
    $stmt = $pdo->prepare("DELETE FROM zone_vehicules WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        // Journaliser l'action
        if (isset($_SESSION['id_utilisateur'])) {
            $id_utilisateur = $_SESSION['id_utilisateur'];
            $ip_address = $_SERVER['REMOTE_ADDR'];

            $log_stmt = $pdo->prepare("
                INSERT INTO journal_activites 
                (id_utilisateur, type_activite, description, date_activite, ip_address) 
                VALUES (?, ?, ?, NOW(), ?)
            ");

            $log_stmt->execute([
                $id_utilisateur,
                'suppression',
                "Suppression de la zone: {$nom_zone} (ID: {$id})",
                $ip_address
            ]);
        }

        $pdo->commit();
        $_SESSION['success'] = "La zone '{$nom_zone}' a été supprimée avec succès.";
    } else {
        $pdo->rollBack();
        $_SESSION['error'] = "Une erreur est survenue lors de la suppression de la zone.";
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
}

// Rediriger vers la page de gestion des zones
header('Location: ../../gestion_zones_vehicules.php');
exit;