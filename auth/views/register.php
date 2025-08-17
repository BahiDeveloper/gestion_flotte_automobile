<?php
session_start();
$title = "Inscription";
require_once '../includes/header.php';

// Récupérer les données du formulaire en cas d'erreur
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>Inscription Utilisateur
                    </h3>
                </div>
                <div class="card-body">
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

                    <form id="registerForm" action="../controllers/auth_controller.php?action=register" method="POST"
                        class="needs-validation" novalidate>

                        <input type="hidden" name="role" value="utilisateur">

                        <!-- Informations personnelles -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-user me-2"></i>Nom
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom"
                                    value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>" required
                                    pattern="[A-Za-zÀ-ÿ\s-]+" placeholder="Entrez votre nom">
                                <div class="invalid-feedback">
                                    Veuillez entrer un nom valide.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">
                                    <i class="fas fa-user me-2"></i>Prénom
                                </label>
                                <input type="text" class="form-control" id="prenom" name="prenom"
                                    value="<?= htmlspecialchars($form_data['prenom'] ?? '') ?>" required
                                    pattern="[A-Za-zÀ-ÿ\s-]+" placeholder="Entrez votre prénom">
                                <div class="invalid-feedback">
                                    Veuillez entrer un prénom valide.
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email (optionnel)
                                </label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                    placeholder="exemple@email.com">
                                <div class="form-text">
                                    L'adresse email est optionnelle mais utile pour récupérer votre compte.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">
                                    <i class="fas fa-phone me-2"></i>Téléphone *
                                </label>
                                <input type="tel" class="form-control" id="telephone" name="telephone"
                                    value="<?= htmlspecialchars($form_data['telephone'] ?? '') ?>" pattern="[0-9\s]+"
                                    required placeholder="Ex: 07 12 34 56 78">
                                <div class="invalid-feedback">
                                    Un numéro de téléphone valide est requis.
                                </div>
                            </div>
                        </div>

                        <!-- Mot de passe -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mot de passe
                                </label>
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
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Confirmer le mot de passe
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required placeholder="Confirmez votre mot de passe">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Les mots de passe ne correspondent pas.
                                </div>
                            </div>
                        </div>

                        <!-- Conditions d'utilisation -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">
                                    conditions d'utilisation</a>
                            </label>
                            <div class="invalid-feedback">
                                Vous devez accepter les conditions d'utilisation.
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>S'inscrire
                            </button>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-outline-secondary m-1">
                                    <i class="fas fa-sign-in-alt me-2"></i>Déjà inscrit ? Se connecter
                                </a>
                                <a href="register_validateur.php" class="btn btn-outline-success m-1">
                                    <i class="fas fa-user-check me-2"></i>Inscription Validateur
                                </a>
                                <a href="register_super_admin.php" class="btn btn-outline-danger m-1">
                                    <i class="fas fa-user-shield me-2"></i>Inscription Administrateur
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal des conditions d'utilisation -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Conditions d'utilisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Conditions d'utilisation -->
                <h6>1. Utilisation du service</h6>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>

                <h6>2. Protection des données</h6>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Validation du formulaire
        const form = document.getElementById('registerForm');
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

        // Mettre à jour la validation des mots de passe en temps réel
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        [password, confirmPassword].forEach(input => {
            input.addEventListener('input', function () {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas.");
                } else {
                    confirmPassword.setCustomValidity("");
                }
            });
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

        // Validation du téléphone en temps réel
        const telephone = document.getElementById('telephone');
        telephone.addEventListener('input', function () {
            const phoneRegex = /^[0-9\s+]+$/;
            if (this.value && !phoneRegex.test(this.value)) {
                this.setCustomValidity("Veuillez entrer un numéro de téléphone valide.");
            } else {
                this.setCustomValidity("");
            }
        });

        // Formater automatiquement le numéro de téléphone
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

        // Validation du nom et prénom en temps réel
        const nameInputs = document.querySelectorAll('#nom, #prenom');
        nameInputs.forEach(input => {
            input.addEventListener('input', function () {
                const nameRegex = /^[A-Za-zÀ-ÿ\s-]+$/;
                if (!nameRegex.test(this.value)) {
                    this.setCustomValidity("Ce champ ne doit contenir que des lettres, espaces et tirets.");
                } else {
                    this.setCustomValidity("");
                }
            });
        });

        // Validation de l'email en temps réel
        const email = document.getElementById('email');
        email.addEventListener('input', function () {
            if (email.validity.typeMismatch) {
                email.setCustomValidity("Veuillez entrer une adresse email valide.");
            } else {
                email.setCustomValidity("");
            }
        });

        // Vérification des conditions d'utilisation
        const terms = document.getElementById('terms');
        terms.addEventListener('change', function () {
            if (!this.checked) {
                this.setCustomValidity("Vous devez accepter les conditions d'utilisation.");
            } else {
                this.setCustomValidity("");
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>