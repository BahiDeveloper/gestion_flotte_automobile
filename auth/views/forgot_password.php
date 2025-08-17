<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$title = "Mot de passe oublié";
require_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Réinitialisation de mot de passe
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

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <p class="text-muted text-center mb-4">
                        Choisissez comment vous souhaitez réinitialiser votre mot de passe.
                    </p>

                    <!-- Boutons de choix de méthode -->
                    <div class="text-center mb-4">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="resetMethod" id="btnEmail" value="email"
                                checked>
                            <label class="btn btn-outline-primary" for="btnEmail">
                                <i class="fas fa-envelope me-2"></i>Par email
                            </label>

                            <input type="radio" class="btn-check" name="resetMethod" id="btnPhone" value="phone">
                            <label class="btn btn-outline-primary" for="btnPhone">
                                <i class="fas fa-phone me-2"></i>Par téléphone
                            </label>
                        </div>
                    </div>

                    <!-- Formulaire Email -->
                    <form id="emailForm" action="../controllers/auth_controller.php?action=forgot-password"
                        method="POST" class="needs-validation">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Adresse email
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Entrez votre email">
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le lien de réinitialisation
                            </button>
                        </div>
                    </form>

                    <!-- Formulaire Téléphone -->
                    <form id="phoneForm" action="../controllers/auth_controller.php?action=forgot-password-phone"
                        method="POST" class="needs-validation" style="display: none;">
                        <div class="mb-3">
                            <label for="telephone" class="form-label">
                                <i class="fas fa-phone me-2"></i>Numéro de téléphone
                            </label>
                            <input type="tel" class="form-control" id="telephone" name="telephone"
                                placeholder="Ex: 07 12 34 56 78" pattern="[0-9\s]{10,}">
                            <div class="invalid-feedback">
                                Veuillez entrer un numéro de téléphone valide.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-mobile-alt me-2"></i>Envoyer le code par SMS
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
        const emailForm = document.getElementById('emailForm');
        const phoneForm = document.getElementById('phoneForm');
        const emailRadio = document.getElementById('btnEmail');
        const phoneRadio = document.getElementById('btnPhone');

        // Gestion du changement de méthode
        function toggleForms() {
            if (emailRadio.checked) {
                emailForm.style.display = 'block';
                phoneForm.style.display = 'none';
            } else {
                emailForm.style.display = 'none';
                phoneForm.style.display = 'block';
            }
        }

        emailRadio.addEventListener('change', toggleForms);
        phoneRadio.addEventListener('change', toggleForms);

        // Validation du formulaire email
        emailForm.addEventListener('submit', function (event) {
            if (!emailForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            emailForm.classList.add('was-validated');
        });

        // Validation et formatage du numéro de téléphone
        const telephone = document.getElementById('telephone');

        phoneForm.addEventListener('submit', function (event) {
            if (!phoneForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            phoneForm.classList.add('was-validated');
        });

        telephone.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, ''); // Garde uniquement les chiffres
            if (value.length > 0) {
                value = value.match(/.{1,2}/g).join(' '); // Ajoute un espace tous les 2 chiffres
            }
            this.value = value;

            // Validation personnalisée
            if (value.replace(/\s/g, '').length !== 10) {
                this.setCustomValidity('Le numéro doit contenir 10 chiffres');
            } else {
                this.setCustomValidity('');
            }
        });

        telephone.addEventListener('blur', function () {
            // Format final au format français (XX XX XX XX XX)
            let value = this.value.replace(/\D/g, '');
            if (value.length === 10) {
                this.value = value.replace(/(\d{2})(?=\d)/g, '$1 ');
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>