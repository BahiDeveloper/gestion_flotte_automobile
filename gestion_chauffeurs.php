<?php
// Démarrer la session avant toute sortie HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Inclure le fichier de configuration de la base de données
    include_once("database" . DIRECTORY_SEPARATOR . "config.php");
} catch (Exception $e) {
    error_log("Erreur d'inclusion de header.php: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Une erreur est survenue lors du chargement de la page. Veuillez réessayer ultérieurement.</div>";
    // Continuer l'exécution ou sortir proprement
}

// Requête pour récupérer tous les chauffeurs avec leur statut
try {
    $query = "SELECT c.id_chauffeur as id, c.nom, c.prenoms as prenom, c.photo_profil, c.type_permis as categorie_permis,
            CASE 
                WHEN c.statut = 'disponible' THEN 'Disponible'
                WHEN c.statut = 'en_course' THEN 'Occupé' 
                WHEN c.statut = 'conge' THEN 'En congé'
                WHEN c.statut = 'indisponible' THEN 'Hors ligne'
            END as disponibilite
            FROM chauffeurs c
            ORDER BY c.nom, c.prenoms";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $chauffeurs_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log l'erreur
    error_log("Erreur SQL (chauffeurs): " . $e->getMessage());
    // Initialiser un tableau vide en cas d'erreur
    $chauffeurs_all = [];
}

// Requête pour récupérer l'historique des activités des chauffeurs
try {
    $query_historique = "SELECT c.nom, c.prenoms, r.date_depart, r.date_retour_effective, 
                      v.marque, v.modele, v.immatriculation,
                      (COALESCE(r.km_retour, 0) - COALESCE(r.km_depart, 0)) as distance_parcourue,
                      r.statut as statut_reservation
                      FROM reservations_vehicules r
                      JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
                      JOIN vehicules v ON r.id_vehicule = v.id_vehicule
                      WHERE r.statut = 'terminee'
                      ORDER BY r.date_retour_effective DESC";
    $stmt_historique = $pdo->prepare($query_historique);
    $stmt_historique->execute();
    $historiques = $stmt_historique->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log l'erreur
    error_log("Erreur SQL (historique): " . $e->getMessage());
    // Initialiser un tableau vide en cas d'erreur
    $historiques = [];
}

// Gestion des erreurs pour l'ajout/modification
$errors = [];
$error_message = [];

// Gérer les messages d'alerte (succès ou erreur)
$alert_message = '';
$alert_type = '';

// Traiter les succès
if (isset($_GET['success'])) {
    $success_type = $_GET['success'];

    switch ($success_type) {
        case 'add':
            $alert_message = 'Le chauffeur a été ajouté avec succès.';
            $alert_type = 'success';
            break;
        case 'edit':
            $alert_message = 'Les informations du chauffeur ont été mises à jour avec succès.';
            $alert_type = 'success';
            break;
        case 'delete':
            $alert_message = 'Le chauffeur a été supprimé avec succès.';
            $alert_type = 'success';
            break;
    }
}

// Traiter les erreurs
if (isset($_GET['error'])) {
    $error_type = $_GET['error'];
    $error_tab = isset($_GET['tab']) ? $_GET['tab'] : '';

    switch ($error_type) {
        case 'add':
            $alert_message = 'Une erreur est survenue lors de l\'ajout du chauffeur.';
            $alert_type = 'error';
            // Activer l'onglet approprié via JavaScript
            echo "<script>document.addEventListener('DOMContentLoaded', function() { 
                    document.querySelector('#profile-tab').click();
                });</script>";
            break;
        case 'edit':
            $alert_message = 'Une erreur est survenue lors de la modification du chauffeur.';
            $alert_type = 'error';
            break;
        case 'delete':
            $alert_message = 'Une erreur est survenue lors de la suppression du chauffeur.';
            $alert_type = 'error';
            break;
    }
}

// Afficher les erreurs spécifiques du formulaire si présentes
$form_errors = [];
if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])) {
    $form_errors = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']); // Nettoyer la session
}

// Récupérer les données du formulaire si elles ont été sauvegardées en session
$form_data = [];
if (isset($_SESSION['form_data']) && !empty($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['form_data']); // Nettoyer la session
}
?>

<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php"); ?>
<!--end header  -->

