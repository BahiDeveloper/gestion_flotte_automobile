<?php
// api/details-reservation.php
header('Content-Type: application/json');
require_once '../database/config.php';

// Vérifier si l'utilisateur est connecté et a les droits appropriés
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour accéder à cette ressource']);
    exit;
}

// Vérifier si l'ID de réservation est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Identifiant de réservation manquant']);
    exit;
}

$id_reservation = intval($_GET['id']);

try {
    // Récupérer les détails complets de la réservation avec toutes les informations associées
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            u.nom AS demandeur_nom, 
            u.prenom AS demandeur_prenom,
            u.telephone AS demandeur_telephone,
            u.email AS demandeur_email,
            v.marque, 
            v.modele, 
            v.immatriculation, 
            v.id_vehicule, 
            v.type_vehicule,
            v.capacite_passagers,
            v.logo_marque_vehicule,
            c.nom AS chauffeur_nom, 
            c.prenoms AS chauffeur_prenoms,
            c.telephone AS chauffeur_telephone,
            c.email AS chauffeur_email,
            i.point_depart, 
            i.point_arrivee, 
            i.distance_prevue, 
            i.temps_trajet_prevu,
            i.points_intermediaires
        FROM 
            reservations_vehicules r
        LEFT JOIN 
            utilisateurs u ON r.id_utilisateur = u.id_utilisateur
        LEFT JOIN 
            vehicules v ON r.id_vehicule = v.id_vehicule
        LEFT JOIN 
            chauffeurs c ON r.id_chauffeur = c.id_chauffeur
        LEFT JOIN 
            itineraires i ON r.id_reservation = i.id_reservation
        WHERE 
            r.id_reservation = ?
    ");
    $stmt->execute([$id_reservation]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        echo json_encode(['success' => false, 'message' => 'Réservation introuvable']);
        exit;
    }

    // Vérifier si l'utilisateur a le droit d'accéder à cette réservation
    // Les administrateurs, gestionnaires et validateurs ont accès à toutes les réservations
    // Les utilisateurs n'ont accès qu'à leurs propres réservations
    if ($_SESSION['role'] === 'utilisateur' && $reservation['id_utilisateur'] != $_SESSION['id_utilisateur']) {
        echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits pour accéder à cette réservation']);
        exit;
    }

    // Formater les détails pour l'affichage
    $formatted_reservation = [
        // Informations de base de la réservation
        'id_reservation' => $reservation['id_reservation'],
        'id_utilisateur' => $reservation['id_utilisateur'],
        'id_vehicule' => $reservation['id_vehicule'],
        'id_chauffeur' => $reservation['id_chauffeur'],
        'demandeur' => $reservation['demandeur'] ?? ($reservation['demandeur_nom'] . ' ' . $reservation['demandeur_prenom']),
        'demandeur_contact' => [
            'nom' => $reservation['demandeur_nom'] ?? '',
            'prenom' => $reservation['demandeur_prenom'] ?? '',
            'telephone' => $reservation['demandeur_telephone'] ?? '',
            'email' => $reservation['demandeur_email'] ?? ''
        ],

        // Dates importantes
        'date_demande' => $reservation['date_demande'],
        'date_depart' => $reservation['date_depart'],
        'date_debut_effective' => $reservation['date_debut_effective'],
        'date_retour_prevue' => $reservation['date_retour_prevue'],
        'date_retour_effective' => $reservation['date_retour_effective'],

        // Détails sur les passagers et le chargement
        'nombre_passagers' => $reservation['nombre_passagers'],
        'type_chargement' => $reservation['type_chargement'],

        // Kilométrage
        'km_depart' => $reservation['km_depart'],
        'km_retour' => $reservation['km_retour'],
        'distance_parcourue' => $reservation['km_retour'] && $reservation['km_depart']
            ? ($reservation['km_retour'] - $reservation['km_depart'])
            : null,

        // Informations sur le statut
        'statut' => $reservation['statut'],
        'statut_libelle' => getStatusLabel($reservation['statut']),
        'priorite' => $reservation['priorite'],
        'priorite_libelle' => getPrioriteLabel($reservation['priorite']),
        'note' => $reservation['note'],
        'objet_demande' => $reservation['objet_demande'] ?? '',
        'materiel' => $reservation['materiel'] ?? '',

        // Informations sur le véhicule
        'vehicule' => [
            'id' => $reservation['id_vehicule'],
            'marque' => $reservation['marque'] ?? '',
            'modele' => $reservation['modele'] ?? '',
            'immatriculation' => $reservation['immatriculation'] ?? '',
            'type' => $reservation['type_vehicule'] ?? '',
            'capacite' => $reservation['capacite_passagers'] ?? '',
            'logo_marque_vehicule' => $reservation['logo_marque_vehicule'] ?? '',
            'designation' => ($reservation['marque'] ?? '') . ' ' . ($reservation['modele'] ?? '') . ' (' . ($reservation['immatriculation'] ?? '') . ')'
        ],

        // Informations sur le chauffeur
        'chauffeur' => [
            'nom' => $reservation['chauffeur_nom'] ?? '',
            'prenoms' => $reservation['chauffeur_prenoms'] ?? '',
            'nom_complet' => trim(($reservation['chauffeur_nom'] ?? '') . ' ' . ($reservation['chauffeur_prenoms'] ?? '')),
            'telephone' => $reservation['chauffeur_telephone'] ?? '',
            'email' => $reservation['chauffeur_email'] ?? ''
        ],

        // Informations sur l'itinéraire
        'itineraire' => [
            'point_depart' => $reservation['point_depart'] ?? '',
            'point_arrivee' => $reservation['point_arrivee'] ?? '',
            'distance_prevue' => $reservation['distance_prevue'] ?? 0,
            'temps_trajet_prevu' => $reservation['temps_trajet_prevu'] ?? 0,
            'points_intermediaires' => $reservation['points_intermediaires'] ?? ''
        ],

        // Permissions spécifiques basées sur le rôle et l'état
        'permissions' => [
            'peut_modifier' => canModifyReservation($_SESSION['role'], $reservation['statut']),
            'peut_valider' => canValidateReservation($_SESSION['role'], $reservation['statut']),
            'peut_refuser' => canRejectReservation($_SESSION['role'], $reservation['statut']),
            'peut_annuler' => canCancelReservation($_SESSION['role'], $reservation['statut']),
            'peut_debuter' => canStartReservation($_SESSION['role'], $reservation['statut']),
            'peut_terminer' => canEndReservation($_SESSION['role'], $reservation['statut'])
        ]
    ];

    echo json_encode(['success' => true, 'reservation' => $formatted_reservation]);

} catch (PDOException $e) {
    error_log('Erreur PDO dans details-reservation.php : ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des détails: ' . $e->getMessage()]);
}

