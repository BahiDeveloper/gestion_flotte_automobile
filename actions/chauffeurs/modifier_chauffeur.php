<?php
// Inclure le fichier de configuration de la base de données
include_once("../../database" . DIRECTORY_SEPARATOR . "config.php");

// Démarrer la session
session_start();

// Initialiser les variables d'erreur
$errors = [];
$success = false;

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et valider l'ID du chauffeur
    if (!isset($_POST['id_chauffeur']) || empty($_POST['id_chauffeur'])) {
        header("Location: ../../gestion_chauffeurs.php?error=invalid_id");
        exit();
    }

    $id_chauffeur = intval($_POST['id_chauffeur']);

    // Vérifier si le chauffeur existe
    $query_check = "SELECT * FROM chauffeurs WHERE id_chauffeur = :id_chauffeur";
    $stmt_check = $pdo->prepare($query_check);
    $stmt_check->execute(['id_chauffeur' => $id_chauffeur]);
    $chauffeur = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$chauffeur) {
        header("Location: ../../gestion_chauffeurs.php?error=not_found");
        exit();
    }

    // Récupérer et nettoyer les données du formulaire
    $nom = trim(htmlspecialchars($_POST['nom']));
    $prenoms = trim(htmlspecialchars($_POST['prenoms']));
    // $date_naissance = $_POST['date_naissance'];
    $telephone = trim(htmlspecialchars($_POST['telephone']));
    $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) ?? null;
    $adresse = trim(htmlspecialchars($_POST['adresse']));
    // $date_embauche = $_POST['date_embauche'];
    $numero_permis = trim(htmlspecialchars($_POST['numero_permis']));

    // Récupérer et traiter le type de permis (tableau)
    $type_permis = isset($_POST['type_permis']) && is_array($_POST['type_permis']) ? $_POST['type_permis'] : [];
    $type_permis_str = implode(',', $type_permis);

    $date_delivrance_permis = $_POST['date_delivrance_permis'];
    $date_expiration_permis = !empty($_POST['date_expiration_permis']) ? $_POST['date_expiration_permis'] : null;
    $statut_permis = $_POST['statut_permis'];
    $specialisation = isset($_POST['specialisation']) ? trim(htmlspecialchars($_POST['specialisation'])) : null;
    $statut = $_POST['statut'];
    $vehicule_attribue = intval($_POST['vehicule_attribue']);

    // Validation des données
    // 1. Vérifier que les champs obligatoires ne sont pas vides
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    }

    if (empty($prenoms)) {
        $errors[] = "Le prénom est obligatoire";
    }

    // if (empty($date_naissance)) {
    //     $errors[] = "La date de naissance est obligatoire";
    // } else {
    //     // Vérifier que la personne a au moins 18 ans
    //     $today = new DateTime();
    //     $birthdate = new DateTime($date_naissance);
    //     $age = $today->diff($birthdate)->y;

    //     if ($age < 18) {
    //         $errors[] = "Le chauffeur doit avoir au moins 18 ans";
    //     }
    // }

    if (empty($telephone)) {
        $errors[] = "Le numéro de téléphone est obligatoire";
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide";
    }  

    if (empty($adresse)) {
        $errors[] = "L'adresse est obligatoire";
    }

    // if (empty($date_embauche)) {
    //     $errors[] = "La date d'embauche est obligatoire";
    // }

    if (empty($numero_permis)) {
        $errors[] = "Le numéro de permis est obligatoire";
    }

    if (empty($type_permis)) {
        $errors[] = "La catégorie de permis est obligatoire";
    }

    if (empty($date_delivrance_permis)) {
        $errors[] = "La date de délivrance du permis est obligatoire";
    }

    // Vérifier si uniquement la catégorie A est sélectionnée
    $only_a = (count($type_permis) === 1 && $type_permis[0] === 'A');

    // Vérification de la date d'expiration selon les catégories de permis
    if (!$only_a && $statut_permis !== 'permanant' && empty($date_expiration_permis)) {
        $errors[] = "La date d'expiration du permis est obligatoire pour les permis autres que A seul";
    }

    // 2. Vérifier que les dates sont cohérentes
    // if (!empty($date_embauche) && !empty($date_naissance)) {
    //     $date_embauche_obj = new DateTime($date_embauche);
    //     $date_naissance_obj = new DateTime($date_naissance);

    //     if ($date_embauche_obj <= $date_naissance_obj) {
    //         $errors[] = "La date d'embauche doit être postérieure à la date de naissance";
    //     }
    // }

    // if (!empty($date_delivrance_permis) && !empty($date_naissance)) {
    //     $date_delivrance_obj = new DateTime($date_delivrance_permis);
    //     $date_naissance_obj = new DateTime($date_naissance);
    //     $date_naissance_obj->modify('+18 years'); // La personne doit avoir au moins 18 ans pour obtenir un permis

    //     if ($date_delivrance_obj < $date_naissance_obj) {
    //         $errors[] = "La date de délivrance du permis n'est pas cohérente avec l'âge minimum requis (18 ans)";
    //     }
    // }

    if (!empty($date_expiration_permis) && !empty($date_delivrance_permis)) {
        $date_expiration_obj = new DateTime($date_expiration_permis);
        $date_delivrance_obj = new DateTime($date_delivrance_permis);

        if ($date_expiration_obj <= $date_delivrance_obj) {
            $errors[] = "La date d'expiration du permis doit être postérieure à la date de délivrance";
        }
    }

    // 3. Vérifier si l'email est déjà utilisé par un autre chauffeur
    // 3. Vérifier si l'email est déjà utilisé par un autre chauffeur
