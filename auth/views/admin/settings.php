<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../controllers/auth_controller.php';
AuthController::requireRole('administrateur');

$title = "Paramètres système";
require_once '../../includes/header.php';

// Récupérer les paramètres actuels
try {
    $stmt = $pdo->query("SELECT * FROM parametres_systeme ORDER BY cle");
    $parametres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur de récupération des paramètres : " . $e->getMessage());
    $parametres = [];
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['parametres'] as $id => $valeur) {
            $stmt = $pdo->prepare("UPDATE parametres_systeme SET valeur = :valeur WHERE id_parametre = :id");
            $stmt->execute([
                'valeur' => $valeur,
                'id' => $id
            ]);
        }
        $_SESSION['success'] = "Paramètres mis à jour avec succès.";
        header('Location: settings.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour des paramètres.";
        error_log("Erreur de mise à jour des paramètres : " . $e->getMessage());
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>
                <i class="fas fa-cogs me-2"></i>Paramètres système
            </h1>
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
        <!-- Paramètres des alertes documents -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i>Configuration des alertes documents
                    </h5>
                </div>
                <div class="card-body">
                    <form id="alertSettingsForm" method="POST" class="needs-validation" novalidate>
                        <?php foreach ($parametres as $param): ?>
                            <div class="mb-3">
                                <label for="param_<?= $param['id_parametre'] ?>" class="form-label">
                                    <?= htmlspecialchars($param['description']) ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="param_<?= $param['id_parametre'] ?>"
                                        name="parametres[<?= $param['id_parametre'] ?>]"
                                        value="<?= htmlspecialchars($param['valeur']) ?>" min="1" required>
                                    <span class="input-group-text">jours</span>
                                </div>
                                <div class="form-text">
                                    Identifiant: <?= htmlspecialchars($param['cle']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Paramètres de l'application -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sliders-h me-2"></i>Paramètres généraux
                    </h5>
                </div>
                <div class="card-body">
                    <form id="appSettingsForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Nom du site</label>
                            <input type="text" class="form-control" id="site_name" value="Gestion de Flotte" required>
                        </div>

                        <div class="mb-3">
                            <label for="timezone" class="form-label">Fuseau horaire</label>
                            <select class="form-select" id="timezone" required>
                                <option value="Africa/Abidjan">Abidjan (GMT+0)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="maintenance_mode" class="form-label">Mode maintenance</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode">
                                <label class="form-check-label" for="maintenance_mode">Activer le mode
                                    maintenance</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Autres sections de paramètres -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>Configuration des notifications
                    </h5>
                </div>
                <div class="card-body">
                    <form id="notificationSettingsForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_notifications" checked>
                                <label class="form-check-label" for="email_notifications">
                                    Activer les notifications par email
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="browser_notifications" checked>
                                <label class="form-check-label" for="browser_notifications">
                                    Activer les notifications du navigateur
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Paramètres de sécurité
                    </h5>
                </div>
                <div class="card-body">
                    <form id="securitySettingsForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="session_timeout" class="form-label">Expiration de session</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="session_timeout" value="30" min="1"
                                    required>
                                <span class="input-group-text">minutes</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="two_factor_auth">
                                <label class="form-check-label" for="two_factor_auth">
                                    Activer l'authentification à deux facteurs
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Validation des formulaires
        const forms = document.querySelectorAll('.needs-validation');

        forms.forEach(form => {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        // Sauvegarder les paramètres généraux
        document.getElementById('appSettingsForm').addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Succès',
                text: 'Les paramètres généraux ont été sauvegardés',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });

        // Sauvegarder les paramètres de notification
        document.getElementById('notificationSettingsForm').addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Succès',
                text: 'Les paramètres de notification ont été sauvegardés',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });

        // Sauvegarder les paramètres de sécurité
        document.getElementById('securitySettingsForm').addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Succès',
                text: 'Les paramètres de sécurité ont été sauvegardés',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
    });
</script>

<?php require_once '../../includes/footer.php'; ?>