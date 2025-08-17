<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir le chemin de base
define('BASE_PATH', dirname(dirname(__FILE__)));

// Corriger les chemins d'inclusion
require_once BASE_PATH . '/config/db_connect.php';
require_once BASE_PATH . '/models/User.php';

class AuthController
{
    private $userModel;
    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
        $this->userModel = new User($pdo);
    }

    // Méthode de connexion
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = filter_input(INPUT_POST, 'identifier', FILTER_SANITIZE_SPECIAL_CHARS); // Champ unique pour email ou téléphone
            $password = $_POST['password'];

            if (!$identifier || !$password) {
                $_SESSION['error'] = "Tous les champs sont obligatoires.";
                header('Location: ../views/login.php');
                exit;
            }

            // Essayer d'abord l'authentification par email
            $user = $this->userModel->verifyLoginByEmail($identifier, $password);

            // Si l'authentification par email échoue, essayer par téléphone
            if (!$user) {
                // Nettoyer le numéro de téléphone (retirer espaces et caractères spéciaux)
                $telephone = preg_replace('/\D/', '', $identifier);
                $user = $this->userModel->verifyLoginByPhone($telephone, $password);
            }


            if ($user) {
                // Initialiser la session
                $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Journaliser la connexion
                $this->logActivity($user['id_utilisateur'], 'connexion', 'Connexion réussie');

                // Rediriger selon le rôle
                switch ($user['role']) {
                    case 'administrateur':
                        header('Location: ../views/dashboard.php');
                        break;
                    case 'gestionnaire':
                        header('Location: ../../index.php');
                        break;
                    case 'validateur':
                        header('Location: ../../index.php');
                        break;
                    default:
                        header('Location: ../../index.php');
                        break;
                }
                exit;
            } else {
                $_SESSION['error'] = "Identifiant ou mot de passe incorrect.";
                header('Location: ../views/login.php');
                exit;
            }
        }
    }

    // Méthode d'inscription
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer et nettoyer les données
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
            $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_SPECIAL_CHARS);
            $telephone = preg_replace('/\D/', '', $telephone);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $role = isset($_POST['role']) ? filter_var($_POST['role'], FILTER_SANITIZE_SPECIAL_CHARS) : 'utilisateur';

            // Nettoyer le code admin s'il existe
            $codeAdmin = isset($_POST['code_admin']) ? trim(filter_var($_POST['code_admin'], FILTER_SANITIZE_SPECIAL_CHARS)) : '';

            // Code admin prédéfini
            $ADMIN_CODE = 'AdminFlotte2025!';

            // Vérifie si le rôle demandé nécessite une vérification du code admin
            $rolesRestricted = ['administrateur', 'gestionnaire'];
            if (in_array($role, $rolesRestricted)) {
                // Vérifie si le code admin est fourni et correct en utilisant une comparaison stricte et sécurisée
                if (empty($codeAdmin) || !hash_equals($ADMIN_CODE, $codeAdmin)) {
                    // Si pas de code ou code incorrect, forcer le rôle utilisateur
                    $role = 'utilisateur';
                }
            }


            // Validation
            $errors = [];

            // Changement : téléphone obligatoire, email optionnel
            if (!$nom || !$prenom || !$telephone || !$password) {
                $errors[] = "Les champs nom, prénom, téléphone et mot de passe sont obligatoires.";
            }

            // Vérifier que le téléphone contient exactement 10 chiffres
            if (strlen($telephone) !== 10) {
                $errors[] = "Le numéro de téléphone doit contenir exactement 10 chiffres.";
            }

            // Vérification de l'email seulement s'il est fourni
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Le format de l'adresse email n'est pas valide.";
            }
            // Vérification du numéro de téléphone
            if (!preg_match('/^\d{10}$/', preg_replace('/\D/', '', $telephone))) {
                $errors[] = "Le numéro de téléphone doit contenir 10 chiffres.";
            }
            // Après la vérification du format du téléphone
            if (!$this->verifyPhoneNumber($telephone)) {
                $errors[] = "Ce numéro de téléphone semble invalide. Veuillez vérifier et réessayer.";
            }
            if (strlen($password) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }
            if ($password !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }

            if ($this->userModel->emailExists($email)) {
                $errors[] = "Cette adresse email est déjà utilisée.";
            }

            // Ajouter la vérification d'unicité du téléphone
            if ($this->userModel->phoneExists($telephone)) {
                $errors[] = "Ce numéro de téléphone est déjà associé à un compte.";
            }



            // Validation du rôle
            $allowed_roles = ['administrateur', 'validateur', 'gestionnaire', 'utilisateur'];

            // if (empty($data['role']) || !in_array($data['role'], $allowed_roles)) {
            //     $errors[] = "Un rôle valide doit être sélectionné : " . implode(', ', $allowed_roles);
            // }

            // Traitement des erreurs
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST;

                // Redirection selon le type d'inscription
                $redirect_page = 'register.php';
                if ($role === 'validateur') {
                    $redirect_page = 'register_validateur.php';
                } elseif (in_array($role, ['administrateur', 'gestionnaire'])) {
                    $redirect_page = 'register_super_admin.php';
                }

                header("Location: ../views/$redirect_page");
                exit;
            }

            // Créer l'utilisateur
            if ($this->userModel->create($nom, $prenom, $email, $password, $telephone, $role)) {
                // Journaliser l'inscription
                $this->logActivity($this->db->lastInsertId(), 'inscription', 'Nouvel utilisateur inscrit');

                $_SESSION['success'] = "Inscription réussie. Vous pouvez maintenant vous connecter.";
                header('Location: ../views/login.php');
                exit;
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de l'inscription.";
                header('Location: ../views/register.php');
                exit;
            }
        }
    }

    // Méthode de déconnexion
    public function logout()
    {
        if (isset($_SESSION['id_utilisateur'])) {
            // Journaliser la déconnexion
            $this->logActivity($_SESSION['id_utilisateur'], 'deconnexion', 'Déconnexion utilisateur');
        }

        // Détruire la session
        session_unset();
        session_destroy();

        // Rediriger vers la page de connexion
        header('Location: login.php');
        exit;
    }

    // Méthode pour mot de passe oublié
