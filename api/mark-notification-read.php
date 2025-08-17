<?php
// api/mark-notification-read.php
header('Content-Type: application/json');
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

// Vérifier si les données nécessaires sont fournies
if (!isset($_POST['notification_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de notification manquant'
    ]);
    exit;
}

// Récupérer l'ID et le type de notification
$notification_id = $_POST['notification_id'];
$mark_all = isset($_POST['mark_all']) && $_POST['mark_all'] === 'true';
$user_id = $_SESSION['id_utilisateur'];

require_once "../database/config.php";

try {
    if ($mark_all) {
        // Si on veut marquer toutes les notifications comme lues

        // Option 1: Si vous avez une table notifications_lues
        $stmt = $pdo->prepare("
            INSERT INTO notifications_lues (id_utilisateur, type_notification, id_reference, date_lecture)
            SELECT 
                :user_id, 
                CASE 
                    WHEN r.id_reservation IS NOT NULL THEN 'reservation'
                    WHEN m.id_maintenance IS NOT NULL THEN 'maintenance_en_cours'
                    WHEN d.id_document IS NOT NULL THEN 'document_a_renouveler'
                    ELSE 'autre'
                END,
                COALESCE(r.id_reservation, m.id_maintenance, d.id_document, 0),
                NOW()
            FROM 
                (SELECT id_reservation FROM reservations_vehicules WHERE statut = 'en_attente' LIMIT 10) r,
                (SELECT id_maintenance FROM maintenances WHERE statut = 'en_cours' LIMIT 5) m,
                (SELECT id_document FROM documents_administratifs 
                 WHERE DATEDIFF(date_expiration, CURDATE()) <= 60 AND statut != 'expire' LIMIT 5) d
            WHERE 
                CONCAT(
                    CASE 
                        WHEN r.id_reservation IS NOT NULL THEN 'reservation'
                        WHEN m.id_maintenance IS NOT NULL THEN 'maintenance_en_cours'
                        WHEN d.id_document IS NOT NULL THEN 'document_a_renouveler'
                        ELSE 'autre'
                    END,
                    '_',
                    COALESCE(r.id_reservation, m.id_maintenance, d.id_document, 0)
                ) NOT IN (
                    SELECT CONCAT(type_notification, '_', id_reference) 
                    FROM notifications_lues 
                    WHERE id_utilisateur = :user_id
                )
            ON DUPLICATE KEY UPDATE date_lecture = NOW()
        ");

        $stmt->execute(['user_id' => $user_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Toutes les notifications ont été marquées comme lues'
        ]);
    } else {
        // Marquer une notification spécifique comme lue

        // Extraire le type et l'ID de la notification
        list($type, $id) = explode('_', $notification_id, 2);

        // Option 1: Si vous avez une table notifications_lues
        $stmt = $pdo->prepare("
            INSERT INTO notifications_lues (id_utilisateur, type_notification, id_reference, date_lecture)
            VALUES (:user_id, :type, :id, NOW())
            ON DUPLICATE KEY UPDATE date_lecture = NOW()
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'type' => $type,
            'id' => $id
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Notification marquée comme lue',
            'notification_id' => $notification_id
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du marquage de la notification: ' . $e->getMessage()
    ]);
}