if (!empty($email) && $email !== $chauffeur['email']) {
    $query = "SELECT COUNT(*) FROM chauffeurs WHERE email = :email AND id_chauffeur <> :id_chauffeur";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'email' => $email,
        'id_chauffeur' => $id_chauffeur
    ]);

    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Cette adresse email est déjà utilisée par un autre chauffeur";
    }
}

    // 4. Vérifier si le numéro de permis est déjà utilisé par un autre chauffeur
    if ($numero_permis !== $chauffeur['numero_permis']) {
        $query = "SELECT COUNT(*) FROM chauffeurs WHERE numero_permis = :numero_permis AND id_chauffeur <> :id_chauffeur";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'numero_permis' => $numero_permis,
            'id_chauffeur' => $id_chauffeur
        ]);

        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Ce numéro de permis est déjà enregistré dans le système";
        }
    }

    // 5. Traitement des fichiers uploadés
    $photo_profil = $chauffeur['photo_profil']; // Garder l'ancienne photo par défaut
    $photo_permis = $chauffeur['photo_permis']; // Garder l'ancienne photo par défaut

    // Traitement de la nouvelle photo de profil si fournie
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] == 0) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $maxFileSize = 2 * 1024 * 1024; // 2 MB

        $fileInfo = pathinfo($_FILES['photo_profil']['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "La photo de profil doit être au format JPG, JPEG ou PNG";
        } elseif ($_FILES['photo_profil']['size'] > $maxFileSize) {
            $errors[] = "La taille de la photo de profil ne doit pas dépasser 2 MB";
        } else {
            // Générer un nom unique pour le fichier
            $new_photo_profil = uniqid() . '_' . $_FILES['photo_profil']['name'];
            $upload_dir = "../../uploads/chauffeurs/profils/";

            // Créer le répertoire s'il n'existe pas
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $upload_path = $upload_dir . $new_photo_profil;

            if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $upload_path)) {
                // Supprimer l'ancienne photo si elle existe
                if (!empty($chauffeur['photo_profil']) && file_exists($upload_dir . $chauffeur['photo_profil'])) {
                    unlink($upload_dir . $chauffeur['photo_profil']);
                }
                $photo_profil = $new_photo_profil;
            } else {
                $errors[] = "Erreur lors de l'upload de la photo de profil";
            }
        }
    }

    // Traitement de la nouvelle photo du permis si fournie
    if (isset($_FILES['photo_permis']) && $_FILES['photo_permis']['error'] == 0) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $maxFileSize = 2 * 1024 * 1024; // 2 MB

        $fileInfo = pathinfo($_FILES['photo_permis']['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "La photo du permis doit être au format JPG, JPEG ou PNG";
        } elseif ($_FILES['photo_permis']['size'] > $maxFileSize) {
            $errors[] = "La taille de la photo du permis ne doit pas dépasser 2 MB";
        } else {
            // Générer un nom unique pour le fichier
            $new_photo_permis = uniqid() . '_' . $_FILES['photo_permis']['name'];
            $upload_dir = "../../uploads/chauffeurs/permis/";

            // Créer le répertoire s'il n'existe pas
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $upload_path = $upload_dir . $new_photo_permis;

            if (move_uploaded_file($_FILES['photo_permis']['tmp_name'], $upload_path)) {
                // Supprimer l'ancienne photo si elle existe
                if (!empty($chauffeur['photo_permis']) && file_exists($upload_dir . $chauffeur['photo_permis'])) {
                    unlink($upload_dir . $chauffeur['photo_permis']);
                }
                $photo_permis = $new_photo_permis;
            } else {
                $errors[] = "Erreur lors de l'upload de la photo du permis";
            }
        }
    }

    // 6. Validation du véhicule attribué
    if ($vehicule_attribue > 0) {
        // Vérifier si le véhicule existe
        $query_vehicle = "SELECT statut FROM vehicules WHERE id_vehicule = :id_vehicule";
        $stmt_vehicle = $pdo->prepare($query_vehicle);
        $stmt_vehicle->execute(['id_vehicule' => $vehicule_attribue]);
        $vehicle = $stmt_vehicle->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle) {
            $errors[] = "Le véhicule sélectionné n'existe pas";
        } elseif ($vehicle['statut'] !== 'disponible' && $vehicule_attribue != $chauffeur['vehicule_attribue']) {
            $errors[] = "Le véhicule sélectionné n'est pas disponible";
        }
    }

    // Si aucune erreur, mettre à jour les données dans la base de données
    if (empty($errors)) {
        try {
            // Démarrer une transaction
            $pdo->beginTransaction();

            // 1. Mettre à jour les informations du chauffeur
            $query = "UPDATE chauffeurs SET 
                nom = :nom,
                prenoms = :prenoms,
                -- date_naissance = :date_naissance,
                telephone = :telephone,
                email = CASE WHEN :email = '' THEN NULL ELSE :email END,
                adresse = :adresse,
                photo_profil = :photo_profil,
                numero_permis = :numero_permis,
                type_permis = :type_permis,
                date_delivrance_permis = :date_delivrance_permis,
                date_expiration_permis = :date_expiration_permis,
                photo_permis = :photo_permis,
                -- date_embauche = :date_embauche,
                specialisation = :specialisation,
                statut = :statut,
                statut_permis = :statut_permis,
                vehicule_attribue = :vehicule_attribue,
                updated_at = NOW()
                WHERE id_chauffeur = :id_chauffeur";

            $stmt = $pdo->prepare($query);
            $result = $stmt->execute([
                'nom' => $nom,
                'prenoms' => $prenoms,
                // 'date_naissance' => $date_naissance,
                'telephone' => $telephone,
                'email' => $email,
                'adresse' => $adresse,
                'photo_profil' => $photo_profil,
                'numero_permis' => $numero_permis,
                'type_permis' => $type_permis_str,
                'date_delivrance_permis' => $date_delivrance_permis,
                'date_expiration_permis' => $date_expiration_permis,
                'photo_permis' => $photo_permis,
                // 'date_embauche' => $date_embauche,
                'specialisation' => $specialisation,
                'statut' => $statut,
                'statut_permis' => $statut_permis,
                'vehicule_attribue' => $vehicule_attribue,
                'id_chauffeur' => $id_chauffeur
            ]);

            if (!$result) {
                throw new PDOException("Erreur lors de la mise à jour des informations du chauffeur");
            }

            // 2. Gérer le changement de véhicule attribué
            if ($vehicule_attribue != $chauffeur['vehicule_attribue']) {
                // Si le chauffeur avait un véhicule précédemment attribué, le libérer
                if ($chauffeur['vehicule_attribue'] > 0) {
                    $query_free = "UPDATE vehicules SET statut = 'disponible' WHERE id_vehicule = :id_vehicule AND statut = 'en_course'";
                    $stmt_free = $pdo->prepare($query_free);
                    $stmt_free->execute(['id_vehicule' => $chauffeur['vehicule_attribue']]);
                }

                // Si un nouveau véhicule est attribué, mettre à jour son statut
                if ($vehicule_attribue > 0) {
                    $query_assign = "UPDATE vehicules SET statut = CASE WHEN statut = 'disponible' THEN 'en_course' ELSE statut END WHERE id_vehicule = :id_vehicule";
                    $stmt_assign = $pdo->prepare($query_assign);
                    $stmt_assign->execute(['id_vehicule' => $vehicule_attribue]);
                }
            }

            // 3. Enregistrer l'activité dans le journal (si le système d'authentification est implémenté)
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté
                $description = "Modification des informations du chauffeur : $nom $prenoms (ID: $id_chauffeur)";

                $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address)
                            VALUES (:id_utilisateur, 'modification_chauffeur', :description, :ip_address)";
                $log_stmt = $pdo->prepare($log_query);
                $log_stmt->execute([
                    'id_utilisateur' => $user_id,
                    'description' => $description,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ]);
            }

            // Valider la transaction
            $pdo->commit();

            // Rediriger vers la page de détails du chauffeur avec un message de succès
            header("Location: ../../chauffeur_details.php?id=$id_chauffeur&success=edit");
            exit();

        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $pdo->rollBack();

            $errors[] = "Erreur de base de données : " . $e->getMessage();

            // Supprimer les nouveaux fichiers uploadés en cas d'erreur
            if ($photo_profil !== $chauffeur['photo_profil'] && !empty($photo_profil)) {
                $upload_dir = "../../uploads/chauffeurs/profils/";
                if (file_exists($upload_dir . $photo_profil)) {
                    unlink($upload_dir . $photo_profil);
                }
            }

            if ($photo_permis !== $chauffeur['photo_permis'] && !empty($photo_permis)) {
                $upload_dir = "../../uploads/chauffeurs/permis/";
                if (file_exists($upload_dir . $photo_permis)) {
                    unlink($upload_dir . $photo_permis);
                }
            }
        }
    }

    // Si des erreurs sont survenues, les stocker en session et rediriger
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST; // Sauvegarder les données du formulaire pour les réafficher

        // Rediriger vers la page d'édition avec les erreurs
        header("Location: ../../edit_chauffeur.php?id=$id_chauffeur&error=edit");
        exit();
    }
} else {
    // Si le formulaire n'a pas été soumis via POST, rediriger vers la page principale
    header("Location: ../../gestion_chauffeurs.php");
    exit();
}
?>