<?php
// api/modifier-demande.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et a les droits
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['administrateur', 'gestionnaire'])) {
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
    // Récupération et validation des données
    $id_reservation = isset($_POST['id_reservation']) ? intval($_POST['id_reservation']) : 0;
    $date_depart = isset($_POST['date_depart']) ? $_POST['date_depart'] : null;
    $date_retour_prevue = isset($_POST['date_retour_prevue']) ? $_POST['date_retour_prevue'] : null;
    $nombre_passagers = isset($_POST['nombre_passagers']) ? intval($_POST['nombre_passagers']) : 0;
    $id_vehicule = isset($_POST['id_vehicule']) && !empty($_POST['id_vehicule']) ? intval($_POST['id_vehicule']) : null;
    $zone_vehicule_id = isset($_POST['zone_vehicule_id']) ? intval($_POST['zone_vehicule_id']) : null;
    $motif_modification = isset($_POST['motif_modification']) ? trim($_POST['motif_modification']) : '';

    // Validation détaillée
    $errors = [];

    if ($id_reservation <= 0) {
        $errors[] = 'Identifiant de réservation invalide';
    }

    if (empty($date_depart)) {
        $errors[] = 'La date de départ est requise';
    }

    if (empty($date_retour_prevue)) {
        $errors[] = 'La date de retour est requise';
    }

    if ($nombre_passagers <= 0) {
        $errors[] = 'Le nombre de passagers doit être supérieur à zéro';
    }

    if (empty($motif_modification)) {
        $errors[] = 'Le motif de modification est requis';
    }

    if ($zone_vehicule_id <= 0) {
        $errors[] = 'Une zone de véhicule valide est requise';
    }

    // Vérification des dates
    $now = new DateTime();
    $departDate = new DateTime($date_depart);
    $arriveeDate = new DateTime($date_retour_prevue);

    if ($departDate < $now) {
        $errors[] = 'La date de départ ne peut pas être dans le passé';
    }

    if ($arriveeDate <= $departDate) {
        $errors[] = 'La date d\'arrivée doit être postérieure à la date de départ';
    }

    // Si des erreurs existent, les renvoyer
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
        exit;
    }

    // Commencer la transaction
    $pdo->beginTransaction();

    // 1. Vérifier l'existence et le statut de la réservation
    $stmt = $pdo->prepare("
        SELECT 
            id_reservation, statut, note, 
            date_depart, date_retour_prevue, 
            nombre_passagers, id_vehicule,
            zone_vehicule_id
        FROM reservations_vehicules 
        WHERE id_reservation = :id
    ");
    $stmt->execute([':id' => $id_reservation]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        throw new Exception('Réservation introuvable');
    }

    // Vérifier si le statut permet la modification
    if (!in_array($reservation['statut'], ['en_attente', 'validee'])) {
        throw new Exception('Impossible de modifier une réservation avec le statut ' . $reservation['statut']);
    }

    // 2. Préparer la note avec l'historique des modifications
    $noteActuelle = $reservation['note'] ?? '';
    $dateModification = date('Y-m-d H:i:s');
    $entreeModification = "[Modification du $dateModification] Motif: $motif_modification";
    $nouvelleNote = !empty($noteActuelle) ? $noteActuelle . "\n\n" . $entreeModification : $entreeModification;

    // 3. Préparer la requête de mise à jour
    $updateQuery = "
        UPDATE reservations_vehicules 
        SET date_depart = :date_depart,
            date_retour_prevue = :date_retour_prevue,
            nombre_passagers = :nombre_passagers,
            note = :note,
            zone_vehicule_id = :zone_vehicule_id
    ";
    
    $updateParams = [
        ':date_depart' => $date_depart,
        ':date_retour_prevue' => $date_retour_prevue,
        ':nombre_passagers' => $nombre_passagers,
        ':note' => $nouvelleNote,
        ':zone_vehicule_id' => $zone_vehicule_id
    ];

    // Ajouter id_vehicule s'il est fourni et différent
    if ($id_vehicule !== null && $id_vehicule !== $reservation['id_vehicule']) {
        $updateQuery .= ", id_vehicule = :id_vehicule";
        $updateParams[':id_vehicule'] = $id_vehicule;
    }

    // Ajouter la condition WHERE
    $updateQuery .= " WHERE id_reservation = :id_reservation";
    $updateParams[':id_reservation'] = $id_reservation;

    // Exécuter la mise à jour
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute($updateParams);

    // 4. Journal des activités
    $description = "Modification de la réservation #$id_reservation. Motif: $motif_modification. Nouvelle zone: $zone_vehicule_id";
    $stmt = $pdo->prepare("
        INSERT INTO journal_activites 
        (id_utilisateur, type_activite, description, ip_address) 
        VALUES (:user, :type, :desc, :ip)
    ");
    $stmt->execute([
        ':user' => $_SESSION['id_utilisateur'],
        ':type' => 'modification_reservation',
        ':desc' => $description,
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);

    // 5. Vérifier si un changement de véhicule ou de zone nécessite une nouvelle validation
    if ($id_vehicule !== $reservation['id_vehicule'] || $zone_vehicule_id !== $reservation['zone_vehicule_id']) {
        // Réinitialiser le statut si changement significatif
        $stmt = $pdo->prepare("
            UPDATE reservations_vehicules 
            SET statut = 'en_attente' 
            WHERE id_reservation = :id
        ");
        $stmt->execute([':id' => $id_reservation]);
    }

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Réservation modifiée avec succès'
    ]);

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Erreur PDO dans modifier-demande.php : ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur lors de la modification'
    ]);
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Erreur dans modifier-demande.php : ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}