<?php
// Assurez-vous que l'objet $roleAccess est disponible
try {
    if (!isset($roleAccess)) {
        include_once("includes" . DIRECTORY_SEPARATOR . "RoleAccess.php");
        if (isset($_SESSION['role'])) {
            $roleAccess = new RoleAccess($_SESSION['role']);
        } else {
            // Redirection ou attribution d'un rôle par défaut
            header('Location: auth/views/login.php');
            exit;
        }
    }
} catch (Exception $e) {
    error_log("Erreur avec RoleAccess: " . $e->getMessage());
    // Définir un comportement par défaut
    class FallbackRoleAccess {
        public function hasPermission($permission) {
            return false;
        }
        public function getRolePermissions() {
            return [];
        }
    }
    $roleAccess = new FallbackRoleAccess();
}
?>

<div class="container-fluid py-4">
    <h1 class="text-center mb-4" style="color: #2c3e50; font-weight: 700;">
        <i class="fas fa-user-tie me-2"></i>Gestion des chauffeurs
    </h1>

    <!-- Onglets de navigation -->
    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button"
                role="tab" aria-controls="list" aria-selected="true">
                <i class="fas fa-users me-2"></i>Liste des chauffeurs
            </button>
        </li>
        <?php if ($roleAccess->hasPermission('historique')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button"
                role="tab" aria-controls="history" aria-selected="false">
                <i class="fas fa-history me-2"></i>Historique
            </button>
        </li>
        <?php endif; ?>
        <?php if ($roleAccess->hasPermission('form')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button"
                role="tab" aria-controls="profile" aria-selected="false">
                <i class="fas fa-plus me-2"></i>Ajouter un chauffeur
            </button>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Contenu des onglets -->
    <div class="tab-content" id="myTabContent">
        
        <!-- Liste des chauffeurs -->
        <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Liste des chauffeurs</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" id="btnExportChauffeurs">
                                <i class="fas fa-file-export me-1"></i>Exporter
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnPrintChauffeurs">
                                <i class="fas fa-print me-1"></i>Imprimer
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="chauffeursTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Catégorie permis</th>
                                    <th>Disponibilité</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($chauffeurs_all as $chauffeur): ?>
                                    <tr>
                                        <!-- Colonne Photo -->
                                        <td class="text-center">
                                            <?php if (!empty($chauffeur['photo_profil'])): ?>
                                                <div class="photo_profil_chauffeurs">
                                                    <img src="uploads/chauffeurs/profils/<?= htmlspecialchars($chauffeur['photo_profil']) ?>"
                                                        alt="Photo de profil" class="img-fluid">
                                                </div>
                                            <?php else: ?>
                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-circle"
                                                    style="width: 45px; height: 45px; font-size: 18px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($chauffeur['nom']) ?></td>
                                        <td><?= htmlspecialchars($chauffeur['prenom']) ?></td>
                                        <td><?= htmlspecialchars($chauffeur['categorie_permis']) ?></td>
                                        <td>
                                            <span class="badge <?=
                                                $chauffeur['disponibilite'] === 'Disponible' ? 'bg-success' :
                                                ($chauffeur['disponibilite'] === 'Occupé' ? 'bg-warning' :
                                                    ($chauffeur['disponibilite'] === 'En congé' ? 'bg-info' : 'bg-secondary'))
                                                ?>">
                                                <?= htmlspecialchars($chauffeur['disponibilite']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Boutons d'action -->
                                            <div class="btn-group" role="group">
                                                <!-- Voir détails (accessible à tous) -->
                                                <a href="chauffeur_details.php?id=<?= $chauffeur['id'] ?>"
                                                    class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
                                                <!-- Modifier -->
                                                <a href="edit_chauffeur.php?id=<?= $chauffeur['id'] ?>"
                                                    class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($roleAccess->hasPermission('deleteHistorique')): ?>
                                                <!-- Supprimer avec SweetAlert -->
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmDeleteChauffeur(<?= $chauffeur['id'] ?>, '<?= htmlspecialchars($chauffeur['nom']) ?> <?= htmlspecialchars($chauffeur['prenom']) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($roleAccess->hasPermission('historique')): ?>
        <!-- Historique des chauffeurs -->
        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des chauffeurs</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" id="btnExportHistorique">
                                <i class="fas fa-file-export me-1"></i>Exporter
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnPrintHistorique">
                                <i class="fas fa-print me-1"></i>Imprimer
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="historiqueTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Chauffeur</th>
                                    <th>Véhicule</th>
                                    <th>Date départ</th>
                                    <th>Date retour</th>
                                    <th>Distance (km)</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historiques as $historique): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($historique['nom']) ?>
                                            <?= htmlspecialchars($historique['prenoms']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($historique['marque']) ?>
                                            <?= htmlspecialchars($historique['modele']) ?>
                                            <small
                                                class="text-muted">(<?= htmlspecialchars($historique['immatriculation']) ?>)</small>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($historique['date_depart'])) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($historique['date_retour_effective'])) ?></td>
                                        <td><?= number_format($historique['distance_parcourue'], 0, ',', ' ') ?> km</td>
                                        <td>
                                            <span class="badge bg-success">Terminée</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (count($historiques) == 0): ?>
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <p class="m-0">Aucun historique disponible</p>
                                </div>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($roleAccess->hasPermission('form')): ?>
        <!-- Ajouter un chauffeur -->
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Ajouter un chauffeur</h5>
                </div>
                <div class="card-body">
                    <form id="addChauffeurForm" action="actions/chauffeurs/ajouter_chauffeur.php" method="POST"
                        enctype="multipart/form-data">
                        <div class="row g-4">
                            <!-- Informations personnelles -->
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Informations personnelles
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="nom" class="form-label">Nom <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="nom" id="nom" class="form-control"
                                                value="<?= isset($form_data['nom']) ? htmlspecialchars($form_data['nom']) : '' ?>"
                                                required>
                                            <div class="invalid-feedback">Veuillez saisir un nom</div>
                                        </div>
                                        <!-- Reste du formulaire inchangé -->
                                        <div class="mb-3">
                                            <label for="prenom" class="form-label">Prénom <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="prenom" id="prenom" class="form-control" 
                                                    value="<?= isset($form_data['prenom']) ? htmlspecialchars($form_data['prenom']) : '' ?>"
                                                    required>
                                            <div class="invalid-feedback">Veuillez saisir un prénom</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="telephone" class="form-label">Téléphone <span
                                                    class="text-danger">*</span></label>
                                            <input type="tel" name="telephone" id="telephone" class="form-control"
                                                value="<?= isset($form_data['telephone']) ? htmlspecialchars($form_data['telephone']) : '' ?>"
                                                maxlength="14" required>
                                            <div class="invalid-feedback">Veuillez saisir un numéro de téléphone valide
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</span></label>
                                            <input type="email" id="email" name="email" class="form-control"
                                                value="<?= isset($form_data['email']) ? htmlspecialchars($form_data['email']) : '' ?>"
                                                >
                                            <div class="invalid-feedback">Veuillez saisir une adresse email valide</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="adresse" class="form-label">Adresse <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="adresse" id="adresse" class="form-control"
                                                value="<?= isset($form_data['adresse']) ? htmlspecialchars($form_data['adresse']) : '' ?>"
                                                required>
                                            <div class="invalid-feedback">Veuillez saisir une adresse</div>
                                        </div>
                                
                                        <div class="mb-3">
                                            <label for="photo_profil" class="form-label">Photo de profil <span
                                                    class="text-danger">*</span></label>
                                            <input type="file" name="photo_profil" id="photo_profil"
                                                class="form-control" accept="image/*" required>
                                            <div class="form-text">Format accepté: jpg, jpeg, png. Max: 2MB</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Permis de conduire -->
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-id-card-alt me-2"></i>Permis de conduire</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="numero_permis" class="form-label">Numéro de permis <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="numero_permis" id="numero_permis"
                                                class="form-control"
                                                value="<?= isset($form_data['numero_permis']) ? htmlspecialchars($form_data['numero_permis']) : '' ?>"
                                                required>
                                            <div class="invalid-feedback">Veuillez saisir un numéro de permis valide
                                            </div>
                                        </div>
                                        <div class="mb-3">
    <label for="categories_permis" class="form-label">Catégories <span class="text-danger">*</span></label>
    <select name="type_permis[]" id="categories_permis" class="form-select" multiple required>
    <option value="A" <?= (isset($form_data['type_permis']) && is_array($form_data['type_permis']) && in_array('A', $form_data['type_permis'], true)) ? 'selected' : '' ?>>A - Motos</option>
    <option value="B" <?= (isset($form_data['type_permis']) && is_array($form_data['type_permis']) && in_array('B', $form_data['type_permis'], true)) ? 'selected' : '' ?>>B - Véhicules légers</option>
    <option value="C" <?= (isset($form_data['type_permis']) && is_array($form_data['type_permis']) && in_array('C', $form_data['type_permis'], true)) ? 'selected' : '' ?>>C - Poids lourds</option>
    <option value="D" <?= (isset($form_data['type_permis']) && is_array($form_data['type_permis']) && in_array('D', $form_data['type_permis'], true)) ? 'selected' : '' ?>>D - Transport en commun</option>
    <option value="E" <?= (isset($form_data['type_permis']) && is_array($form_data['type_permis']) && in_array('E', $form_data['type_permis'], true)) ? 'selected' : '' ?>>E - Remorques</option>
    <!-- autres options -->
</select>
    
    <div class="form-text">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs catégories</div>
    <div class="invalid-feedback">Veuillez sélectionner au moins une catégorie</div>
</div>
                                        <div class="mb-3">
                                            <label for="date_delivrance_permis" class="form-label">Date de délivrance
                                                <span class="text-danger">*</span></label>
                                            <input type="date" name="date_delivrance_permis" id="date_delivrance_permis"
                                                class="form-control" max="<?= date('Y-m-d') ?>"
                                                value="<?= isset($form_data['date_delivrance_permis']) ? $form_data['date_delivrance_permis'] : '' ?>"
                                                required>
                                            <div class="invalid-feedback">La date de délivrance ne peut pas être dans le
                                                futur</div>
                                        </div>
                                        <div class="mb-3" id="date_expiration_field">
                                            <label for="date_expiration_permis" class="form-label">Date
                                                d'expiration</label>
                                            <input type="date" name="date_expiration_permis" id="date_expiration_permis"
                                                class="form-control" min="<?= date('Y-m-d') ?>"
                                                value="<?= isset($form_data['date_expiration_permis']) ? $form_data['date_expiration_permis'] : '' ?>">
                                            <div class="form-text">Laisser vide pour les permis permanents (catégorie A)
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="photo_permis" class="form-label">Photo du permis <span
                                                    class="text-danger">*</span></label>
                                            <input type="file" name="photo_permis" id="photo_permis"
                                                class="form-control" accept="image/*" required>
                                            <div class="form-text">Format accepté: jpg, jpeg, png. Max: 2MB</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="specialisation"
                                                class="form-label">Spécialisation/Compétences</label>
                                            <textarea name="specialisation" id="specialisation" class="form-control"
                                                rows="3"
                                                placeholder="Ex: Transport longue distance, Manutention, Transport de personnes VIP..."><?= isset($form_data['specialisation']) ? htmlspecialchars($form_data['specialisation']) : '' ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="statut" class="form-label">Statut initial</label>
                                            <select name="statut" id="statut" class="form-select">
                                                <option value="disponible" <?= (!isset($form_data['statut']) || $form_data['statut'] === 'disponible') ? 'selected' : '' ?>>Disponible
                                                </option>
                                                <option value="indisponible" <?= (isset($form_data['statut']) && $form_data['statut'] === 'indisponible') ? 'selected' : '' ?>>
                                                    Indisponible</option>
                                                <option value="conge" <?= (isset($form_data['statut']) && $form_data['statut'] === 'conge') ? 'selected' : '' ?>>En congé
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons de soumission -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-lg ms-2">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmation de suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteModalBody">
                Êtes-vous sûr de vouloir supprimer ce chauffeur?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Start Message alert avec sweet alert   -->
<?php if (!empty($alert_message)): ?>
    <!-- Script pour afficher l'alerte avec SweetAlert2 -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: <?= json_encode($alert_type === 'success' ? 'Succès!' : 'Erreur!') ?>,
            text: <?= json_encode($alert_message) ?>,
            icon: <?= json_encode($alert_type) ?>,
            confirmButtonText: 'OK'
        });
    });
</script>
<?php endif; ?>

<?php if (!empty($form_errors)): ?>
    <!-- Script pour afficher les erreurs spécifiques du formulaire avec SweetAlert2 -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'Erreurs de validation',
            html: <?= json_encode(implode('<br>', $form_errors)) ?>,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
</script>
<?php endif; ?>
<!-- End Message alert avec sweet alert   -->

<!-- Transmettre les permissions utilisateur au JavaScript -->
<script>
    // Définir les permissions utilisateur pour le JavaScript
    const userPermissions = <?= json_encode($roleAccess->getRolePermissions()) ?>;
</script>

<!-- Remplacer tout le bloc <script> par cette simple inclusion -->
<script src="assets/js/chauffeurs/gestion_chauffeur.js"></script>

<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php"); ?>
<!--end footer   -->