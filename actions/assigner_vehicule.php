<?php
function nettoyerAdresse($adresse)
{
    // Supprime tout ce qui vient après la première virgule
    $parties = explode(',', $adresse);
    return trim($parties[0]); // Retourne uniquement la première partie (l'adresse principale)
}

include_once("../database/config.php");

date_default_timezone_set('Africa/Abidjan');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_vehicule = htmlspecialchars($_POST['id_vehicule']);
    $objet_demande = htmlspecialchars($_POST['objet_demande']);

    // Nettoyer les adresses de départ et d'arrivée
    $trajet_A = nettoyerAdresse($_POST['trajet_A']);
    $trajet_B = nettoyerAdresse($_POST['trajet_B']);
    $trajet = htmlspecialchars($trajet_A . " - " . $trajet_B);

    $date_depart_prevue = $_POST['date_depart_prevue'];
    $date_arrivee_prevue = $_POST['date_arrivee_prevue'];
    $distance_trajet = htmlspecialchars($_POST['distance_trajet']);
    $duree_trajet = htmlspecialchars($_POST['duree_trajet']);

    $now = date('Y-m-d H:i:s');

    $date_depart_prevue = date('Y-m-d H:i:s', strtotime($date_depart_prevue));
    $date_arrivee_prevue = date('Y-m-d H:i:s', strtotime($date_arrivee_prevue));

    // Validation des données
    if (empty($id_vehicule) || empty($objet_demande) || empty($trajet) || empty($date_depart_prevue) || empty($date_arrivee_prevue)) {
        header("Location:../gestion_vehicules.php?error_assignation=4");
        exit();
    }

    if ($date_depart_prevue >= $date_arrivee_prevue) {
        header("Location:../gestion_vehicules.php?error_assignation=6");
        exit();
    }

    if ($date_depart_prevue < $now || $date_arrivee_prevue < $now) {
        header("Location:../gestion_vehicules.php?error_assignation=5");
        exit();
    }

    if (strlen($trajet) < 5) {
        header("Location:../gestion_vehicules.php?error_assignation=7");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Vérifier la disponibilité du véhicule
        $sql_check_vehicle = "SELECT * FROM deplacements 
                              WHERE id_vehicule = :id_vehicule 
                              AND (
                                  (date_depart_prevue <= :date_depart_prevue AND date_arrivee_prevue >= :date_depart_prevue) OR
                                  (date_depart_prevue <= :date_arrivee_prevue AND date_arrivee_prevue >= :date_arrivee_prevue) OR
                                  (date_depart_prevue >= :date_depart_prevue AND date_arrivee_prevue <= :date_arrivee_prevue)
                              )
                              AND date_arrivee IS NULL";

        $stmt_check_vehicle = $pdo->prepare($sql_check_vehicle);
        $stmt_check_vehicle->execute([
            ':id_vehicule' => $id_vehicule,
            ':date_depart_prevue' => $date_depart_prevue,
            ':date_arrivee_prevue' => $date_arrivee_prevue
        ]);

        if ($stmt_check_vehicle->rowCount() > 0) {
            header("Location:../gestion_vehicules.php?error_assignation=3");
            exit();
        }

        // Insertion de la demande de déplacement
        $sql = "INSERT INTO deplacements (id_vehicule, objet_demande, trajet, date_depart_prevue, date_arrivee_prevue, distance_trajet, duree_trajet) 
                VALUES (:id_vehicule, :objet_demande, :trajet, :date_depart_prevue, :date_arrivee_prevue, :distance_trajet, :duree_trajet)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_vehicule' => $id_vehicule,
            ':objet_demande' => $objet_demande,
            ':trajet' => $trajet,
            ':date_depart_prevue' => $date_depart_prevue,
            ':date_arrivee_prevue' => $date_arrivee_prevue,
            ':distance_trajet' => $distance_trajet,
            ':duree_trajet' => $duree_trajet
        ]);

        // Mettre à jour l'état du véhicule
        $sql_update_vehicle_status = "UPDATE vehicules 
                SET etat = 'Demandé' 
                WHERE id = :id_vehicule";
        $stmt_update_vehicle_status = $pdo->prepare($sql_update_vehicle_status);
        $stmt_update_vehicle_status->execute([':id_vehicule' => $id_vehicule]);

        $pdo->commit();

        header("Location:../gestion_vehicules.php?success_assignation=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        header("Location:../gestion_vehicules.php?error_assignation=8");
        exit();
    }
}
?>