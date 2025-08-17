<?php
// Démarrer la session
session_start();

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../database/config.php');

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

try {
    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation des données
    $validationRules = [
        'id_reservation' => ['required' => true, 'type' => 'int'],
        'id_vehicule' => ['required' => true, 'type' => 'int'],
        'id_chauffeur' => ['required' => true, 'type' => 'int'],
        'commentaire' => ['required' => false, 'type' => 'string'],
        'priorite' => ['required' => false, 'type' => 'int', 'default' => 1],
        'notification' => ['required' => false, 'type' => 'bool', 'default' => false]
    ];

    // Valider et filtrer les données
    $data = [];
    foreach ($validationRules as $field => $rule) {
        if ($rule['required'] && !isset($input[$field])) {
            throw new Exception("Le champ $field est obligatoire");
        }

        $value = $input[$field] ?? ($rule['default'] ?? null);

        switch ($rule['type']) {
            case 'int':
                $data[$field] = filter_var($value, FILTER_VALIDATE_INT);
                break;
            case 'bool':
                $data[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'string':
                $data[$field] = trim($value);
                break;
        }

        if ($rule['required'] && $data[$field] === false) {
            throw new Exception("Le champ $field est invalide");
        }
    }

    // Commencer la transaction
    $pdo->beginTransaction();

    // Vérifier l'existence de la réservation
    $stmt = $pdo->prepare("SELECT statut FROM reservations_vehicules WHERE id_reservation = :id");
    $stmt->execute([':id' => $data['id_reservation']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        throw new Exception('Réservation non trouvée');
    }

    if ($reservation['statut'] !== 'en_attente') {
        throw new Exception('La réservation ne peut pas être validée (statut actuel: ' . $reservation['statut'] . ')');
    }

    // Mettre à jour la réservation
    $updateReservation = "UPDATE reservations_vehicules 
                          SET statut = 'validee', 
                              id_vehicule = :vehicule, 
                              id_chauffeur = :chauffeur, 
                              note = :commentaire,
                              priorite = :priorite
                          WHERE id_reservation = :id";
    $stmt = $pdo->prepare($updateReservation);
    $stmt->execute([
        ':id' => $data['id_reservation'],
        ':vehicule' => $data['id_vehicule'],
        ':chauffeur' => $data['id_chauffeur'],
        ':commentaire' => $data['commentaire'] ?? null,
        ':priorite' => $data['priorite']
    ]);

    // Mettre à jour le statut du véhicule
    $updateVehicule = "UPDATE vehicules 
                       SET statut = 'en_course' 
                       WHERE id_vehicule = :id";
    $pdo->prepare($updateVehicule)->execute([':id' => $data['id_vehicule']]);

    // Mettre à jour le statut du chauffeur
    $updateChauffeur = "UPDATE chauffeurs 
                        SET statut = 'en_course' 
                        WHERE id_chauffeur = :id";
    $pdo->prepare($updateChauffeur)->execute([':id' => $data['id_chauffeur']]);

    // Enregistrer dans le journal d'activités
    $insertJournal = "INSERT INTO journal_activites 
                      (id_utilisateur, type_activite, description, ip_address) 
                      VALUES (:user, :type, :desc, :ip)";
    $stmt = $pdo->prepare($insertJournal);
    $stmt->execute([
        ':user' => $_SESSION['id_utilisateur'],
        ':type' => 'reservation_validation',
        ':desc' => "Validation de la réservation #" . $data['id_reservation'],
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);

    // Envoi de notification (à implémenter selon votre système)
    if ($data['notification']) {
        // Code d'envoi de notification
    }

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Réservation validée avec succès'
    ]);

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Erreur PDO dans valider-reservation.php : ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur lors de la validation'
    ]);
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Erreur dans valider-reservation.php : ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}