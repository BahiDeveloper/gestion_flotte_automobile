<?php
// Démarrer la session
session_start();

// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID du chauffeur est fourni dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Rediriger vers la page de gestion des chauffeurs avec un message d'erreur
    header("Location: gestion_chauffeurs.php?error=invalid_id");
    exit();
}

$id_chauffeur = intval($_GET['id']);

// Requête pour récupérer les informations du chauffeur
$query = "SELECT * FROM chauffeurs WHERE id_chauffeur = :id_chauffeur";
$stmt = $pdo->prepare($query);
$stmt->execute(['id_chauffeur' => $id_chauffeur]);
$chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le chauffeur existe
if (!$chauffeur) {
    // Rediriger vers la page de gestion des chauffeurs avec un message d'erreur
    header("Location: gestion_chauffeurs.php?error=not_found");
    exit();
}

// Récupérer les véhicules disponibles
$query_vehicules = "SELECT id_vehicule, marque, modele, immatriculation, statut FROM vehicules ORDER BY marque, modele";
$stmt_vehicules = $pdo->prepare($query_vehicules);
$stmt_vehicules->execute();
$vehicules = $stmt_vehicules->fetchAll(PDO::FETCH_ASSOC);

// Initialiser les variables pour les erreurs et les succès
$errors = [];
$success = false;

// Récupérer les données du formulaire si elles ont été sauvegardées en session
$form_data = [];
if (isset($_SESSION['form_data']) && !empty($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['form_data']); // Nettoyer la session
}

// Récupérer les erreurs spécifiques du formulaire si présentes
if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])) {
    $errors = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']); // Nettoyer la session
}

// Extraire les catégories de permis du chauffeur en un tableau
$type_permis_array = explode(',', $chauffeur['type_permis']);

// Initialiser le message d'alerte
$alert_message = '';
$alert_type = '';

// Traiter les succès ou erreurs transmis par URL
if (isset($_GET['success']) && $_GET['success'] === 'edit') {
    $alert_message = 'Les informations du chauffeur ont été mises à jour avec succès.';
    $alert_type = 'success';
} elseif (isset($_GET['error']) && $_GET['error'] === 'edit') {
    $alert_message = 'Une erreur est survenue lors de la modification du chauffeur.';
    $alert_type = 'error';
}
?>

<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->

