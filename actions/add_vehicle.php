<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Vérifier que toutes les données sont présentes
    if (!isset($_POST['marque'], $_POST['modele'], $_POST['immatriculation'], $_POST['type_vehicule'], $_POST['capacite_passagers'], $_POST['kilometrage_actuel'], $_POST['type_carburant'], $_POST['id_zone'])) {
        header('Location: ../gestion_vehicules.php?error_vehicule_add=1');
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
        $logo_marque_vehicule_dir = '../uploads/vehicules/logo_marque/';

        if (!is_dir($logo_marque_vehicule_dir)) {
            mkdir($logo_marque_vehicule_dir, 0777, true);
        }

        move_uploaded_file($logo_marque_vehicule_tmp_name, $logo_marque_vehicule_dir . $logo_marque_vehicule_new_name);
    }


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

    // Rediriger vers la liste des véhicules avec un paramètre de succès
// Après avoir ajouté un véhicule avec succès
    header('Location: ../gestion_vehicules.php?success_vehicule_add=1');
    exit();
}
?>