<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Initialisation des messages d'erreur
$error_message = [];
$errors = [];

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $categorie_permis = trim($_POST['categorie_permis']);
    $numero_permis = trim($_POST['numero_permis']);
    $date_delivrance_permis = $_POST['date_delivrance_permis'];
    $date_expiration_permis = $_POST['date_expiration_permis'];

    $disponibilite = (($categorie_permis !== "A") && $date_expiration_permis < date('Y-m-d')) ? 'Hors ligne' : 'Disponible';
    $statut_permis = 'Valide'; // Par défaut, le permis est permanant

    // Vérifier que la date de délivrance n'est pas dans le futur
    $date_aujourdhui = date('Y-m-d'); // Date du jour au format YYYY-MM-DD
    if ($date_delivrance_permis > $date_aujourdhui) {
        $errors['date_delivrance_permis'] = "La date de délivrance ne peut pas être dans le futur.";
    }

    // Validation des champs
    if (empty($nom))
        $errors['nom'] = "Le nom est obligatoire.";

    if (empty($prenom))
        $errors['prenom'] = "Le prénom est obligatoire.";

    if (empty($telephone))
        $errors['telephone'] = "Le téléphone est obligatoire.";

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = "L'email est invalide.";

    if (empty($adresse))
        $errors['adresse'] = "L'adresse est obligatoire.";

    if (empty($categorie_permis))
        $errors['categorie_permis'] = "La catégorie du permis est obligatoire.";

    if (empty($numero_permis))
        $errors['numero_permis'] = "Le numéro de permis est obligatoire.";

    if (empty($date_delivrance_permis))
        $errors['date_delivrance_permis'] = "La date de délivrance est obligatoire.";

    if (($categorie_permis !== "A" && empty($date_expiration_permis)) || ($categorie_permis !== "A" && $date_expiration_permis < $date_delivrance_permis))
        $errors['date_expiration_permis'] = "Date d'expiration invalide.";

    if ($categorie_permis === "A" && empty($date_expiration_permis)) {
        $statut_permis = 'Permanant';
    }


    if (empty($errors)) {
        $photo_profil_new_name = null;
        $photo_permis_new_name = null;



        if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
            $photo_profil_new_name = uniqid('profil_') . '.' . pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['photo_profil']['tmp_name'], '../uploads/chauffeurs/profils/' . $photo_profil_new_name);
        }

        if (isset($_FILES['photo_permis']) && $_FILES['photo_permis']['error'] === UPLOAD_ERR_OK) {
            $photo_permis_new_name = uniqid('permis_') . '.' . pathinfo($_FILES['photo_permis']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['photo_permis']['tmp_name'], '../uploads/chauffeurs/permis_photo/' . $photo_permis_new_name);
        }

        $sql = "UPDATE chauffeurs SET 
                                    nom = :nom, 
                                    prenom = :prenom, 
                                    telephone = :telephone, 
                                    email = :email,
                                    adresse = :adresse,
                                    categorie_permis = :categorie_permis,
                                    numero_permis = :numero_permis,
                                    date_delivrance_permis = :date_delivrance_permis,
                                    date_expiration_permis = :date_expiration_permis,
                                    disponibilite = :disponibilite,
                                    statut_permis = :statut_permis";

        if ($photo_profil_new_name)
            $sql .= ", photo_profil = :photo_profil";
        if ($photo_permis_new_name)
            $sql .= ", photo_permis = :photo_permis";

        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $params = [
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':telephone' => $telephone,
            ':email' => $email,
            ':adresse' => $adresse,
            ':categorie_permis' => $categorie_permis,
            ':numero_permis' => $numero_permis,
            ':date_delivrance_permis' => $date_delivrance_permis,
            ':date_expiration_permis' => $date_expiration_permis,
            ':disponibilite' => $disponibilite,
            ':statut_permis' => $statut_permis,
            ':id' => $id
        ];

        if ($photo_profil_new_name)
            $params[':photo_profil'] = $photo_profil_new_name;
        if ($photo_permis_new_name)
            $params[':photo_permis'] = $photo_permis_new_name;

        try {
            $stmt->execute($params);
            header("Location: ../gestion_chauffeurs.php?success_chauffeur_edit=1");
            exit();
        } catch (PDOException $e) {
            $error_message[] = "Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $error_message = array_values($errors);
    }
}
?>