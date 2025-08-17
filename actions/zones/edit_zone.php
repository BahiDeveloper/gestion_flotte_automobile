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
if (!$roleAccess->hasPermission('modifyRequest')) {
    $_SESSION['error'] = "Vous n'avez pas les permissions nécessaires pour effectuer cette action.";
    header('Location: ../../gestion_zones_vehicules.php');
    exit;
}

// Inclure le fichier de configuration de la base de données
require_once '../../database/config.php';

// Traiter le formulaire uniquement lors d'une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les entrées
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nom_zone = trim($_POST['nom_zone'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation de base
    $errors = [];

    if (!$id) {
        $errors[] = "ID de zone invalide.";
    }

    if (empty($nom_zone)) {
        $errors[] = "Le nom de la zone est requis.";
    }

    // Si aucune erreur, procéder à la modification
    if (empty($errors)) {
        try {
            // Vérifier si la zone existe
            $stmt = $pdo->prepare("SELECT nom_zone FROM zone_vehicules WHERE id = ?");
            $stmt->execute([$id]);
            $existing_zone = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing_zone) {
                $_SESSION['error'] = "La zone demandée n'existe pas.";
                header('Location: ../../gestion_zones_vehicules.php');
                exit;
            }

            // Vérifier si le nouveau nom existe déjà pour une autre zone
            if ($existing_zone['nom_zone'] !== $nom_zone) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM zone_vehicules WHERE nom_zone = ? AND id != ?");
                $stmt->execute([$nom_zone, $id]);
                $name_exists = $stmt->fetchColumn();

                if ($name_exists) {
                    $_SESSION['error'] = "Une autre zone avec ce nom existe déjà.";
                    header('Location: ../../gestion_zones_vehicules.php');
                    exit;
                }
            }

            // Préparer et exécuter la requête de mise à jour
            $stmt = $pdo->prepare("
                UPDATE zone_vehicules 
                SET nom_zone = ?, description = ?, updated_at = NOW() 
                WHERE id = ?
            ");

            $result = $stmt->execute([$nom_zone, $description, $id]);

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
                        'modification',
                        "Modification de la zone ID: {$id} - Nouveau nom: {$nom_zone}",
                        $ip_address
                    ]);
                }

                $_SESSION['success'] = "La zone a été modifiée avec succès.";
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la modification de la zone.";
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
        }
    } else {
        // Stocker les erreurs en session
        $_SESSION['error'] = implode("<br>", $errors);
    }

    // Rediriger vers la page de gestion des zones
    header('Location: ../../gestion_zones_vehicules.php');
    exit;
} else {
    // Si accès direct à ce script sans POST, rediriger
    header('Location: ../../gestion_zones_vehicules.php');
    exit;
}