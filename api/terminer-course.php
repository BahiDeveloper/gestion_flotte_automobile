<?php
/**
 * API pour terminer une course et enregistrer le kilométrage de retour
 */

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Inclusion du fichier de configuration
require_once "../database/config.php";
session_start();

// Vérifier si l'utilisateur est connecté et a les droits
if (
    !isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['administrateur', 'gestionnaire', 'validateur'])
) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

// Vérifier si la méthode est POST
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
    $kilometrage_retour = isset($_POST['kilometrage_retour']) ? intval($_POST['kilometrage_retour']) : 0;
    $commentaires = isset($_POST['commentaires']) ? trim($_POST['commentaires']) : '';
    $materiel_retour = isset($_POST['materiel_retour']) ? trim($_POST['materiel_retour']) : '';
    $notifier_fin = isset($_POST['notifier_fin']) && $_POST['notifier_fin'] == 'on';

    // Validation détaillée des données
    $errors = [];

    if ($id_reservation <= 0) {
        $errors[] = 'Identifiant de réservation invalide';
    }

    if ($kilometrage_retour <= 0) {
        $errors[] = 'Kilométrage de retour invalide';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
        exit;
    }

    // Débuter une transaction
    $pdo->beginTransaction();

    // 1. Récupérer les informations actuelles de la réservation et du véhicule
    $query = "SELECT 
                r.id_reservation, r.id_vehicule, r.id_chauffeur, r.statut, r.km_depart, r.note, r.materiel,
                v.kilometrage_actuel, 
                c.nom as chauffeur_nom, 
                c.prenoms as chauffeur_prenoms
            FROM 
                reservations_vehicules r
            JOIN vehicules v ON r.id_vehicule = v.id_vehicule
            LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
            WHERE 
                r.id_reservation = :id_reservation";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();

    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifications
    if (!$reservation) {
        throw new Exception('Réservation non trouvée');
    }

    if ($reservation['statut'] !== 'en_cours') {
        throw new Exception('Cette réservation n\'est pas en cours (statut actuel: ' . $reservation['statut'] . ')');
    }

    if ($kilometrage_retour <= $reservation['km_depart']) {
        throw new Exception('Le kilométrage de retour doit être supérieur au kilométrage de départ');
    }

    // Calculer la distance parcourue
    $distance_parcourue = $kilometrage_retour - $reservation['km_depart'];

    // 2. Préparer la note
    $note = $commentaires;
    if (!empty($reservation['note']) && !empty($commentaires)) {
        $note = $reservation['note'] . "\n" . $commentaires;
    }

    // 3. Mettre à jour la réservation
    $date_retour_effective = date('Y-m-d H:i:s'); // Date et heure actuelles

    $updateReservation = "UPDATE reservations_vehicules 
                     SET statut = 'terminee', 
                         km_retour = :kilometrage_retour,
                         date_retour_effective = :date_retour_effective,
                         note = :note,
                         materiel_retour = :materiel_retour
                     WHERE id_reservation = :id_reservation";

    $stmt = $pdo->prepare($updateReservation);
    $stmt->execute([
        ':kilometrage_retour' => $kilometrage_retour,
        ':date_retour_effective' => $date_retour_effective,
        ':note' => $note,
        ':materiel_retour' => $materiel_retour,
        ':id_reservation' => $id_reservation
    ]);

    // 4. Mettre à jour le véhicule
    $updateVehicule = "UPDATE vehicules 
                       SET statut = 'disponible',
                           kilometrage_actuel = :kilometrage_retour
                       WHERE id_vehicule = :id_vehicule";

    $stmt = $pdo->prepare($updateVehicule);
    $stmt->execute([
        ':kilometrage_retour' => $kilometrage_retour,
        ':id_vehicule' => $reservation['id_vehicule']
    ]);

    // 5. Mettre à jour le statut du chauffeur si présent
    if (!empty($reservation['id_chauffeur'])) {
        $updateChauffeur = "UPDATE chauffeurs 
                           SET statut = 'disponible'
                           WHERE id_chauffeur = :id_chauffeur";

        $stmt = $pdo->prepare($updateChauffeur);
        $stmt->execute([':id_chauffeur' => $reservation['id_chauffeur']]);
    }

    // 6. Ajouter une entrée dans le journal d'activités
    $description = "Fin de la course #" . $id_reservation .
        " - Kilométrage de départ: " . $reservation['km_depart'] .
        " km, Kilométrage de retour: " . $kilometrage_retour .
        " km, Distance parcourue: " . $distance_parcourue . " km";
    
    // Ajouter l'information sur le matériel de retour si fourni
    if (!empty($materiel_retour)) {
        $description .= " - Matériel retour: " . $materiel_retour;
    }

    $insertJournal = "INSERT INTO journal_activites (
        id_utilisateur, type_activite, description, ip_address
    ) VALUES (
        :id_utilisateur, 'fin_course', :description, :ip_address
    )";

    $stmt = $pdo->prepare($insertJournal);
    $stmt->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':description' => $description,
        ':ip_address' => $_SERVER['REMOTE_ADDR']
    ]);

    // 7. Envoyer notification si demandé
    if ($notifier_fin) {
        // TODO: Implémenter l'envoi de notification 
        // (email, SMS, notification interne, etc.)
        // Vous pouvez utiliser les informations du chauffeur et de la réservation
    }

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Course terminée avec succès',
        'kilometrage_retour' => $kilometrage_retour,
        'distance_parcourue' => $distance_parcourue
    ]);

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erreur PDO dans terminer-course.php : " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur lors de la finalisation de la course'
    ]);
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erreur dans terminer-course.php : " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}