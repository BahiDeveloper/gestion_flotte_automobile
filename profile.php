<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth/controllers/auth_controller.php';
AuthController::requireAuth();

$title = "Mon profil";
require_once 'auth/includes/header.php';

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT id_utilisateur, nom, prenom, email, telephone, role, date_creation, actif
        FROM utilisateurs 
        WHERE id_utilisateur = :id
    ");
    $stmt->execute(['id' => $_SESSION['id_utilisateur']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les dernières activités de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT *
        FROM journal_activites
        WHERE id_utilisateur = :id
        ORDER BY date_activite DESC
        LIMIT 5
    ");
    $stmt->execute(['id' => $_SESSION['id_utilisateur']]);
    $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des données utilisateur : " . $e->getMessage());
    $_SESSION['error'] = "Une erreur est survenue lors de la récupération de vos informations.";
    $user = [];
    $activites = [];
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="fas fa-user-circle me-2"></i>Mon profil</h1>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Informations personnelles -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Informations personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <form id="profileForm" action="auth/controllers/user_controller.php?action=update_profile"
                        method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                value="<?= htmlspecialchars($user['nom']) ?>" required>
                            <div class="invalid-feedback">
                                Veuillez entrer votre nom.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom"
                                value="<?= htmlspecialchars($user['prenom']) ?>" required>
                            <div class="invalid-feedback">
                                Veuillez entrer votre prénom.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= htmlspecialchars($user['email']) ?>" required>
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone"
                                value="<?= htmlspecialchars($user['telephone']) ?>" pattern="[0-9\s+]{10,}">
                            <div class="invalid-feedback">
                                Veuillez entrer un numéro de téléphone valide.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modification du mot de passe -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2"></i>Modification du mot de passe
                    </h5>
                </div>
                <div class="card-body">
                    <form id="passwordForm" action="auth/controllers/user_controller.php?action=update_password"
                        method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password"
                                    name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Veuillez entrer votre mot de passe actuel.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                    required minlength="8">
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Le mot de passe doit contenir au moins 8 caractères.
                            </div>
                            <div class="invalid-feedback">
                                Le mot de passe doit contenir au moins 8 caractères.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required>
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Les mots de passe ne correspondent pas.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Modifier le mot de passe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières activités -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Dernières activités
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Adresse IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activites as $activite): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i:s', strtotime($activite['date_activite'])) ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = match ($activite['type_activite']) {
                                                'connexion' => 'bg-success',
                                                'deconnexion' => 'bg-danger',
                                                'modification' => 'bg-warning',
                                                default => 'bg-info'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= ucfirst(htmlspecialchars($activite['type_activite'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($activite['description']) ?></td>
                                        <td><?= htmlspecialchars($activite['ip_address']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Validation du formulaire de profil
        const profileForm = document.getElementById('profileForm');
        profileForm.addEventListener('submit', function (event) {
            if (!profileForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            profileForm.classList.add('was-validated');
        });

        // Validation du formulaire de mot de passe
        const passwordForm = document.getElementById('passwordForm');
        passwordForm.addEventListener('submit', function (event) {
            if (!passwordForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');

            if (newPassword.value !== confirmPassword.value) {
                event.preventDefault();
                confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas.");
            } else {
                confirmPassword.setCustomValidity("");
            }

            passwordForm.classList.add('was-validated');
        });

        // Vérification de la correspondance des mots de passe en temps réel
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');

        confirmPassword.addEventListener('input', function () {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas.");
            } else {
                confirmPassword.setCustomValidity("");
            }
        });

        // Formatage automatique du numéro de téléphone
        const telephone = document.getElementById('telephone');
        telephone.addEventListener('blur', function () {
            if (this.value) {
                // Supprimer tous les espaces et caractères non numériques
                let numbers = this.value.replace(/[^\d]/g, '');
                // Formater avec des espaces (XX XX XX XX XX)
                numbers = numbers.match(/.{1,2}/g);
                if (numbers) {
                    this.value = numbers.join(' ');
                }
            }
        });
    });

    // Fonction pour basculer la visibilité du mot de passe
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<?php require_once 'auth/includes/footer.php'; ?>