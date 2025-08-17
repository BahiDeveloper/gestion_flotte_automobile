<?php
// Démarrer la session
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../auth/views/login.php');
    exit;
}

// Vérifier les permissions (optionnel, selon votre logique d'autorisation)
require_once '../../includes/RoleAccess.php';
$roleAccess = new RoleAccess($_SESSION['role']);
if (!$roleAccess->hasPermission('form')) {
    $_SESSION['error'] = "Vous n'avez pas les permissions nécessaires pour effectuer cette action.";
    header('Location: ../../gestion_zones_vehicules.php');
    exit;
}

// Inclure le fichier de configuration de la base de données
require_once '../../database/config.php';

// Traiter le formulaire uniquement lors d'une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les entrées
    $nom_zone = trim($_POST['nom_zone'] ?? '');
    $description = trim($_POST['description'] ?? null);

    // Validation de base
    $errors = [];

    if (empty($nom_zone)) {
        $errors[] = "Le nom de la zone est requis.";
    }

    // Si aucune erreur, procéder à l'ajout
    if (empty($errors)) {
        try {
            // Vérifier si la zone existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM zone_vehicules WHERE nom_zone = ?");
            $stmt->execute([$nom_zone]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $_SESSION['error'] = "Une zone avec ce nom existe déjà.";
                header('Location: ../../gestion_zones_vehicules.php');
                exit;
            }

            // Préparer et exécuter la requête d'insertion
            $stmt = $pdo->prepare("
                INSERT INTO zone_vehicules (nom_zone, description, created_at, updated_at) 
                VALUES (?, ?, NOW(), NOW())
            ");

            $result = $stmt->execute([$nom_zone, $description]);

            if ($result) {
                // Journaliser l'action
                if (isset($_SESSION['id_utilisateur'])) {
                    $id_utilisateur = $_SESSION['id_utilisateur'];
                    $zone_id = $pdo->lastInsertId();
                    $ip_address = $_SERVER['REMOTE_ADDR'];

                    $log_stmt = $pdo->prepare("
                        INSERT INTO journal_activites 
                        (id_utilisateur, type_activite, description, date_activite, ip_address) 
                        VALUES (?, ?, ?, NOW(), ?)
                    ");

                    $log_stmt->execute([
                        $id_utilisateur,
                        'création',
                        "Création d'une nouvelle zone: {$nom_zone} (ID: {$zone_id})",
                        $ip_address
                    ]);
                }

                $_SESSION['success'] = "La zone '{$nom_zone}' a été ajoutée avec succès.";
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de l'ajout de la zone.";
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