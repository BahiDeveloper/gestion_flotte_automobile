<?php
// Inclure le fichier de configuration de la base de données
include_once("../../database" . DIRECTORY_SEPARATOR . "config.php");

// Démarrer la session pour accéder à l'ID de l'utilisateur connecté
session_start();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Vérifier que toutes les données sont présentes
    if (!isset($_POST['marque'], $_POST['modele'], $_POST['immatriculation'], $_POST['type_vehicule'], $_POST['capacite_passagers'], $_POST['kilometrage_actuel'], $_POST['type_carburant'], $_POST['id_zone'])) {
        header('Location: ../../gestion_vehicules.php?error_vehicule_add=1');
        exit();
    }

    // Récupérer les données du formulaire
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $immatriculation = $_POST['immatriculation'];
    $type_vehicule = $_POST['type_vehicule'];
    $capacite_passagers = $_POST['capacite_passagers'];
    $kilometrage_actuel = $_POST['kilometrage_actuel'];
    $type_carburant = $_POST['type_carburant'];
    $id_zone = $_POST['id_zone'];

    $logo_marque_vehicule_new_name = null;

    // Traitement des fichiers uploadés
    if (isset($_FILES['logo_marque_vehicule']) && $_FILES['logo_marque_vehicule']['error'] === UPLOAD_ERR_OK) {
        $logo_marque_vehicule_name = $_FILES['logo_marque_vehicule']['name'];
        $logo_marque_vehicule_tmp_name = $_FILES['logo_marque_vehicule']['tmp_name'];
        $logo_marque_vehicule_extension = pathinfo($logo_marque_vehicule_name, PATHINFO_EXTENSION);
        $logo_marque_vehicule_new_name = uniqid('logo_') . '.' . $logo_marque_vehicule_extension;
        $logo_marque_vehicule_dir = '../../uploads/vehicules/logo_marque/';

        if (!is_dir($logo_marque_vehicule_dir)) {
            mkdir($logo_marque_vehicule_dir, 0777, true);
        }

        move_uploaded_file($logo_marque_vehicule_tmp_name, $logo_marque_vehicule_dir . $logo_marque_vehicule_new_name);
    }

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Préparer la requête SQL
        $query = "INSERT INTO vehicules (id_zone, marque, modele, logo_marque_vehicule, immatriculation, type_vehicule, capacite_passagers, type_carburant, kilometrage_actuel) 
                  VALUES (:id_zone, :marque, :modele, :logo_marque_vehicule, :immatriculation, :type_vehicule, :capacite_passagers, :type_carburant, :kilometrage_actuel)";
        $stmt = $pdo->prepare($query);

        // Exécuter la requête avec les données du formulaire
        $stmt->execute([
            ':id_zone' => $id_zone,
            ':marque' => $marque,
            ':modele' => $modele,
            ':logo_marque_vehicule' => $logo_marque_vehicule_new_name,
            ':immatriculation' => $immatriculation,
            ':type_vehicule' => $type_vehicule,
            ':capacite_passagers' => $capacite_passagers,
            ':type_carburant' => $type_carburant,
            ':kilometrage_actuel' => $kilometrage_actuel
        ]);

        // Récupérer l'ID du véhicule nouvellement ajouté
        $vehicule_id = $pdo->lastInsertId();

        // Vérifier si un utilisateur est connecté
        if (isset($_SESSION['id_utilisateur'])) {
            // Préparer la requête d'insertion dans le journal des activités
            $query_log = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                          VALUES (:id_utilisateur, 'ajout_vehicule', :description, :ip_address)";
            $stmt_log = $pdo->prepare($query_log);

            // Exécuter l'insertion dans le journal
            $stmt_log->execute([
                ':id_utilisateur' => $_SESSION['id_utilisateur'],
                ':description' => "Ajout d'un nouveau véhicule : {$marque} {$modele} (Immatriculation: {$immatriculation})",
                ':ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        }

        // Valider la transaction
        $pdo->commit();

        // Rediriger vers la liste des véhicules avec un paramètre de succès
        header('Location: ../../gestion_vehicules.php?success_vehicule_add=1');
        exit();

    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();

        // Gérer l'erreur (journaliser ou afficher un message)
        error_log("Erreur lors de l'ajout du véhicule : " . $e->getMessage());
        header('Location: ../../gestion_vehicules.php?error_vehicule_add=2');
        exit();
    }
}
?>