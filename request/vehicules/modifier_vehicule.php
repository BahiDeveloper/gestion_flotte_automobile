<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID du véhicule est passé en paramètre
if (!isset($_GET['id'])) {
    header('Location: gestion_vehicules.php?error_vehicule_edit=1');
    exit();
}

$id_vehicule = $_GET['id'];

// Récupérer les informations du véhicule avec la zone correspondante
$query = "SELECT v.*, z.* 
              FROM vehicules v
              LEFT JOIN zone_vehicules z ON v.id_zone = z.id
              WHERE v.id_vehicule = :id_vehicule";
$stmt = $pdo->prepare($query);
$stmt->execute([':id_vehicule' => $id_vehicule]);
$vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicule) {
    header('Location: gestion_vehicules.php?error_vehicule_edit=2');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $immatriculation = $_POST['immatriculation'];
    $type_vehicule = $_POST['type_vehicule'];
    $capacite_passagers = $_POST['capacite_passagers'];
    $kilometrage_actuel = $_POST['kilometrage_actuel'];
    $type_carburant = $_POST['type_carburant'];
    $id_zone = $_POST['id_zone'];

    // Traitement du fichier uploadé (logo de la marque)
    $logo_marque_vehicule_new_name = $vehicule['logo_marque_vehicule']; // Conserver l'ancien logo par défaut

    if (isset($_FILES['logo_marque_vehicule']) && $_FILES['logo_marque_vehicule']['error'] === UPLOAD_ERR_OK) {
        $logo_marque_vehicule_name = $_FILES['logo_marque_vehicule']['name'];
        $logo_marque_vehicule_tmp_name = $_FILES['logo_marque_vehicule']['tmp_name'];
        $logo_marque_vehicule_extension = pathinfo($logo_marque_vehicule_name, PATHINFO_EXTENSION);
        $logo_marque_vehicule_new_name = uniqid('logo_') . '.' . $logo_marque_vehicule_extension;
        $logo_marque_vehicule_dir = 'uploads/vehicules/logo_marque/';

        if (!is_dir($logo_marque_vehicule_dir)) {
            mkdir($logo_marque_vehicule_dir, 0777, true);
        }

        move_uploaded_file($logo_marque_vehicule_tmp_name, $logo_marque_vehicule_dir . $logo_marque_vehicule_new_name);

        // Supprimer l'ancien logo s'il existe
        if ($vehicule['logo_marque_vehicule'] && file_exists($logo_marque_vehicule_dir . $vehicule['logo_marque_vehicule'])) {
            unlink($logo_marque_vehicule_dir . $vehicule['logo_marque_vehicule']);
        }
    }

    // Mettre à jour les informations du véhicule dans la base de données
    $query = "UPDATE vehicules 
              SET marque = :marque, 
                  modele = :modele, 
                  logo_marque_vehicule = :logo_marque_vehicule, 
                  immatriculation = :immatriculation, 
                  type_vehicule = :type_vehicule, 
                  capacite_passagers = :capacite_passagers, 
                  type_carburant = :type_carburant, 
                  kilometrage_actuel = :kilometrage_actuel, 
                  id_zone = :id_zone 
              WHERE id_vehicule = :id_vehicule";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':marque' => $marque,
        ':modele' => $modele,
        ':logo_marque_vehicule' => $logo_marque_vehicule_new_name,
        ':immatriculation' => $immatriculation,
        ':type_vehicule' => $type_vehicule,
        ':capacite_passagers' => $capacite_passagers,
        ':type_carburant' => $type_carburant,
        ':kilometrage_actuel' => $kilometrage_actuel,
        ':id_zone' => $id_zone,
        ':id_vehicule' => $id_vehicule
    ]);

    // Rediriger vers la liste des véhicules avec un message de succès
    header('Location: gestion_vehicules.php?success_vehicule_edit=1');
    exit();
}
?>