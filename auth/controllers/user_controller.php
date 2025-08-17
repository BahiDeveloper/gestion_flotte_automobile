<?php
session_start();
require_once '../config/db_connect.php';
require_once '../models/User.php';
require_once 'auth_controller.php';

class UserController
{
    private $userModel;
    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
        $this->userModel = new User($pdo);

        // Vérifier que l'utilisateur est un administrateur
        AuthController::requireRole('administrateur');
    }

    // Liste de tous les utilisateurs
    public function index()
    {
        try {
            // Récupérer directement les utilisateurs sans passer par la session
            $stmt = $this->db->query("
            SELECT id_utilisateur, nom, prenom, email, telephone, role, date_creation, actif 
            FROM utilisateurs 
            ORDER BY date_creation DESC
        ");

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Pour le débogage
            if (empty($users)) {
                error_log("Aucun utilisateur trouvé dans la base de données");
            } else {
                error_log("Nombre d'utilisateurs trouvés : " . count($users));
            }

            // Stocker dans la session
            $_SESSION['users'] = $users;

            header('Location: ../views/admin/users_list.php');
            exit;
        } catch (Exception $e) {
            error_log("Erreur dans UserController::index : " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
            header('Location: ../views/admin/users_list.php');
            exit;
        }
    }

    // Afficher le formulaire d'ajout
    public function create()
    {
        header('Location: ../views/admin/user_add.php');
        exit;
    }

    // Traiter l'ajout d'un utilisateur
// Traiter l'ajout d'un utilisateur
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin/users_list.php');
            exit;
        }

        // Récupérer et nettoyer les données
        $data = [
            'nom' => filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS),
            'prenom' => filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'telephone' => filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_SPECIAL_CHARS),
            'role' => filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS),
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password']
        ];

        // Validation
        $errors = $this->validateUserData($data, true);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: ../views/admin/user_add.php');
            exit;
        }

        // Créer l'utilisateur
        if (
            $this->userModel->create(
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $data['password'],
                $data['telephone'],
                $data['role']
            )
        ) {
            // Journaliser l'action
            $this->logActivity('création', "Création de l'utilisateur {$data['nom']} {$data['prenom']}");
            $_SESSION['success'] = "Utilisateur créé avec succès.";

            // Charger immédiatement la liste des utilisateurs
            $stmt = $this->db->query("
            SELECT id_utilisateur, nom, prenom, email, telephone, role, date_creation, actif 
            FROM utilisateurs 
            ORDER BY date_creation DESC
        ");
            $_SESSION['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Redirection unique vers la liste
            header('Location: ../views/admin/users_list.php');
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de la création de l'utilisateur.";
            header('Location: ../views/admin/user_add.php');
            exit;
        }
    }

    // Afficher le formulaire de modification
    public function edit($id)
    {
        try {
            // Vérification de l'ID
            if (!$id) {
                throw new Exception("ID d'utilisateur invalide");
            }

            // Requête pour obtenir les détails de l'utilisateur
            $stmt = $this->db->prepare("
                SELECT id_utilisateur, nom, prenom, email, telephone, role, actif, date_creation
                FROM utilisateurs 
                WHERE id_utilisateur = :id
            ");
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception("Utilisateur non trouvé");
            }

            // Stocker les données de l'utilisateur dans la session
            $_SESSION['edit_user'] = $user;

            // Redirection vers la page d'édition
            header('Location: ../views/admin/user_edit.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ../views/admin/users_list.php');
            exit;
        }
    }

    // Traiter la modification d'un utilisateur
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin/users_list.php');
            exit;
        }

        // Récupérer et nettoyer les données
        $data = [
            'nom' => filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS),
            'prenom' => filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'telephone' => filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_SPECIAL_CHARS),
            'role' => $_POST['role']  // Utiliser directement $_POST pour le rôle
        ];

        // Validation
        $errors = $this->validateUserData($data, false, $id);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header("Location: ../views/admin/user_edit.php?id=$id");
            exit;
        }

        // Préparer les données pour la mise à jour
        $updateData = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'telephone' => $data['telephone'],
            'role' => $data['role']
        ];

        // Ajouter le mot de passe s'il est fourni
        if (!empty($_POST['password'])) {
            $updateData['mot_de_passe'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        // Debug - afficher les données avant la mise à jour
        error_log("Données de mise à jour : " . print_r($updateData, true));

        // Mettre à jour l'utilisateur
        if ($this->userModel->update($id, $updateData)) {
            // Journaliser l'action
            $this->logActivity('modification', "Modification de l'utilisateur #$id");
            $_SESSION['success'] = "Utilisateur mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur.";
        }

        header('Location: ../views/admin/users_list.php');
        exit;
    }

    // Désactiver un utilisateur
    public function disable($id)
    {
        try {
            // Vérifier que l'utilisateur n'est pas en train de se désactiver lui-même
            if ($id == $_SESSION['id_utilisateur']) {
                throw new Exception("Vous ne pouvez pas désactiver votre propre compte.");
            }

            if ($this->userModel->disable($id)) {
                // Journaliser l'action
                $this->logActivity('désactivation', "Désactivation de l'utilisateur #$id");

                $_SESSION['success'] = "Utilisateur désactivé avec succès.";
            } else {
                throw new Exception("Erreur lors de la désactivation de l'utilisateur.");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ../views/admin/users_list.php');
        exit;
    }

    // Validation des données utilisateur
    private function validateUserData($data, $isNew = true, $userId = null)
    {
        $errors = [];

        // Validation des champs obligatoires
        if (empty($data['nom']))
            $errors[] = "Le nom est obligatoire.";
        if (empty($data['prenom']))
            $errors[] = "Le prénom est obligatoire.";
        if (empty($data['email']))
            $errors[] = "L'email est obligatoire.";
        if ($isNew && empty($data['password']))
            $errors[] = "Le mot de passe est obligatoire.";

        // Validation de l'email
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        }

        // Validation de l'unicité de l'email
        if (!empty($data['email']) && $this->userModel->emailExists($data['email'], $userId)) {
            $errors[] = "Cette adresse email est déjà utilisée.";
        }

        // Validation du mot de passe
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }
            if ($data['password'] !== $data['confirm_password']) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }
        }

        // Validation du rôle
        $allowed_roles = ['administrateur', 'validateur', 'gestionnaire', 'utilisateur'];
        // Ajoutez une validation plus stricte
        if (empty($data['role']) || !in_array($data['role'], $allowed_roles)) {
            $errors[] = "Un rôle valide doit être sélectionné : " . implode(', ', $allowed_roles);
        }

        return $errors;
    }

    // Journalisation des activités
    private function logActivity($type, $description)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address)
                VALUES (:user_id, :type, :description, :ip)
            ");

            $stmt->execute([
                'user_id' => $_SESSION['id_utilisateur'],
                'type' => $type,
                'description' => $description,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
        } catch (PDOException $e) {
            error_log("Erreur de journalisation : " . $e->getMessage());
        }
    }

    // Dans UserController.php, ajouter la méthode delete :
    public function delete($id)
    {
        try {
            // Vérifier que l'utilisateur n'essaie pas de se supprimer lui-même
            if ($id == $_SESSION['id_utilisateur']) {
                throw new Exception("Vous ne pouvez pas supprimer votre propre compte.");
            }

            // Vérifier que l'utilisateur existe
            $user = $this->userModel->getById($id);
            if (!$user) {
                throw new Exception("Utilisateur non trouvé.");
            }

            // Supprimer l'utilisateur
            if ($this->userModel->delete($id)) {
                // Journaliser l'action
                $this->logActivity('suppression', "Suppression de l'utilisateur #$id");
                $_SESSION['success'] = "Utilisateur supprimé avec succès.";
            } else {
                throw new Exception("Erreur lors de la suppression de l'utilisateur.");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ../views/admin/users_list.php');
        exit;
    }

}

// Traitement des actions
$userController = new UserController();

// Router basique
$action = $_GET['action'] ?? '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Dans user_controller.php, ajouter le cas de suppression dans le switch :
switch ($action) {
    case 'create':
        $userController->create();
        break;
    case 'store':
        $userController->store();
        break;
    case 'edit':
        if ($id)
            $userController->edit($id);
        break;
    case 'update':
        if ($id)
            $userController->update($id);
        break;
    case 'disable':
        if ($id)
            $userController->disable($id);
        break;
    // Ajouter le case pour la suppression
    case 'delete':
        if ($id)
            $userController->delete($id);
        break;
    default:
        $userController->index();
        break;
}