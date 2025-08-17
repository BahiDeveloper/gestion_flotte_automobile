<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../controllers/auth_controller.php';
AuthController::requireRole('administrateur');

// Récupérer l'utilisateur à modifier
$user = isset($_SESSION['edit_user']) ? $_SESSION['edit_user'] : null;

// Debug - à retirer après test
error_log('User data in session: ' . print_r($_SESSION['edit_user'], true));

if (!$user || !isset($user['id_utilisateur'])) {
    $_SESSION['error'] = "Utilisateur non trouvé.";
    header('Location: users_list.php');
    exit;
}

// Récupérer les données du formulaire en cas d'erreur
$form_data = $_SESSION['form_data'] ?? $user;
unset($_SESSION['form_data']);

$title = "Modifier un utilisateur";
require_once '../../includes/header.php';
?>



<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="fas fa-user-edit me-2"></i>Modifier un utilisateur</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="users_list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Erreurs :</strong>
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-user-edit me-2"></i>Modification de l'utilisateur
                #<?= htmlspecialchars($user['id_utilisateur']) ?>
            </h5>
        </div>
        <div class="card-body">
            <form id="editUserForm"
                action="../../controllers/user_controller.php?action=update&id=<?= $user['id_utilisateur'] ?>"
                method="POST" class="needs-validation" novalidate>

                <!-- Ajoutez un champ caché pour l'ID -->
                <input type="hidden" name="id_utilisateur" value="<?= $user['id_utilisateur'] ?>">

                <!-- Informations personnelles -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-primary"><i class="fas fa-user me-2"></i>Informations personnelles</h6>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                value="<?= htmlspecialchars($form_data['nom']) ?>" required pattern="[A-Za-zÀ-ÿ\s-]+"
                                placeholder="Entrez le nom">
                            <div class="invalid-feedback">
                                Le nom ne doit contenir que des lettres, espaces et tirets.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom"
                                value="<?= htmlspecialchars($form_data['prenom']) ?>" required pattern="[A-Za-zÀ-ÿ\s-]+"
                                placeholder="Entrez le prénom">
                            <div class="invalid-feedback">
                                Le prénom ne doit contenir que des lettres, espaces et tirets.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone"
                                value="<?= htmlspecialchars($form_data['telephone']) ?>" pattern="[0-9\s+]+"
                                placeholder="Ex: 07 12 34 56 78">
                            <div class="invalid-feedback">
                                Format de téléphone invalide.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations de connexion -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-primary"><i class="fas fa-lock me-2"></i>Informations de connexion</h6>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= htmlspecialchars($form_data['email']) ?>" required
                                placeholder="exemple@email.com">
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" minlength="8"
                                    placeholder="Laisser vide pour ne pas modifier">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Minimum 8 caractères. Laissez vide pour conserver l'ancien mot de passe.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" placeholder="Confirmez le nouveau mot de passe">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paramètres du compte -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-primary"><i class="fas fa-cog me-2"></i>Paramètres du compte</h6>
                        <hr>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required
                                <?= ($user['id_utilisateur'] == $_SESSION['id_utilisateur']) ? 'disabled' : '' ?>>
                                <option value="utilisateur" <?= $form_data['role'] === 'utilisateur' ? 'selected' : '' ?>>
                                    Utilisateur
                                </option>
                                <option value="validateur" <?= $form_data['role'] === 'validateur' ? 'selected' : '' ?>>
                                    Validateur
                                </option>

                                <option value="gestionnaire" <?= $form_data['role'] === 'gestionnaire' ? 'selected' : '' ?>>
                                    Gestionnaire
                                </option>
                                <option value="administrateur" <?= $form_data['role'] === 'administrateur' ? 'selected' : '' ?>>
                                    Administrateur
                                </option>
                            </select>
                            <?php if ($user['id_utilisateur'] == $_SESSION['id_utilisateur']): ?>
                                <div class="form-text text-warning">
                                    <i class="fas fa-info-circle"></i>
                                    Vous ne pouvez pas modifier votre propre rôle.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Statut du compte</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1"
                                    <?= $form_data['actif'] ? 'checked' : '' ?>
                                    <?= ($user['id_utilisateur'] == $_SESSION['id_utilisateur']) ? 'disabled' : '' ?>>
                                <label class="form-check-label" for="actif">Compte actif</label>
                            </div>
                            <?php if ($user['id_utilisateur'] == $_SESSION['id_utilisateur']): ?>
                                <div class="form-text text-warning">
                                    <i class="fas fa-info-circle"></i>
                                    Vous ne pouvez pas désactiver votre propre compte.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Dernière connexion</label>
                            <p class="form-control-static">
                                <?php if (isset($user['derniere_connexion'])): ?>
                                    <?= date('d/m/Y H:i', strtotime($user['derniere_connexion'])) ?>
                                <?php else: ?>
                                    Jamais connecté
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-end">
                        <button type="reset" class="btn btn-secondary me-2">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Validation du formulaire
        const form = document.getElementById('editUserForm');
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Vérifier les mots de passe si renseignés
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            if (password.value || confirmPassword.value) {
                if (password.value !== confirmPassword.value) {
                    event.preventDefault();
                    confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas.");
                } else {
                    confirmPassword.setCustomValidity("");
                }
            }

            form.classList.add('was-validated');
        });

        // Toggle password visibility
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);

            button.addEventListener('click', function () {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }

        togglePasswordVisibility('password', 'togglePassword');
        togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');

        // Validation en temps réel
        const inputs = {
            telephone: {
                element: document.getElementById('telephone'),
                regex: /^[0-9\s+]+$/,
                message: "Format de téléphone invalide."
            },
            email: {
                element: document.getElementById('email'),
                regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: "Format d'email invalide."
            },
            nom: {
                element: document.getElementById('nom'),
                regex: /^[A-Za-zÀ-ÿ\s-]+$/,
                message: "Le nom ne doit contenir que des lettres, espaces et tirets."
            },
            prenom: {
                element: document.getElementById('prenom'),
                regex: /^[A-Za-zÀ-ÿ\s-]+$/,
                message: "Le prénom ne doit contenir que des lettres, espaces et tirets."
            }
        };

        // Appliquer les validations
        Object.values(inputs).forEach(input => {
            if (input.element) {
                input.element.addEventListener('input', function () {
                    if (this.value && !input.regex.test(this.value)) {
                        this.setCustomValidity(input.message);
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });

        // Formatage automatique du téléphone
        const telephone = document.getElementById('telephone');
        if (telephone) {
            telephone.addEventListener('blur', function () {
                if (this.value) {
                    let numbers = this.value.replace(/[^\d]/g, '');
                    numbers = numbers.match(/.{1,2}/g);
                    if (numbers) {
                        this.value = numbers.join(' ');
                    }
                }
            });
        }
    });
</script>

<?php require_once '../../includes/footer.php'; ?>