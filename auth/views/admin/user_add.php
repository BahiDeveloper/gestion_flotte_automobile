<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../controllers/auth_controller.php';
AuthController::requireRole('administrateur');

$title = "Ajouter un utilisateur";
require_once '../../includes/header.php';

// Récupérer les données du formulaire en cas d'erreur
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur</h1>
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
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Informations de l'utilisateur</h5>
        </div>
        <div class="card-body">
            <form id="addUserForm" action="../../controllers/user_controller.php?action=store" method="POST"
                class="needs-validation" novalidate>
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
                                value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>" required
                                pattern="[A-Za-zÀ-ÿ\s-]+" placeholder="Entrez le nom">
                            <div class="invalid-feedback">
                                Le nom ne doit contenir que des lettres, espaces et tirets.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom"
                                value="<?= htmlspecialchars($form_data['prenom'] ?? '') ?>" required
                                pattern="[A-Za-zÀ-ÿ\s-]+" placeholder="Entrez le prénom">
                            <div class="invalid-feedback">
                                Le prénom ne doit contenir que des lettres, espaces et tirets.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone"
                                value="<?= htmlspecialchars($form_data['telephone'] ?? '') ?>" pattern="[0-9\s+]+"
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
                                value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required
                                placeholder="exemple@email.com">
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="password" class="form-label">Mot de passe <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required
                                    minlength="8" placeholder="Minimum 8 caractères">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Le mot de passe doit contenir au moins 8 caractères.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required placeholder="Confirmez le mot de passe">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Les mots de passe ne correspondent pas.
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
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Sélectionnez un rôle</option>
                                <option value="utilisateur" <?= (isset($form_data['role']) && $form_data['role'] === 'utilisateur') ? 'selected' : '' ?>>Utilisateur</option>
                                <option value="gestionnaire" <?= (isset($form_data['role']) && $form_data['role'] === 'gestionnaire') ? 'selected' : '' ?>>Gestionnaire</option>
                                <option value="validateur" <?= (isset($form_data['role']) && $form_data['role'] === 'validateur') ? 'selected' : '' ?>>Validateur</option>
                                <option value="administrateur" <?= (isset($form_data['role']) && $form_data['role'] === 'administrateur') ? 'selected' : '' ?>>Administrateur</option>
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un rôle.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="statut" class="form-label">Statut initial</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="statut" name="actif" value="1"
                                    checked>
                                <label class="form-check-label" for="statut">Compte actif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-end">
                        <button type="reset" class="btn btn-secondary me-2">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer
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
        const form = document.getElementById('addUserForm');
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Vérifier si les mots de passe correspondent
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            if (password.value !== confirmPassword.value) {
                event.preventDefault();
                confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas.");
            } else {
                confirmPassword.setCustomValidity("");
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

        // Validation en temps réel du téléphone
        const telephone = document.getElementById('telephone');
        telephone.addEventListener('input', function () {
            const phoneRegex = /^[0-9\s+]+$/;
            if (this.value && !phoneRegex.test(this.value)) {
                this.setCustomValidity("Format de téléphone invalide.");
            } else {
                this.setCustomValidity("");
            }
        });

        // Formatage automatique du téléphone
        telephone.addEventListener('blur', function () {
            if (this.value) {
                let numbers = this.value.replace(/[^\d]/g, '');
                numbers = numbers.match(/.{1,2}/g);
                if (numbers) {
                    this.value = numbers.join(' ');
                }
            }
        });

        // Vérification de l'email
        const email = document.getElementById('email');
        email.addEventListener('input', function () {
            if (email.validity.typeMismatch) {
                email.setCustomValidity("Veuillez entrer une adresse email valide.");
            } else {
                email.setCustomValidity("");
            }
        });

        // Validation des champs nom et prénom
        ['nom', 'prenom'].forEach(id => {
            const input = document.getElementById(id);
            input.addEventListener('input', function () {
                const nameRegex = /^[A-Za-zÀ-ÿ\s-]+$/;
                if (!nameRegex.test(this.value)) {
                    this.setCustomValidity("Ce champ ne doit contenir que des lettres, espaces et tirets.");
                } else {
                    this.setCustomValidity("");
                }
            });
        });
    });
</script>

<?php require_once '../../includes/footer.php'; ?>