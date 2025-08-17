<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = "Inscription Validateur";
require_once '../includes/header.php';

// Récupérer les données du formulaire en cas d'erreur
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-check me-2"></i>Inscription Validateur
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette page est réservée à l'inscription des validateurs.
                        Pour une inscription utilisateur standard, veuillez utiliser le
                        <a href="register.php" class="alert-link">formulaire d'inscription normal</a>.
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

                    <form id="registerValidatorForm" action="../controllers/auth_controller.php?action=register"
                        method="POST" class="needs-validation" novalidate>

                        <input type="hidden" name="role" value="validateur">

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
                                        minlength="12" placeholder="Minimum 12 caractères">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Le mot de passe doit contenir au moins 12 caractères, incluant majuscules,
                                    minuscules, chiffres et caractères spéciaux.
                                </div>
                                <div class="invalid-feedback">
                                    Le mot de passe doit respecter les critères de sécurité.
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
                                    conditions d'utilisation</a> et je m'engage à respecter les politiques de sécurité
                            </label>
                            <div class="invalid-feedback">
                                Vous devez accepter les conditions d'utilisation.
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-check me-2"></i>Créer le compte validateur
                            </button>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-outline-secondary m-1">
                                    <i class="fas fa-sign-in-alt me-2"></i>Déjà inscrit ? Se connecter
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
                <h5 class="modal-title" id="termsModalLabel">Conditions d'utilisation - Compte Validateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Responsabilités du validateur</h6>
                <p>En tant que validateur, vous aurez la responsabilité d'approuver ou de rejeter les demandes...</p>

                <h6>2. Confidentialité des données</h6>
                <p>Vous vous engagez à maintenir la confidentialité des informations auxquelles vous avez accès...</p>

                <h6>3. Processus de validation</h6>
                <p>Vous devez suivre les procédures établies pour la validation des demandes...</p>
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
        const form = document.getElementById('registerValidatorForm');

        // Validation du mot de passe
        function validatePassword(password) {
            const minLength = password.length >= 12;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            return minLength && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar;
        }

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            // Validation du mot de passe
            if (!validatePassword(password.value)) {
                event.preventDefault();
                password.setCustomValidity("Le mot de passe doit contenir au moins 12 caractères, incluant majuscules, minuscules, chiffres et caractères spéciaux.");
            } else {
                password.setCustomValidity("");
            }

            // Vérification de la correspondance des mots de passe
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
                regex: /^[a-zA-ZÀ-ÿ\s-]+$/,
                message: "Le nom ne doit contenir que des lettres, espaces et tirets."
            },
            prenom: {
                element: document.getElementById('prenom'),
                regex: /^[a-zA-ZÀ-ÿ\s-]+$/,
                message: "Le prénom ne doit contenir que des lettres, espaces et tirets."
            }
        };

        // Ajouter la validation en temps réel pour chaque input
        Object.entries(inputs).forEach(([key, { element, regex, message }]) => {
            if (element) {
                element.addEventListener('input', function () {
                    if (this.value && !regex.test(this.value)) {
                        this.setCustomValidity(message);
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });

        // Formatage automatique du numéro de téléphone
        const telephone = document.getElementById('telephone');
        if (telephone) {
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
        }

        // Validation du mot de passe en temps réel
        const password = document.getElementById('password');
        if (password) {
            password.addEventListener('input', function () {
                let validationMessage = [];

                if (this.value.length < 12) {
                    validationMessage.push("Au moins 12 caractères");
                }
                if (!/[A-Z]/.test(this.value)) {
                    validationMessage.push("Au moins une majuscule");
                }
                if (!/[a-z]/.test(this.value)) {
                    validationMessage.push("Au moins une minuscule");
                }
                if (!/[0-9]/.test(this.value)) {
                    validationMessage.push("Au moins un chiffre");
                }
                if (!/[!@#$%^&*(),.?":{}|<>]/.test(this.value)) {
                    validationMessage.push("Au moins un caractère spécial");
                }

                if (validationMessage.length > 0) {
                    this.setCustomValidity("Requis : " + validationMessage.join(", "));
                } else {
                    this.setCustomValidity("");
                }
            });
        }

        // Validation des conditions d'utilisation
        const terms = document.getElementById('terms');
        if (terms) {
            terms.addEventListener('change', function () {
                if (!this.checked) {
                    this.setCustomValidity("Vous devez accepter les conditions d'utilisation.");
                } else {
                    this.setCustomValidity("");
                }
            });
        }

        // Afficher les messages de validation personnalisés
        const formElements = form.querySelectorAll('input, select');
        formElements.forEach(input => {
            input.addEventListener('invalid', function (event) {
                if (!event.target.validity.valid) {
                    const fb = event.target.nextElementSibling;
                    if (fb && fb.classList.contains('invalid-feedback')) {
                        fb.textContent = event.target.validationMessage;
                    }
                }
            });
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>