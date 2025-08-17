<?php
// terminer_assignation.php

// Inclure la configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignation_id = $_POST['assignation_id'];
    $kilometrage_fin = $_POST['kilometrage_fin'];
    $date_arrivee_finale = date('Y-m-d H:i:s'); // Date actuelle


    // Début du bloc try-catch pour gérer les erreurs
    try {
        // Récupérer le kilométrage actuel du véhicule associé à l'assignation
        $sql_kilometrage = "SELECT v.kilometrage_actuel 
                            FROM vehicules v 
                            JOIN deplacements d ON v.id = d.id_vehicule 
                            WHERE d.id = :assignation_id";
        $stmt_kilometrage = $pdo->prepare($sql_kilometrage);
        $stmt_kilometrage->execute([':assignation_id' => $assignation_id]);
        $kilometrage_actuel = $stmt_kilometrage->fetchColumn();

        // Vérifier que le kilométrage de fin est supérieur au kilométrage actuel
        if ($kilometrage_fin <= $kilometrage_actuel) {
            throw new Exception("Le kilométrage de fin doit être supérieur au kilométrage actuel du véhicule.");
        }

        // Mettre à jour la base de données pour l'assignation
        $sql = "UPDATE deplacements 
                SET date_arrivee = :date_arrivee,
                    etat_course = 'terminee'
                WHERE id = :assignation_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':date_arrivee' => $date_arrivee_finale,
            ':assignation_id' => $assignation_id
        ]);

        // Mettre à jour le statut du véhicule (disponible) et son kilométrage actuel
        $sql_vehicle = "UPDATE vehicules SET etat = 'Disponible', kilometrage_actuel = :kilometrage_actuel WHERE id = (SELECT id_vehicule FROM deplacements WHERE id = :assignation_id)";
        $stmt_vehicle = $pdo->prepare($sql_vehicle);
        $stmt_vehicle->execute([
            ':kilometrage_actuel' => $kilometrage_fin, // Le kilométrage actuel du véhicule
            ':assignation_id' => $assignation_id
        ]);

        // Mettre à jour la disponibilité du chauffeur
        $sql_chauffeur = "UPDATE chauffeurs SET disponibilite = 'Disponible' WHERE id = (SELECT id_chauffeur FROM deplacements WHERE id = :assignation_id)";
        $stmt_chauffeur = $pdo->prepare($sql_chauffeur);
        $stmt_chauffeur->execute([
            ':assignation_id' => $assignation_id
        ]);

        // Insérer les données dans la table rapports_flotte
        $sql_rapport = "INSERT INTO rapports_flotte (id_vehicule, id_chauffeur, date_rapport, consommation, nombre_km_parcourus)
                        VALUES (
                            (SELECT id_vehicule FROM deplacements WHERE id = :assignation_id),
                            (SELECT id_chauffeur FROM deplacements WHERE id = :assignation_id),
                            :date_rapport,
                            :consommation,
                            :kilometrage_parcouru
                        )";

        $stmt_rapport = $pdo->prepare($sql_rapport);

        // Calcul de la distance parcourue
        $kilometrage_parcouru = $kilometrage_fin - $kilometrage_actuel;

        // Exemple de consommation estimée (à adapter selon vos besoins)
        $consommation = ($kilometrage_parcouru * 0.08); // Supposons 8 L/100 km

        $stmt_rapport->execute([
            ':assignation_id' => $assignation_id,
            ':date_rapport' => $date_arrivee_finale,
            ':consommation' => $consommation,
            ':kilometrage_parcouru' => $kilometrage_parcouru
        ]);


        // Redirection après succès
        header("Location: ../gestion_vehicules.php?success_assignation_termine=1");
        exit();

    } catch (Exception $e) {
        // En cas d'erreur, afficher une alerte SweetAlert et arrêter le script
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: " . json_encode($e->getMessage()) . ",
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = '../gestion_vehicules.php'; // Rediriger après la fermeture de l'alerte
                });
            };
        </script>";
        exit();
    }
}
?>