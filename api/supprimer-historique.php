<?php
/**
 * API pour supprimer une entrée de l'historique
 * Note: Cela ne supprime pas réellement la réservation, mais la marque comme "archivée"
 */

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Inclusion du fichier de configuration
require_once "../database/config.php";

// Récupérer les données envoyées
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si l'ID de réservation est fourni
if (!isset($data['id_reservation']) || empty($data['id_reservation'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de réservation non fourni'
    ]);
    exit;
}

$id_reservation = intval($data['id_reservation']);

try {
    // Débuter une transaction
    $pdo->beginTransaction();
    
    // Vérifier que la réservation existe et est terminée ou annulée
    $query = "SELECT id_reservation, statut FROM reservations_vehicules 
              WHERE id_reservation = :id_reservation 
              AND statut IN ('terminee', 'annulee')";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();
    
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Réservation non trouvée ou non terminée/annulée'
        ]);
        exit;
    }
    
    // Marquer la réservation comme archivée (au lieu de la supprimer réellement)
    // Si la table n'a pas de champ "archive", nous ajoutons une note spéciale
    $statut = $reservation['statut'] . '_archivee';
    $updateQuery = "UPDATE reservations_vehicules 
                    SET statut = :statut, 
                        note = CONCAT(IFNULL(note, ''), '\n[ARCHIVÉE LE ', NOW(), ']')
                    WHERE id_reservation = :id_reservation";
    
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':statut', $statut);
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();
    
    // Ajouter une entrée dans le journal d'activités
    $description = "Archivage de la réservation #" . $id_reservation . " depuis l'historique";
    $insertJournal = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address)
                      VALUES (:id_utilisateur, 'archivage_historique', :description, :ip_address)";
    
    $stmt = $pdo->prepare($insertJournal);
    $idUtilisateur = $_SESSION['utilisateur']['id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    $stmt->bindParam(':id_utilisateur', $idUtilisateur);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':ip_address', $ipAddress);
    $stmt->execute();
    
    // Valider la transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Entrée supprimée avec succès de l\'historique'
    ]);
    
} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    // Gérer les erreurs
    error_log("Erreur dans supprimer-historique.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression de l\'historique: ' . $e->getMessage()
    ]);
}