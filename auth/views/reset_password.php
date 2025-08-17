<?php
session_start();
$title = "Réinitialisation de mot de passe";
require_once '../includes/header.php';

// Vérifier la présence du token
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
if (!$token) {
    $_SESSION['error'] = "Lien de réinitialisation invalide.";
    header('Location: login.php');
    exit;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-lock me-2"></i>Réinitialisation de mot de passe
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <p class="text-muted text-center mb-4">
                        Saisissez votre nouveau mot de passe. Il doit contenir au moins 8 caractères.
                    </p>

                    <form id="resetPasswordForm" action="../controllers/auth_controller.php?action=reset-password"
                        method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Nouveau mot de passe
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

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirmer le mot de passe
                            </label>
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

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Réinitialiser le mot de passe
                            </button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        <p class="mb-0">
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Validation du formulaire
        const form = document.getElementById('resetPasswordForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Validation des mots de passe
            if (password.value !== confirmPassword.value) {
                event.preventDefault();
                confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas.");
            } else {
                confirmPassword.setCustomValidity("");
            }

            form.classList.add('was-validated');
        });

        // Validation des mots de passe en temps réel
        confirmPassword.addEventListener('input', function () {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas.");
            } else {
                confirmPassword.setCustomValidity("");
            }
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
    });
</script>

<?php require_once '../includes/footer.php'; ?>