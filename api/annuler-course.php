<?php
/**
 * API pour annuler une course
 * Gère l'annulation d'une réservation validée ou en cours
 */

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../database/config.php";
session_start();

// Vérifier si l'utilisateur est connecté et a les droits
if (
    !isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['administrateur', 'gestionnaire'])
) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    // Validation et récupération des données
    $id_reservation = isset($_POST['id_reservation']) ? intval($_POST['id_reservation']) : 0;
    $motif_annulation = isset($_POST['motif_annulation']) ? trim($_POST['motif_annulation']) : '';
    $details_annulation = isset($_POST['details_annulation']) ? trim($_POST['details_annulation']) : '';
    $notifier_annulation = isset($_POST['notifier_annulation']) && $_POST['notifier_annulation'] == 'on';

    // Validation détaillée des données
    $errors = [];

    if ($id_reservation <= 0) {
        $errors[] = 'Identifiant de réservation invalide';
    }

    if (empty($motif_annulation)) {
        $errors[] = 'Le motif d\'annulation est requis';
    }

    if (empty($details_annulation)) {
        $errors[] = 'Les détails de l\'annulation sont requis';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
        exit;
    }

    // Liste des motifs valides
    $motifs_valides = ['demandeur', 'vehicule', 'chauffeur', 'meteo', 'autre'];
    if (!in_array($motif_annulation, $motifs_valides)) {
        throw new Exception('Motif d\'annulation invalide');
    }

    // Commencer la transaction
    $pdo->beginTransaction();

    // 1. Récupérer les informations de la réservation
    $query = "SELECT 
                r.id_reservation, r.id_vehicule, r.id_chauffeur, r.statut, 
                r.date_depart, r.date_retour_prevue, r.km_depart, r.note,
                v.immatriculation as vehicule_immatriculation,
                c.nom as chauffeur_nom, c.prenoms as chauffeur_prenoms,
                u.email as demandeur_email, u.nom as demandeur_nom
            FROM 
                reservations_vehicules r
            LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
            LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
            LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
            WHERE 
                r.id_reservation = :id_reservation";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':id_reservation' => $id_reservation]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        throw new Exception('Réservation non trouvée');
    }

    // Vérifier le statut de la réservation
    if (!in_array($reservation['statut'], ['validee', 'en_cours'])) {
        throw new Exception('Cette réservation ne peut pas être annulée (statut actuel: ' . $reservation['statut'] . ')');
    }

    // 2. Préparer le commentaire d'annulation
    $commentaire_annulation = "Annulation le " . date('Y-m-d H:i:s') .
        "\nMotif: " . $motif_annulation .
        "\nDétails: " . $details_annulation;

    $note_finale = !empty($reservation['note'])
        ? $reservation['note'] . "\n\n" . $commentaire_annulation
        : $commentaire_annulation;

    // 3. Mettre à jour la réservation
    $stmt = $pdo->prepare("UPDATE reservations_vehicules 
                           SET statut = 'annulee', 
                               note = :note 
                           WHERE id_reservation = :id");
    $stmt->execute([
        ':note' => $note_finale,
        ':id' => $id_reservation
    ]);

    // Variables pour suivre la libération des ressources
    $vehicule_libere = false;
    $chauffeur_libere = false;

    // 4. Vérifier et mettre à jour le statut du véhicule
    if (!empty($reservation['id_vehicule'])) {
        // Vérifier s'il y a d'autres réservations actives pour ce véhicule
        $queryCheckVehicule = "SELECT COUNT(*) 
            FROM reservations_vehicules 
            WHERE id_vehicule = :id_vehicule 
            AND id_reservation != :id_reservation
            AND statut IN ('validee', 'en_cours')
            AND (
                (date_depart <= :date_retour AND date_retour_prevue >= :date_depart)
            )";

        $stmt = $pdo->prepare($queryCheckVehicule);
        $stmt->execute([
            ':id_vehicule' => $reservation['id_vehicule'],
            ':id_reservation' => $id_reservation,
            ':date_depart' => $reservation['date_depart'],
            ':date_retour' => $reservation['date_retour_prevue']
        ]);

        $autresReservationsVehicule = $stmt->fetchColumn();

        // Si pas d'autres réservations, marquer comme disponible
        if ($autresReservationsVehicule == 0) {
            $stmt = $pdo->prepare("UPDATE vehicules SET statut = 'disponible' WHERE id_vehicule = :id");
            $stmt->execute([':id' => $reservation['id_vehicule']]);
            $vehicule_libere = true;
        }
    }

    // 5. Vérifier et mettre à jour le statut du chauffeur
    if (!empty($reservation['id_chauffeur'])) {
        // Vérifier s'il y a d'autres réservations actives pour ce chauffeur
        $queryCheckChauffeur = "SELECT COUNT(*) 
            FROM reservations_vehicules 
            WHERE id_chauffeur = :id_chauffeur 
            AND id_reservation != :id_reservation
            AND statut IN ('validee', 'en_cours')
            AND (
                (date_depart <= :date_retour AND date_retour_prevue >= :date_depart)
            )";

        $stmt = $pdo->prepare($queryCheckChauffeur);
        $stmt->execute([
            ':id_chauffeur' => $reservation['id_chauffeur'],
            ':id_reservation' => $id_reservation,
            ':date_depart' => $reservation['date_depart'],
            ':date_retour' => $reservation['date_retour_prevue']
        ]);

        $autresReservationsChauffeur = $stmt->fetchColumn();

        // Si pas d'autres réservations, marquer comme disponible
        if ($autresReservationsChauffeur == 0) {
            $stmt = $pdo->prepare("UPDATE chauffeurs SET statut = 'disponible' WHERE id_chauffeur = :id");
            $stmt->execute([':id' => $reservation['id_chauffeur']]);
            $chauffeur_libere = true;
        }
    }

    // 6. Journal des activités - Complètement isolé dans un try/catch séparé
    // pour que les erreurs n'affectent pas l'annulation de la course
    try {
        $description = "Annulation de la réservation #$id_reservation - " .
            "Motif: $motif_annulation - " .
            "Détails: $details_annulation";

        if ($reservation['statut'] === 'en_cours' && !empty($reservation['km_depart'])) {
            $description .= " - Kilométrage au départ: {$reservation['km_depart']} km";
        }

        // 6.1 Vérifier d'abord si l'utilisateur actuel existe réellement dans la base de données
        $checkUserStmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE id_utilisateur = :user");
        $checkUserStmt->execute([':user' => $_SESSION['id_utilisateur']]);
        $userExists = $checkUserStmt->fetch();
        
        if ($userExists) {
            // L'utilisateur existe, procéder normalement
            $stmt = $pdo->prepare("INSERT INTO journal_activites (
                id_utilisateur, type_activite, description, ip_address
            ) VALUES (
                :user, 'annulation_course', :desc, :ip
            )");
            $stmt->execute([
                ':user' => $_SESSION['id_utilisateur'],
                ':desc' => $description,
                ':ip' => $_SERVER['REMOTE_ADDR']
            ]);
        } else {
            // 6.2 L'utilisateur de session n'existe pas, trouver un utilisateur valide dans la base
            // Rechercher un utilisateur administrateur dont l'ID existe réellement
            $findValidUserStmt = $pdo->prepare("
                SELECT u.id_utilisateur 
                FROM utilisateurs u
                WHERE u.role = 'administrateur'
                AND EXISTS (
                    SELECT 1 FROM journal_activites j WHERE j.id_utilisateur = u.id_utilisateur
                )
                LIMIT 1
            ");
            $findValidUserStmt->execute();
            $validUser = $findValidUserStmt->fetch();
            
            if ($validUser) {
                // Un utilisateur valide a été trouvé
                $stmt = $pdo->prepare("INSERT INTO journal_activites (
                    id_utilisateur, type_activite, description, ip_address
                ) VALUES (
                    :user, 'annulation_course', :desc, :ip
                )");
                $stmt->execute([
                    ':user' => $validUser['id_utilisateur'],
                    ':desc' => $description . " (action effectuée par un utilisateur de session non valide)",
                    ':ip' => $_SERVER['REMOTE_ADDR']
                ]);
            } else {
                // 6.3 Aucun utilisateur valide n'a été trouvé, prendre le premier utilisateur de la base
                $findAnyUserStmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs LIMIT 1");
                $findAnyUserStmt->execute();
                $anyUser = $findAnyUserStmt->fetch();
                
                if ($anyUser) {
                    $stmt = $pdo->prepare("INSERT INTO journal_activites (
                        id_utilisateur, type_activite, description, ip_address
                    ) VALUES (
                        :user, 'annulation_course', :desc, :ip
                    )");
                    $stmt->execute([
                        ':user' => $anyUser['id_utilisateur'],
                        ':desc' => $description . " (action système)",
                        ':ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                } else {
                    // 6.4 La base de données ne contient aucun utilisateur
                    error_log("Impossible d'enregistrer l'annulation dans le journal : aucun utilisateur valide trouvé dans la base de données");
                }
            }
        }
    } catch (Exception $e) {
        // Attrape TOUTE exception possible lors de la journalisation
        // Pas seulement PDOException, mais aussi tout autre type d'erreur
        error_log("Erreur lors de l'insertion dans le journal : " . $e->getMessage());
        // On continue l'exécution du script, la journalisation est secondaire
    }

    // 7. Notifications (optionnel)
    if ($notifier_annulation) {
        // TODO: Implémenter le système de notification
        // Peut inclure l'envoi d'email au demandeur, au chauffeur, etc.
        // Utiliser $reservation['demandeur_email'], $reservation['demandeur_nom'], etc.
    }

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Course annulée avec succès',
        'vehicule_libere' => $vehicule_libere,
        'chauffeur_libere' => $chauffeur_libere
    ]);

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erreur PDO dans annuler-course.php : " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur lors de l\'annulation de la course'
    ]);
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erreur dans annuler-course.php : " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}