// Fonction pour obtenir un libellé explicite du statut
function getStatusLabel($status)
{
    $statusMap = [
        'en_attente' => 'En attente de validation',
        'validee' => 'Validée',
        'en_cours' => 'En cours',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée'
    ];

    return isset($statusMap[$status]) ? $statusMap[$status] : $status;
}

// Fonction pour obtenir un libellé explicite de la priorité
function getPrioriteLabel($priorite)
{
    $prioriteMap = [
        1 => 'Normale',
        2 => 'Moyenne',
        3 => 'Haute',
        4 => 'Critique'
    ];

    return isset($prioriteMap[$priorite]) ? $prioriteMap[$priorite] : 'Non définie';
}

// Vérifier si l'utilisateur peut modifier la réservation
function canModifyReservation($role, $statut)
{
    // Les administrateurs et gestionnaires peuvent modifier toutes les réservations non terminées/annulées
    if (in_array($role, ['administrateur', 'gestionnaire'])) {
        return in_array($statut, ['en_attente', 'validee', 'en_cours']);
    }

    // Les validateurs peuvent modifier les réservations en attente
    if ($role === 'validateur') {
        return $statut === 'en_attente';
    }

    // Les utilisateurs ne peuvent modifier que leurs réservations en attente
    if ($role === 'utilisateur') {
        return $statut === 'en_attente';
    }

    return false;
}

// Vérifier si l'utilisateur peut valider la réservation
function canValidateReservation($role, $statut)
{
    // Seuls les administrateurs, gestionnaires et validateurs peuvent valider des réservations en attente
    if (in_array($role, ['administrateur', 'gestionnaire', 'validateur'])) {
        return $statut === 'en_attente';
    }

    return false;
}

// Vérifier si l'utilisateur peut refuser la réservation
function canRejectReservation($role, $statut)
{
    // Même logique que pour la validation
    return canValidateReservation($role, $statut);
}

// Vérifier si l'utilisateur peut annuler la réservation
function canCancelReservation($role, $statut)
{
    // Les administrateurs et gestionnaires peuvent annuler toutes les réservations non terminées
    if (in_array($role, ['administrateur', 'gestionnaire'])) {
        return in_array($statut, ['en_attente', 'validee', 'en_cours']);
    }

    // Les validateurs peuvent annuler les réservations en attente ou validées
    if ($role === 'validateur') {
        return in_array($statut, ['en_attente', 'validee']);
    }

    // Les utilisateurs ne peuvent annuler que leurs réservations en attente
    if ($role === 'utilisateur') {
        return $statut === 'en_attente';
    }

    return false;
}

// Vérifier si l'utilisateur peut débuter la course
function canStartReservation($role, $statut)
{
    // Seuls les administrateurs, gestionnaires et validateurs peuvent débuter des courses validées
    if (in_array($role, ['administrateur', 'gestionnaire', 'validateur'])) {
        return $statut === 'validee';
    }

    return false;
}

// Vérifier si l'utilisateur peut terminer la course
function canEndReservation($role, $statut)
{
    // Seuls les administrateurs, gestionnaires et validateurs peuvent terminer des courses en cours
    if (in_array($role, ['administrateur', 'gestionnaire', 'validateur'])) {
        return $statut === 'en_cours';
    }

    return false;
}
?>