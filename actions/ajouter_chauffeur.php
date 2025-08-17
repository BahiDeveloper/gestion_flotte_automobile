<?php
// Inclure le fichier de configuration de la base de données
include_once("../database" . DIRECTORY_SEPARATOR . "config.php");

// Initialisation des messages d'erreur et de succès
$error_message = [];
$errors = [];

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupérer les informations du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $categorie_permis = trim($_POST['categorie_permis']);
    $numero_permis = trim($_POST['numero_permis']);
    $date_delivrance_permis = $_POST['date_delivrance_permis'];
    $date_expiration_permis = $_POST['date_expiration_permis'];

    // Définir la disponibilité par défaut
    $disponibilite = 'Disponible'; // Par défaut, le chauffeur est disponible
    $statut_permis = 'Valide'; // Par défaut, le permis est permanant

    // Vérifier si la date d'expiration du permis est inférieure à la date du jour
    $date_aujourdhui = date('Y-m-d'); // Date du jour au format YYYY-MM-DD
    if ($categorie_permis !== "A" && $date_expiration_permis < $date_aujourdhui) {
        $disponibilite = 'Hors ligne'; // Si le permis est expiré, le chauffeur est hors ligne
    }

    // Vérifier que la date de délivrance n'est pas dans le futur
    if ($date_delivrance_permis > $date_aujourdhui) {
        $errors['date_delivrance_permis'] = "La date de délivrance ne peut pas être dans le futur.";
    }

    // Validation des champs
    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire.";
    }
    if (empty($prenom)) {
        $errors['prenom'] = "Le prénom est obligatoire.";
    }
    if (empty($telephone)) {
        $errors['telephone'] = "Le téléphone est obligatoire.";
    }
    if (empty($email)) {
        $errors['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "L'email n'est pas valide.";
    }
    if (empty($adresse)) {
        $errors['adresse'] = "L'adresse est obligatoire.";
    }
    if (empty($categorie_permis)) {
        $errors['categorie_permis'] = "La catégorie du permis est obligatoire.";
    }
    if (empty($numero_permis)) {
        $errors['numero_permis'] = "Le numéro de permis est obligatoire.";
    }
    if (empty($date_delivrance_permis)) {
        $errors['date_delivrance_permis'] = "La date de délivrance est obligatoire.";
    }
    if ($categorie_permis !== "A" && empty($date_expiration_permis)) {
        $errors['date_expiration_permis'] = "La date d'expiration est obligatoire.";
    } elseif ($categorie_permis !== "A" && $date_expiration_permis < $date_delivrance_permis) {
        $errors['date_expiration_permis'] = "La date d'expiration doit être postérieure à la date de délivrance.";
    }

    if ($categorie_permis === "A" && empty($date_expiration_permis)) {
        $statut_permis = 'Permanant';
    }

    // Vérification de l'unicité de l'email
    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chauffeurs WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = "Cet email est déjà utilisé par un autre chauffeur.";
        }
    }

    // Vérification de l'unicité du numéro de permis
    if (!isset($errors['numero_permis'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chauffeurs WHERE numero_permis = :numero_permis");
        $stmt->execute([':numero_permis' => $numero_permis]);
        if ($stmt->fetchColumn() > 0) {
            $errors['numero_permis'] = "Ce numéro de permis existe déjà.";
        }
    }

    if (empty($errors)) {
        // Traitement des fichiers uploadés
        if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
            $photo_profil_name = $_FILES['photo_profil']['name'];
            $photo_profil_tmp_name = $_FILES['photo_profil']['tmp_name'];
            $photo_profil_extension = pathinfo($photo_profil_name, PATHINFO_EXTENSION);
            $photo_profil_new_name = uniqid('profil_') . '.' . $photo_profil_extension;
            $photo_profil_dir = '../uploads/chauffeurs/profils/';

            // Créer le dossier si nécessaire
            if (!is_dir($photo_profil_dir)) {
                mkdir($photo_profil_dir, 0777, true);
            }

            // Déplacer le fichier dans le dossier approprié
            move_uploaded_file($photo_profil_tmp_name, $photo_profil_dir . $photo_profil_new_name);
        } else {
            $errors['photo_profil'] = "La photo de profil est obligatoire.";
        }

        if (isset($_FILES['photo_permis']) && $_FILES['photo_permis']['error'] === UPLOAD_ERR_OK) {
            $photo_permis_name = $_FILES['photo_permis']['name'];
            $photo_permis_tmp_name = $_FILES['photo_permis']['tmp_name'];
            $photo_permis_extension = pathinfo($photo_permis_name, PATHINFO_EXTENSION);
            $photo_permis_new_name = uniqid('permis_') . '.' . $photo_permis_extension;
            $photo_permis_dir = '../uploads/chauffeurs/permis_photo/';

            // Créer le dossier si nécessaire
            if (!is_dir($photo_permis_dir)) {
                mkdir($photo_permis_dir, 0777, true);
            }

            // Déplacer le fichier dans le dossier approprié
            move_uploaded_file($photo_permis_tmp_name, $photo_permis_dir . $photo_permis_new_name);
        } else {
            $errors['photo_permis'] = "La photo du permis est obligatoire.";
        }
    }

    // Si aucune erreur, procéder à l'insertion
    if (empty($errors)) {
        $sql = "INSERT INTO chauffeurs (nom, 
                                        prenom, 
                                        telephone, 
                                        email, 
                                        adresse, 
                                        photo_profil, 
                                        categorie_permis, 
                                        numero_permis, 
                                        date_delivrance_permis, 
                                        date_expiration_permis, 
                                        photo_permis,
                                        disponibilite,
                                        statut_permis) 
                VALUES (:nom, 
                        :prenom, 
                        :telephone, 
                        :email, 
                        :adresse, 
                        :photo_profil, 
                        :categorie_permis, 
                        :numero_permis, 
                        :date_delivrance_permis, 
                        :date_expiration_permis, 
                        :photo_permis,
                        :disponibilite,
                        :statut_permis)";

        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':telephone' => $telephone,
                ':email' => $email,
                ':adresse' => $adresse,
                ':photo_profil' => $photo_profil_new_name,
                ':categorie_permis' => $categorie_permis,
                ':numero_permis' => $numero_permis,
                ':date_delivrance_permis' => $date_delivrance_permis,
                ':date_expiration_permis' => $date_expiration_permis,
                ':photo_permis' => $photo_permis_new_name,
                ':disponibilite' => $disponibilite,
                ':statut_permis' => $statut_permis
            ]);
            header("Location: ../gestion_chauffeurs.php?success_chauffeur=1");
            exit();
        } catch (PDOException $e) {
            $error_message[] = "Erreur lors de l'ajout du chauffeur : " . $e->getMessage();
        }
    } else {
        // Si des erreurs sont présentes, on les affichera dans le formulaire
        $error_message = array_values($errors);
    }
}
?>