// Méthode pour mot de passe oublié par email
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Veuillez fournir une adresse email valide.";
                header('Location: ../views/forgot_password.php');
                exit;
            }

            // Vérifier si l'email existe
            try {
                $stmt = $this->db->prepare("SELECT id_utilisateur, nom, prenom FROM utilisateurs WHERE email = :email AND actif = 1");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $_SESSION['error'] = "Aucun compte actif n'est associé à cette adresse email.";
                    header('Location: ../views/forgot_password.php');
                    exit;
                }

                // Générer et sauvegarder le token
                $token = $this->userModel->createPasswordReset($email);

                if ($token) {
                    // Charger le service d'email
                    require_once '../services/MailService.php';
                    $mailService = new MailService();

                    // Envoyer l'email de réinitialisation
                    $emailSent = $mailService->sendPasswordResetEmail($email, $token, $user['nom'], $user['prenom']);

                    if ($emailSent) {
                        $_SESSION['success'] = "Un email de réinitialisation a été envoyé à votre adresse.";

                        // Journaliser l'activité
                        $this->logActivity($user['id_utilisateur'], 'demande_reset', "Demande de réinitialisation de mot de passe");
                    } else {
                        $_SESSION['error'] = "Problème lors de l'envoi de l'email. Veuillez réessayer plus tard.";
                    }
                } else {
                    $_SESSION['error'] = "Une erreur est survenue. Veuillez réessayer plus tard.";
                }

                header('Location: ../views/forgot_password.php');
                exit;
            } catch (PDOException $e) {
                error_log("Erreur dans forgotPassword : " . $e->getMessage());
                $_SESSION['error'] = "Une erreur est survenue. Veuillez réessayer plus tard.";
                header('Location: ../views/forgot_password.php');
                exit;
            }
        }
    }

    // Méthode pour mot de passe oublié par téléphone
    public function forgotPasswordPhone()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);
            $telephone = preg_replace('/\D/', '', $telephone); // Garde uniquement les chiffres

            if (!$telephone || strlen($telephone) !== 10) {
                $_SESSION['error'] = "Veuillez fournir un numéro de téléphone valide.";
                header('Location: ../views/forgot_password.php');
                exit;
            }

            try {
                // Vérifier si le numéro existe
                $stmt = $this->db->prepare("SELECT id_utilisateur, nom, prenom FROM utilisateurs WHERE telephone = :telephone AND actif = 1");
                $stmt->execute(['telephone' => $telephone]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $_SESSION['error'] = "Aucun compte actif n'est associé à ce numéro de téléphone.";
                    header('Location: ../views/forgot_password.php');
                    exit;
                }

                // Générer un code de vérification à 6 chiffres
                $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

                // Sauvegarder le code dans la base de données
                $stmt = $this->db->prepare("
                INSERT INTO password_resets (telephone, code, expiry, used)
                VALUES (:telephone, :code, DATE_ADD(NOW(), INTERVAL 15 MINUTE), 0)
            ");

                if (
                    $stmt->execute([
                        'telephone' => $telephone,
                        'code' => password_hash($code, PASSWORD_DEFAULT)
                    ])
                ) {
                    // En production, envoyer le code par SMS via un service
                    // Pour le développement, on affiche simplement le code

                    // OPTION 1: Afficher le code pour le développement
                    // $_SESSION['success'] = "Un code de vérification a été envoyé par SMS. (Code: $code)";

                    // OPTION 2: En production, décommentez et adaptez ce code

                    // Intégration avec un service SMS (exemple)
                    require_once '../services/SmsService.php';
                    $smsService = new SmsService();
                    $message = "Votre code de vérification pour réinitialiser votre mot de passe est : $code";
                    $result = $smsService->send($telephone, $message);

                    if ($result) {
                        $_SESSION['success'] = "Un code de vérification a été envoyé par SMS à votre numéro.";

                        // Journaliser l'activité
                        $this->logActivity($user['id_utilisateur'], 'demande_reset_tel', "Demande de réinitialisation de mot de passe par téléphone");
                    } else {
                        throw new Exception("Erreur lors de l'envoi du SMS.");
                    }


                } else {
                    throw new Exception("Erreur lors de la génération du code.");
                }

            } catch (Exception $e) {
                error_log("Erreur reset password par téléphone : " . $e->getMessage());
                $_SESSION['error'] = "Une erreur est survenue. Veuillez réessayer plus tard.";
            }

            header('Location: ../views/forgot_password.php');
            exit;
        }
    }

    // Méthode pour réinitialiser le mot de passe
    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            if (!$token || !$password || !$confirmPassword) {
                $_SESSION['error'] = "Tous les champs sont obligatoires.";
                header("Location: ../views/reset_password.php?token=$token");
                exit;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
                header("Location: ../views/reset_password.php?token=$token");
                exit;
            }

            if (strlen($password) < 8) {
                $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères.";
                header("Location: ../views/reset_password.php?token=$token");
                exit;
            }

            // Vérifier le token
            $email = $this->userModel->verifyResetToken($token);
            if (!$email) {
                $_SESSION['error'] = "Le lien de réinitialisation est invalide ou a expiré.";
                header('Location: ../views/forgot_password.php');
                exit;
            }

            // Récupérer les informations de l'utilisateur pour la journalisation
            try {
                $stmt = $this->db->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $userId = $stmt->fetchColumn();

                // Réinitialiser le mot de passe
                if ($this->userModel->resetPassword($email, $token, $password)) {
                    // Journaliser la réinitialisation
                    if ($userId) {
                        $this->logActivity($userId, 'reset_password', "Réinitialisation du mot de passe réussie");
                    }

                    $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                    header('Location: ../views/login.php');
                } else {
                    $_SESSION['error'] = "Une erreur est survenue lors de la réinitialisation du mot de passe.";
                    header("Location: ../views/reset_password.php?token=$token");
                }
                exit;
            } catch (PDOException $e) {
                error_log("Erreur dans resetPassword : " . $e->getMessage());
                $_SESSION['error'] = "Une erreur est survenue. Veuillez réessayer plus tard.";
                header("Location: ../views/reset_password.php?token=$token");
                exit;
            }
        }
    }

    // Méthode pour journaliser les activités
    private function logActivity($userId, $type, $description)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address)
                VALUES (:user_id, :type, :description, :ip)
            ");

            $stmt->execute([
                'user_id' => $userId,
                'type' => $type,
                'description' => $description,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
        } catch (PDOException $e) {
            error_log("Erreur de journalisation : " . $e->getMessage());
        }
    }

    // Vérification si l'utilisateur est connecté
    public static function isAuthenticated()
    {
        return isset($_SESSION['id_utilisateur']);
    }

    // Vérification du rôle de l'utilisateur
    public static function hasRole($role)
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    // Redirection si non authentifié
    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            $_SESSION['error'] = "Veuillez vous connecter pour accéder à cette page.";
            header('Location: ../views/login.php');
            exit;
        }
    }

    // Redirection si pas le bon rôle
    public static function requireRole($role)
    {
        self::requireAuth();
        if (!self::hasRole($role)) {
            $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour accéder à cette page.";
            header('Location: ../views/dashboard.php');
            exit;
        }
    }

    // Méthode à ajouter à AuthController
    private function verifyPhoneNumber($phoneNumber)
    {
        try {
            require_once '../services/SmsService.php';
            $smsService = new SmsService();

            // Utiliser la méthode de vérification du service SMS
            return $smsService->verifyPhoneNumber($phoneNumber);
        } catch (Exception $e) {
            error_log("Erreur de vérification du numéro de téléphone : " . $e->getMessage());
            // En cas d'erreur du service, on accepte le numéro pour ne pas bloquer l'inscription
            return true;
        }
    }

}


// Traitement des actions
$auth = new AuthController();

// Router basique
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $auth->login();
        break;
    case 'register':
        $auth->register();
        break;
    case 'logout':
        $auth->logout();
        break;
    case 'forgot-password':
        $auth->forgotPassword();
        break;
    case 'reset-password':
        $auth->resetPassword();
        break;
    case 'forgot-password-phone':
        $auth->forgotPasswordPhone();
        break;
}

