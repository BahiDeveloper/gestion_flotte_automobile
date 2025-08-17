<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si les données du formulaire ont été soumises
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $id = $_POST['id'];
    $marque = isset($_POST['marque']) ? $_POST['marque'] : null;
    $modele = isset($_POST['modele']) ? $_POST['modele'] : null;
    $immatriculation = isset($_POST['immatriculation']) ? $_POST['immatriculation'] : null;
    $typeVehicule = isset($_POST['type_vehicule']) ? $_POST['type_vehicule'] : null;
    $capacite = isset($_POST['capacite']) ? $_POST['capacite'] : null;
    // $etat = isset($_POST['etat']) ? $_POST['etat'] : null;
    // $kilometrage = isset($_POST['kilometrage_actuel']) ? $_POST['kilometrage_actuel'] : null;

    // Construire la requête SQL dynamiquement en fonction des champs modifiés
    $query = "UPDATE vehicules SET ";
    $updates = [];
    $params = [];

    if ($marque !== null) {
        $updates[] = "marque = :marque";
        $params['marque'] = $marque;
    }
    if ($modele !== null) {
        $updates[] = "modele = :modele";
        $params['modele'] = $modele;
    }
    if ($immatriculation !== null) {
        $updates[] = "immatriculation = :immatriculation";
        $params['immatriculation'] = $immatriculation;
    }
    if ($typeVehicule !== null) {
        $updates[] = "type_vehicule = :typeVehicule";
        $params['typeVehicule'] = $typeVehicule;
    }
    if ($capacite !== null) {
        $updates[] = "capacite = :capacite";
        $params['capacite'] = $capacite;
    }
    // if ($etat !== null) {
    //     $updates[] = "etat = :etat";
    //     $params['etat'] = $etat;
    // }
    // if ($kilometrage !== null) {
    //     $updates[] = "kilometrage_actuel = :kilometrage";
    //     $params['kilometrage'] = $kilometrage;
    // }

    // Ajouter l'ID à la fin des paramètres
    $params['id'] = $id;

    // Construire la requête finale
    $query .= implode(", ", $updates) . " WHERE id = :id";

    // Exécuter la requête
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Rediriger vers la page de gestion des véhicules avec un message de succès
    header('Location: ../gestion_vehicules.php?success_vehicule_up=1');
    exit();
}