<?php
// api/enregistrer-reservation.php
header('Content-Type: application/json');

// Désactiver l'affichage des erreurs pour éviter de contaminer la sortie JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Capturer toute sortie potentielle
ob_start();

session_start(); // Démarrer la session au début

require_once "../database/config.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    ob_end_clean(); // Vider tout buffer de sortie
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

try {
    // Récupérer les données du formulaire
    $demandeur = filter_input(INPUT_POST, 'demandeur', FILTER_SANITIZE_STRING);
    $dateDepart = filter_input(INPUT_POST, 'dateDepartPrevue', FILTER_SANITIZE_STRING);
    $dateArrivee = filter_input(INPUT_POST, 'dateArriveePrevue', FILTER_SANITIZE_STRING);
    $typeVehicule = filter_input(INPUT_POST, 'typeVehicule', FILTER_SANITIZE_STRING);
    $nbPassagers = filter_input(INPUT_POST, 'nbPassagers', FILTER_VALIDATE_INT) ?: 0;
    $vehicule = filter_input(INPUT_POST, 'vehicule', FILTER_SANITIZE_STRING);
    $objetDemande = filter_input(INPUT_POST, 'objetDemande', FILTER_SANITIZE_STRING);
    $lieuDepart = filter_input(INPUT_POST, 'lieuDepart', FILTER_SANITIZE_STRING);
    $lieuArrivee = filter_input(INPUT_POST, 'lieuArrivee', FILTER_SANITIZE_STRING);
    $kilometrageEstimee = filter_input(INPUT_POST, 'kilometrageEstimee', FILTER_VALIDATE_FLOAT) ?: 0;
    $dureeEstimee = filter_input(INPUT_POST, 'dureeEstimee', FILTER_VALIDATE_FLOAT) ?: 0;
    $zoneVehicule = filter_input(INPUT_POST, 'zoneVehicule', FILTER_VALIDATE_INT) ?: 0;

    // Validation des données
    $errors = [];
    if (empty($demandeur)) $errors[] = "Le nom du demandeur est requis";
    if (empty($dateDepart)) $errors[] = "La date de départ est requise";
    if (empty($dateArrivee)) $errors[] = "La date d'arrivée est requise";
    if (empty($typeVehicule)) $errors[] = "Le type de véhicule est requis";
    if ($nbPassagers <= 0) $errors[] = "Le nombre de passagers doit être positif";
    if (empty($vehicule)) $errors[] = "Un véhicule doit être sélectionné";
    if (empty($objetDemande)) $errors[] = "L'objet de la demande est requis";
    if (empty($lieuDepart)) $errors[] = "Le lieu de départ est requis";
    if (empty($lieuArrivee)) $errors[] = "Le lieu d'arrivée est requis";
    if ($zoneVehicule <= 0) $errors[] = "La zone du véhicule est requise";

    if (!empty($errors)) {
        throw new Exception(implode(", ", $errors));
    }

    // Vérification des dates
    $now = new DateTime();
    $departDate = new DateTime($dateDepart);
    $arriveeDate = new DateTime($dateArrivee);

    if ($departDate < $now) {
        throw new Exception('La date de départ ne peut pas être dans le passé');
    }

    if ($arriveeDate <= $departDate) {
        throw new Exception('La date d\'arrivée doit être postérieure à la date de départ');
    }

    // Démarrer la transaction
    $pdo->beginTransaction();

    // Insérer la réservation
    $sql = "INSERT INTO reservations_vehicules (
        id_utilisateur, demandeur, date_demande, date_depart, date_retour_prevue, 
        nombre_passagers, id_vehicule, statut, objet_demande, date_debut_effective, materiel
    ) VALUES (
        :id_utilisateur, :demandeur, NOW(), :date_depart, :date_retour_prevue,
        :nombre_passagers, :id_vehicule, 'en_attente', :objet_demande, '0000-00-00 00:00:00', ''
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':demandeur' => $demandeur,
        ':date_depart' => $dateDepart,
        ':date_retour_prevue' => $dateArrivee,
        ':nombre_passagers' => $nbPassagers,
        ':id_vehicule' => $vehicule,
        ':objet_demande' => $objetDemande
    ]);

    $idReservation = $pdo->lastInsertId();

    // Insérer l'itinéraire
    $sqlItineraire = "INSERT INTO itineraires (
        id_reservation, point_depart, point_arrivee, 
        distance_prevue, temps_trajet_prevu
    ) VALUES (
        :id_reservation, :point_depart, :point_arrivee,
        :distance_prevue, :temps_trajet_prevu
    )";

    $stmt = $pdo->prepare($sqlItineraire);
    $stmt->execute([
        ':id_reservation' => $idReservation,
        ':point_depart' => $lieuDepart,
        ':point_arrivee' => $lieuArrivee,
        ':distance_prevue' => $kilometrageEstimee,
        ':temps_trajet_prevu' => $dureeEstimee
    ]);

    // Journal d'activité
    $sqlJournal = "INSERT INTO journal_activites (
        id_utilisateur, type_activite, description
    ) VALUES (
        :id_utilisateur, 'creation_reservation', 
        :description
    )";

    $stmt = $pdo->prepare($sqlJournal);
    $stmt->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':description' => "Nouvelle réservation créée #$idReservation pour $demandeur"
    ]);

    // Valider la transaction
    $pdo->commit();

    // Vider tout buffer de sortie avant d'envoyer la réponse JSON
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Réservation enregistrée avec succès',
        'id_reservation' => $idReservation
    ]);

} catch (Exception $e) {
    // Annuler la transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Erreur lors de l'enregistrement de la réservation : " . $e->getMessage());
    
    // Vider tout buffer de sortie avant d'envoyer la réponse JSON
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}