<div class="container-fluid py-4">
    <!-- Barre de navigation avec bouton retour -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="gestion_chauffeurs.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste des chauffeurs
        </a>
        <h1 class="mb-0">
            <i class="fas fa-user-edit me-2"></i>Modifier le chauffeur
        </h1>
        <a href="chauffeur_details.php?id=<?= $id_chauffeur ?>" class="btn btn-outline-info">
            <i class="fas fa-eye me-2"></i>Voir détails
        </a>
    </div>

    <!-- Formulaire de modification -->
    <form id="editChauffeurForm" action="actions/chauffeurs/modifier_chauffeur.php" method="POST"
        enctype="multipart/form-data">
        <input type="hidden" name="id_chauffeur" value="<?= $id_chauffeur ?>">

        <div class="row g-4">
            <!-- Informations personnelles -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Informations personnelles</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <?php if (!empty($chauffeur['photo_profil'])): ?>
                                <div class="position-relative d-inline-block mb-3">
                                    <img src="uploads/chauffeurs/profils/<?= htmlspecialchars($chauffeur['photo_profil']) ?>"
                                        alt="Photo actuelle" class="img-thumbnail rounded-circle edit-profile-pic">
                                    <div class="profile-pic-overlay">
                                        <label for="photo_profil" class="btn btn-sm btn-light rounded-circle">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="photo_profil" class="form-label">Modifier la photo de profil</label>
                                <input type="file" name="photo_profil" id="photo_profil" class="form-control"
                                    accept="image/*">
                                <div class="form-text">Format accepté: jpg, jpeg, png. Max: 2MB. Laissez vide pour
                                    conserver l'image actuelle.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" class="form-control"
                                    value="<?= isset($form_data['nom']) ? htmlspecialchars($form_data['nom']) : htmlspecialchars($chauffeur['nom']) ?>"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="prenoms" class="form-label">Prénom <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="prenoms" id="prenoms" class="form-control"
                                    value="<?= isset($form_data['prenoms']) ? htmlspecialchars($form_data['prenoms']) : htmlspecialchars($chauffeur['prenoms']) ?>"
                                    required>
                            </div>
                        </div>



                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone <span
                                    class="text-danger">*</span></label>
                            <input type="tel" name="telephone" id="telephone" class="form-control"
                                value="<?= isset($form_data['telephone']) ? htmlspecialchars($form_data['telephone']) : htmlspecialchars($chauffeur['telephone']) ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger"></span></label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="<?= isset($form_data['email']) ? htmlspecialchars($form_data['email']) : htmlspecialchars($chauffeur['email']) ?>"
                               >
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                            <textarea name="adresse" id="adresse" class="form-control" rows="3"
                                required><?= isset($form_data['adresse']) ? htmlspecialchars($form_data['adresse']) : htmlspecialchars($chauffeur['adresse']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="specialisation" class="form-label">Spécialisation/Compétences</label>
                            <textarea name="specialisation" id="specialisation" class="form-control" rows="3"
                                placeholder="Ex: Transport longue distance, Manutention, Transport de personnes VIP..."><?= isset($form_data['specialisation']) ? htmlspecialchars($form_data['specialisation']) : htmlspecialchars($chauffeur['specialisation']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permis et Statut -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-id-card-alt me-2"></i>Permis de conduire</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <?php if (!empty($chauffeur['photo_permis'])): ?>
                                <div class="position-relative d-inline-block mb-3">
                                    <img src="uploads/chauffeurs/permis/<?= htmlspecialchars($chauffeur['photo_permis']) ?>"
                                        alt="Photo du permis actuel" class="img-thumbnail permis-pic">
                                    <div class="profile-pic-overlay">
                                        <label for="photo_permis" class="btn btn-sm btn-light rounded-circle">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="photo_permis" class="form-label">Modifier la photo du permis</label>
                                <input type="file" name="photo_permis" id="photo_permis" class="form-control"
                                    accept="image/*">
                                <div class="form-text">Format accepté: jpg, jpeg, png. Max: 2MB. Laissez vide pour
                                    conserver l'image actuelle.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="numero_permis" class="form-label">Numéro de permis <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="numero_permis" id="numero_permis" class="form-control"
                                value="<?= isset($form_data['numero_permis']) ? htmlspecialchars($form_data['numero_permis']) : htmlspecialchars($chauffeur['numero_permis']) ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="type_permis" class="form-label">Catégories <span
                                    class="text-danger">*</span></label>
                            <select name="type_permis[]" id="type_permis" class="form-select" multiple required>
                                <option value="A" <?= in_array('A', $type_permis_array) ? 'selected' : '' ?>>A - Motos
                                </option>
                                <option value="B" <?= in_array('B', $type_permis_array) ? 'selected' : '' ?>>B -
                                    Véhicules légers</option>
                                <option value="C" <?= in_array('C', $type_permis_array) ? 'selected' : '' ?>>C - Poids
                                    lourds</option>
                                <option value="D" <?= in_array('D', $type_permis_array) ? 'selected' : '' ?>>D -
                                    Transport en commun</option>
                                <option value="E" <?= in_array('E', $type_permis_array) ? 'selected' : '' ?>>E -
                                    Remorques</option>
                            </select>
                            <div class="form-text">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs
                                catégories</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_delivrance_permis" class="form-label">Date de délivrance <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="date_delivrance_permis" id="date_delivrance_permis"
                                    class="form-control"
                                    value="<?= isset($form_data['date_delivrance_permis']) ? $form_data['date_delivrance_permis'] : $chauffeur['date_delivrance_permis'] ?>"
                                    max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_expiration_permis" class="form-label">Date d'expiration</label>
                                <input type="date" name="date_expiration_permis" id="date_expiration_permis"
                                    class="form-control"
                                    value="<?= isset($form_data['date_expiration_permis']) ? $form_data['date_expiration_permis'] : $chauffeur['date_expiration_permis'] ?>">
                                <div class="form-text">Laisser vide pour les permis permanents (catégorie A uniquement)
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="statut_permis" class="form-label">Statut du permis <span
                                    class="text-danger">*</span></label>
                            <select name="statut_permis" id="statut_permis" class="form-select" required>
                                <option value="valide" <?= $chauffeur['statut_permis'] === 'valide' ? 'selected' : '' ?>>
                                    Valide</option>
                                <option value="expire" <?= $chauffeur['statut_permis'] === 'expire' ? 'selected' : '' ?>>
                                    Expiré</option>
                                <option value="permanant" <?= $chauffeur['statut_permis'] === 'permanant' ? 'selected' : '' ?>>Permanent</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Statut et véhicule -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Statut et assignation</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut du chauffeur <span
                                    class="text-danger">*</span></label>
                            <select name="statut" id="statut" class="form-select" required>
                                <option value="disponible" <?= $chauffeur['statut'] === 'disponible' ? 'selected' : '' ?>>
                                    Disponible</option>
                                <option value="en_course" <?= $chauffeur['statut'] === 'en_course' ? 'selected' : '' ?>>En
                                    course</option>
                                <option value="conge" <?= $chauffeur['statut'] === 'conge' ? 'selected' : '' ?>>En congé
                                </option>
                                <option value="indisponible" <?= $chauffeur['statut'] === 'indisponible' ? 'selected' : '' ?>>Indisponible</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="vehicule_attribue" class="form-label">Véhicule attribué</label>
                            <select name="vehicule_attribue" id="vehicule_attribue" class="form-select">
                                <option value="0">Aucun véhicule attribué</option>
                                <?php foreach ($vehicules as $vehicule): ?>
                                    <option value="<?= $vehicule['id_vehicule'] ?>"
                                        <?= $chauffeur['vehicule_attribue'] == $vehicule['id_vehicule'] ? 'selected' : '' ?>
                                        <?= $vehicule['statut'] !== 'disponible' && $chauffeur['vehicule_attribue'] != $vehicule['id_vehicule'] ? 'disabled' : '' ?>>
                                        <?= htmlspecialchars($vehicule['marque']) ?>
                                        <?= htmlspecialchars($vehicule['modele']) ?>
                                        (<?= htmlspecialchars($vehicule['immatriculation']) ?>)
                                        <?= $vehicule['statut'] !== 'disponible' && $chauffeur['vehicule_attribue'] != $vehicule['id_vehicule'] ? ' - ' . ucfirst($vehicule['statut']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Les véhicules non disponibles sont désactivés, sauf si déjà attribués
                                à ce chauffeur</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons de soumission -->
        <div class="d-flex justify-content-center mt-4 mb-4">
            <button type="submit" class="btn btn-success btn-lg me-2">
                <i class="fas fa-save me-2"></i>Enregistrer les modifications
            </button>
            <a href="gestion_chauffeurs.php" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-times me-2"></i>Annuler
            </a>
        </div>
    </form>
</div>

<!-- Message alert avec SweetAlert -->
<?php if (!empty($alert_message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: '<?= $alert_type === 'success' ? 'Succès!' : 'Erreur!' ?>',
                text: '<?= addslashes($alert_message) ?>',
                icon: '<?= $alert_type ?>',
                confirmButtonText: 'OK'
            });
        });
    </script>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Erreurs de validation',
                html: '<?= implode('<br>', array_map('addslashes', $errors)) ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    </script>
<?php endif; ?>

<!-- CSS personnalisé -->
<style>
    .edit-profile-pic {
        width: 150px;
        height: 150px;
        object-fit: cover;
    }

    .permis-pic {
        max-height: 200px;
        max-width: 100%;
    }

    .profile-pic-overlay {
        position: absolute;
        bottom: 0;
        right: 0;
        opacity: 0.8;
        transition: opacity 0.3s;
    }

    .profile-pic-overlay:hover {
        opacity: 1;
    }
</style>

<!-- JavaScript pour les fonctionnalités de la page -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gestion de la date d'expiration selon le type de permis sélectionné
        const typePermisSelect = document.getElementById('type_permis');
        const dateExpirationField = document.getElementById('date_expiration_permis');

        function checkPermisType() {
            const selectedOptions = Array.from(typePermisSelect.selectedOptions);
            const onlyA = selectedOptions.length === 1 && selectedOptions[0].value === 'A';

            if (onlyA) {
                dateExpirationField.value = '';
                dateExpirationField.disabled = true;
                dateExpirationField.removeAttribute('required');
                document.getElementById('statut_permis').value = 'permanant';
            } else {
                dateExpirationField.disabled = false;
                dateExpirationField.setAttribute('required', 'required');
            }
        }

        typePermisSelect.addEventListener('change', checkPermisType);

        // Vérifier l'état initial
        checkPermisType();

        // Validation du formulaire avant soumission
        document.getElementById('editChauffeurForm').addEventListener('submit', function (event) {
            let isValid = true;

            // Vérifier que les catégories de permis sont sélectionnées
            if (typePermisSelect.selectedOptions.length === 0) {
                isValid = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de validation',
                    text: 'Veuillez sélectionner au moins une catégorie de permis.'
                });
            }

            // Si la catégorie A n'est pas la seule sélectionnée, vérifier que la date d'expiration est renseignée
            const selectedOptions = Array.from(typePermisSelect.selectedOptions);
            const onlyA = selectedOptions.length === 1 && selectedOptions[0].value === 'A';

            if (!onlyA && !dateExpirationField.value) {
                isValid = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de validation',
                    text: 'La date d\'expiration du permis est obligatoire pour les permis autres que A seul.'
                });
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    });
</script>

<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer   -->