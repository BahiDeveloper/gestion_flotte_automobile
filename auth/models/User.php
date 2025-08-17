<?php
class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Créer un nouvel utilisateur
    public function create($nom, $prenom, $email, $password, $telephone = null, $role = 'utilisateur')
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, :role)
            ");

            return $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'mot_de_passe' => $hashedPassword,
                'telephone' => $telephone,
                'role' => $role
            ]);
        } catch (PDOException $e) {
            // Log l'erreur
            error_log("Erreur de création d'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    // Vérifier les identifiants de connexion
    public function verifyLogin($email, $password)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM utilisateurs 
                WHERE email = :email AND actif = 1
            ");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur de vérification de connexion : " . $e->getMessage());
            return false;
        }
    }

    // Récupérer un utilisateur par son ID
    public function getById($id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id_utilisateur, nom, prenom, email, telephone, role, actif, date_creation
                FROM utilisateurs 
                WHERE id_utilisateur = :id
            ");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    // Récupérer tous les utilisateurs
    public function getAll()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT id_utilisateur, nom, prenom, email, telephone, role, date_creation, actif 
                FROM utilisateurs
                ORDER BY nom, prenom
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur de récupération des utilisateurs : " . $e->getMessage());
            return [];
        }
    }

    // Mettre à jour un utilisateur
    public function update($id, $data)
    {
        try {
            $fields = [];
            $params = ['id' => $id];

            foreach ($data as $key => $value) {
                if ($value !== null && $key !== 'id_utilisateur') {
                    $fields[] = "$key = :$key";
                    $params[$key] = $value;
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE utilisateurs SET " . implode(', ', $fields) . " WHERE id_utilisateur = :id";
            error_log("SQL Query: " . $sql); // Debug
            error_log("Params: " . print_r($params, true)); // Debug

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur de mise à jour d'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    // Désactiver un utilisateur
    public function disable($id)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE utilisateurs 
                SET actif = 0 
                WHERE id_utilisateur = :id
            ");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur de désactivation d'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    // Vérifier si un email existe déjà
    public function emailExists($email, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = :email";
            $params = ['email' => $email];

            if ($excludeId) {
                $sql .= " AND id_utilisateur != :id";
                $params['id'] = $excludeId;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur de vérification d'email : " . $e->getMessage());
            return false;
        }
    }

    // Vérifier si un numero de telephone existe déjà
    public function phoneExists($telephone, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE telephone = :telephone";
            $params = ['telephone' => $telephone];

            if ($excludeId) {
                $sql .= " AND id_utilisateur != :id";
                $params['id'] = $excludeId;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur de vérification de téléphone : " . $e->getMessage());
            return false;
        }
    }

    // Méthodes pour la réinitialisation du mot de passe
    public function createPasswordReset($email)
    {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->pdo->prepare("
                INSERT INTO password_resets (email, token, expiry)
                VALUES (:email, :token, :expiry)
            ");

            if (
                $stmt->execute([
                    'email' => $email,
                    'token' => $token,
                    'expiry' => $expiry
                ])
            ) {
                return $token;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur de création de reset password : " . $e->getMessage());
            return false;
        }
    }

    // Vérifier un token de réinitialisation
    public function verifyResetToken($token)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT email FROM password_resets
            WHERE token = :token AND expiry > NOW() AND used = 0
        ");
            $stmt->execute(['token' => $token]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur de vérification de token : " . $e->getMessage());
            return false;
        }
    }

    // Réinitialiser le mot de passe
    public function resetPassword($email, $token, $newPassword)
    {
        try {
            $this->pdo->beginTransaction();

            // Vérifier et marquer le token comme utilisé
            $stmt = $this->pdo->prepare("
                UPDATE password_resets
                SET used = 1
                WHERE email = :email AND token = :token AND expiry > NOW() AND used = 0
            ");
            $stmt->execute([
                'email' => $email,
                'token' => $token
            ]);

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            // Mettre à jour le mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                UPDATE utilisateurs
                SET mot_de_passe = :password
                WHERE email = :email
            ");
            $stmt->execute([
                'password' => $hashedPassword,
                'email' => $email
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur de réinitialisation de mot de passe : " . $e->getMessage());
            return false;
        }
    }

    // Dans User.php, ajouter la méthode delete :
    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur de suppression d'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    // Dans User.php, ajoutons ces deux méthodes

    public function verifyLoginByEmail($email, $password)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT * FROM utilisateurs 
            WHERE email = :email AND actif = 1
        ");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur de vérification de connexion par email : " . $e->getMessage());
            return false;
        }
    }

    public function verifyLoginByPhone($telephone, $password)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT * FROM utilisateurs 
            WHERE telephone = :telephone AND actif = 1
        ");
            $stmt->execute(['telephone' => $telephone]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur de vérification de connexion par téléphone : " . $e->getMessage());
            return false;
        }
    }

}