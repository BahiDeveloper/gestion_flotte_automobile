<?php
// Inclure le fichier de configuration de la base de données
include_once("../../database" . DIRECTORY_SEPARATOR . "config.php");

// Initialiser les variables d'erreur
$errors = [];
$success = false;

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $nom = trim(htmlspecialchars($_POST['nom']));
    $prenom = trim(htmlspecialchars($_POST['prenom']));
    // $date_naissance = $_POST['date_naissance'];
    $telephone = trim(htmlspecialchars($_POST['telephone']));
    $email = !empty(trim($_POST['email'])) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : null;
    $adresse = trim(htmlspecialchars($_POST['adresse']));
    // $date_embauche = $_POST['date_embauche'];
    $numero_permis = trim(htmlspecialchars($_POST['numero_permis']));

    // Récupérer et traiter le type de permis qui est maintenant un tableau
    $type_permis = isset($_POST['type_permis']) && is_array($_POST['type_permis']) ? $_POST['type_permis'] : [];

    $date_delivrance_permis = $_POST['date_delivrance_permis'];
    $date_expiration_permis = isset($_POST['date_expiration_permis']) ? $_POST['date_expiration_permis'] : null;
    $specialisation = isset($_POST['specialisation']) ? trim(htmlspecialchars($_POST['specialisation'])) : null;
    $statut = $_POST['statut'];

    // Validation des données
    // 1. Vérifier que les champs obligatoires ne sont pas vides
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    }

    if (empty($prenom)) {
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

    // Nouveau code (à utiliser)
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

    // Validation: le tableau ne doit pas être vide
    if (empty($type_permis)) {
        $errors[] = "La catégorie de permis est obligatoire";
    }

    if (empty($date_delivrance_permis)) {
        $errors[] = "La date de délivrance du permis est obligatoire";
    }

    // Joindre les catégories avec une virgule pour stockage en base
    $type_permis_str = implode(',', $type_permis);

    // Vérifier si uniquement la catégorie A est sélectionnée
    $only_a = (count($type_permis) === 1 && $type_permis[0] === 'A');

    // Vérifier si A fait partie des catégories
    $has_a = in_array('A', $type_permis);

    // 2. Vérifier si le permis est de type A (pas besoin de date d'expiration)
    // Vérifier la date d'expiration selon les catégories sélectionnées
    if (!$only_a && empty($date_expiration_permis)) {
        $errors[] = "La date d'expiration du permis est obligatoire pour les permis autres que A seul";
    }

    // 3. Vérifier que les dates sont cohérentes
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

    // 4. Vérifier si l'email est déjà utilisé
// Nouveau code (à utiliser)
    if (!empty($email)) {
        $query = "SELECT COUNT(*) FROM chauffeurs WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['email' => $email]);

        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Cette adresse email est déjà utilisée par un autre chauffeur";
        }
    }

    // 5. Vérifier si le numéro de permis est déjà utilisé
    $query = "SELECT COUNT(*) FROM chauffeurs WHERE numero_permis = :numero_permis";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['numero_permis' => $numero_permis]);

    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Ce numéro de permis est déjà enregistré dans le système";
    }

    // 6. Traitement des fichiers uploadés
    $photo_profil = "";
    $photo_permis = "";

    // Traitement de la photo de profil
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
            $photo_profil = uniqid() . '_' . $_FILES['photo_profil']['name'];
            $upload_dir = "../../uploads/chauffeurs/profils/";

            // Créer le répertoire s'il n'existe pas
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $upload_path = $upload_dir . $photo_profil;

            if (!move_uploaded_file($_FILES['photo_profil']['tmp_name'], $upload_path)) {
                $errors[] = "Erreur lors de l'upload de la photo de profil";
            }
        }
    } else {
        $errors[] = "La photo de profil est obligatoire";
    }

    // Traitement de la photo du permis
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
            $photo_permis = uniqid() . '_' . $_FILES['photo_permis']['name'];
            $upload_dir = "../../uploads/chauffeurs/permis/";

            // Créer le répertoire s'il n'existe pas
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $upload_path = $upload_dir . $photo_permis;

            if (!move_uploaded_file($_FILES['photo_permis']['tmp_name'], $upload_path)) {
                $errors[] = "Erreur lors de l'upload de la photo du permis";
            }
        }
    } else {
        $errors[] = "La photo du permis est obligatoire";
    }

    // Si aucune erreur, insérer les données dans la base de données
    if (empty($errors)) {
        try {
            // Déterminer le statut du permis
            $statut_permis = 'valide';
            if ($only_a) {
                $statut_permis = 'permanant';
            } elseif ($has_a) {
                // Si A combiné avec d'autres catégories
                if (!empty($date_expiration_permis)) {
                    $today = new DateTime();
                    $expiration = new DateTime($date_expiration_permis);

                    if ($expiration <= $today) {
                        $statut_permis = 'expire';
                    }
                }
            } else {
                // Uniquement des catégories non-A
                if (!empty($date_expiration_permis)) {
                    $today = new DateTime();
                    $expiration = new DateTime($date_expiration_permis);

                    if ($expiration <= $today) {
                        $statut_permis = 'expire';
                    }
                }
            }

            // Insertion dans la table chauffeurs
            $query = "INSERT INTO chauffeurs (
                nom, prenoms, telephone, email, adresse, photo_profil,
                numero_permis, type_permis, date_delivrance_permis, date_expiration_permis,
                photo_permis, specialisation, statut, statut_permis, vehicule_attribue
            ) VALUES (
                :nom, :prenoms, :telephone, :email, :adresse, :photo_profil,
                :numero_permis, :type_permis, :date_delivrance_permis, :date_expiration_permis,
                :photo_permis, :specialisation, :statut, :statut_permis, 0
            )";

            $stmt = $pdo->prepare($query);
            $result = $stmt->execute([
                'nom' => $nom,
                'prenoms' => $prenom,
                // 'date_naissance' => $date_naissance,
                'telephone' => $telephone,
                'email' => $email,
                'adresse' => $adresse,
                'photo_profil' => $photo_profil,
                'numero_permis' => $numero_permis,
                'type_permis' => $type_permis_str,
                'date_delivrance_permis' => $date_delivrance_permis,
                'date_expiration_permis' => ($type_permis == 'A') ? null : $date_expiration_permis,
                'photo_permis' => $photo_permis,
                // 'date_embauche' => $date_embauche,
                'specialisation' => $specialisation,
                'statut' => $statut,
                'statut_permis' => $statut_permis
            ]);

            if ($result) {
                // Récupérer l'ID du chauffeur ajouté
                $id_chauffeur = $pdo->lastInsertId();

                // Enregistrer l'activité dans le journal
                // $user_id = 1; // À remplacer par l'ID de l'utilisateur connecté quand l'authentification sera implémentée
                // $description = "Ajout du chauffeur : $nom $prenom";

                // $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address)
                //             VALUES (:id_utilisateur, 'ajout_chauffeur', :description, :ip_address)";
                // $log_stmt = $pdo->prepare($log_query);
                // $log_stmt->execute([
                //     'id_utilisateur' => $user_id,
                //     'description' => $description,
                //     'ip_address' => $_SERVER['REMOTE_ADDR']
                // ]);

                $success = true;
                // Rediriger vers la page de gestion des chauffeurs avec un message de succès
                header("Location: ../../gestion_chauffeurs.php?success=add");
                exit();
            } else {
                $errors[] = "Erreur lors de l'ajout du chauffeur";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();

            // Supprimer les fichiers uploadés en cas d'erreur
            if (!empty($photo_profil)) {
                unlink("../../uploads/chauffeurs/profils/" . $photo_profil);
            }

            if (!empty($photo_permis)) {
                unlink("../../uploads/chauffeurs/permis/" . $photo_permis);
            }
        }
    }

    // Si des erreurs sont survenues, les stocker en session et rediriger
    if (!empty($errors)) {
        session_start();
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST; // Sauvegarder les données du formulaire pour les réafficher

        // Supprimer les fichiers uploadés en cas d'erreur
        if (!empty($photo_profil)) {
            unlink("../../uploads/chauffeurs/profils/" . $photo_profil);
        }

        if (!empty($photo_permis)) {
            unlink("../../uploads/chauffeurs/permis/" . $photo_permis);
        }

        header("Location: ../../gestion_chauffeurs.php?error=add&tab=profile");
        exit();
    }
} else {
    // Si le formulaire n'a pas été soumis via POST, rediriger vers la page principale
    header("Location: ../../gestion_chauffeurs.php");
    exit();
}