<?php
/**
 * API pour confirmer le début d'une course et enregistrer le kilométrage de départ
 */

// Headers pour JSON et CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Inclusion du fichier de configuration
require_once "../database/config.php";

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

// Récupérer les données envoyées
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si toutes les données requises sont fournies
if (!isset($data['id_reservation']) || !isset($data['kilometrage_depart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Données incomplètes. ID de réservation et kilométrage de départ requis.'
    ]);
    exit;
}

$id_reservation = intval($data['id_reservation']);
$kilometrage_depart = intval($data['kilometrage_depart']);
$materiel = isset($data['materiel']) ? trim($data['materiel']) : '';
$acteurs = isset($data['acteurs']) ? trim($data['acteurs']) : '';
$idUtilisateur = $_SESSION['id_utilisateur'];
$dateDebut = date('Y-m-d H:i:s'); // Date et heure actuelles

try {
    // Débuter une transaction
    $pdo->beginTransaction();

    // 1. Récupérer les informations actuelles de la réservation et du véhicule
    $query = "SELECT 
                r.id_reservation, r.id_vehicule, r.statut, 
                v.kilometrage_actuel, v.statut as statut_vehicule,
                c.id_chauffeur, c.statut as statut_chauffeur
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
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Réservation non trouvée'
        ]);
        exit;
    }

    if ($reservation['statut'] !== 'validee') {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Cette réservation ne peut pas être démarrée (statut actuel: ' . $reservation['statut'] . ')'
        ]);
        exit;
    }

    if ($kilometrage_depart < $reservation['kilometrage_actuel']) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Le kilométrage de départ ne peut pas être inférieur au kilométrage actuel du véhicule'
        ]);
        exit;
    }

    // 2. Mettre à jour le statut de la réservation, le kilométrage de départ, 
    // le matériel, les acteurs, et la date de début effective
    $updateReservation = "UPDATE reservations_vehicules 
                          SET statut = 'en_cours', 
                              km_depart = :kilometrage_depart,
                              materiel = :materiel,
                              acteurs = :acteurs,
                              date_debut_effective = :date_debut_effective
                          WHERE id_reservation = :id_reservation";

    $stmt = $pdo->prepare($updateReservation);
    $stmt->bindParam(':kilometrage_depart', $kilometrage_depart);
    $stmt->bindParam(':materiel', $materiel);
    $stmt->bindParam(':acteurs', $acteurs);
    $stmt->bindParam(':date_debut_effective', $dateDebut);
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();

    // 3. Mettre à jour le statut et le kilométrage du véhicule
    $updateVehicule = "UPDATE vehicules 
                       SET statut = 'en_course',
                           kilometrage_actuel = :kilometrage_depart
                       WHERE id_vehicule = :id_vehicule";

    $stmt = $pdo->prepare($updateVehicule);
    $stmt->bindParam(':kilometrage_depart', $kilometrage_depart);
    $stmt->bindParam(':id_vehicule', $reservation['id_vehicule']);
    $stmt->execute();

    // 4. Mettre à jour le statut du chauffeur si présent
    if (!empty($reservation['id_chauffeur'])) {
        $updateChauffeur = "UPDATE chauffeurs 
                           SET statut = 'en_course'
                           WHERE id_chauffeur = :id_chauffeur";

        $stmt = $pdo->prepare($updateChauffeur);
        $stmt->bindParam(':id_chauffeur', $reservation['id_chauffeur']);
        $stmt->execute();
    }

    // 5. Ajouter une entrée dans le journal d'activités
    $description = "Début de la course #" . $id_reservation . " - Kilométrage de départ: " . $kilometrage_depart . " km";

    // Ajouter l'information sur le matériel si fourni
    if (!empty($materiel)) {
        $description .= " - Matériel: " . $materiel;
    }
    
    // Ajouter l'information sur les acteurs si fourni
    if (!empty($acteurs)) {
        $description .= " - Acteurs: " . $acteurs;
    }

    $insertJournal = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address)
                      VALUES (:id_utilisateur, 'debut_course', :description, :ip_address)";

    $stmt = $pdo->prepare($insertJournal);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt->bindParam(':id_utilisateur', $idUtilisateur);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':ip_address', $ipAddress);
    $stmt->execute();

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Course démarrée avec succès',
        'kilometrage_depart' => $kilometrage_depart,
        'date_debut_effective' => $dateDebut
    ]);

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if (isset($pdo)) {
        $pdo->rollBack();
    }

    // Gérer les erreurs
    error_log("Erreur dans confirmer-debut-course.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du démarrage de la course: ' . $e->getMessage()
    ]);
}