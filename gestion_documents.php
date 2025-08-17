<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Modification de la récupération de l'ID utilisateur
$id_utilisateur = $_SESSION['id_utilisateur'];


if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['utilisateur', 'validateur'])) {
  header('Location: index.php');
  exit;
}

// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Requête pour récupérer les documents actifs avec leurs informations détaillées
$sql = "SELECT d.*, 
       v.marque, v.modele, v.immatriculation,
       CONCAT(u.nom, ' ', u.prenom) as nom_utilisateur,
       (CASE 
           WHEN d.frequence_renouvellement = 0 THEN 'permanent'
           WHEN DATEDIFF(d.date_expiration, CURDATE()) <= 7 THEN 'urgente'
           WHEN DATEDIFF(d.date_expiration, CURDATE()) <= 30 THEN 'proche'
           WHEN DATEDIFF(d.date_expiration, CURDATE()) <= 60 THEN 'attention'
           ELSE 'valide'
       END) as statut_alerte
       FROM documents_administratifs d
       LEFT JOIN vehicules v ON d.id_vehicule = v.id_vehicule
       LEFT JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
       WHERE d.statut != 'expire'
       ORDER BY 
           CASE 
               WHEN d.frequence_renouvellement = 0 THEN 1
               WHEN DATEDIFF(d.date_expiration, CURDATE()) <= 7 THEN 2
               WHEN DATEDIFF(d.date_expiration, CURDATE()) <= 30 THEN 3
               WHEN DATEDIFF(d.date_expiration, CURDATE()) <= 60 THEN 4
               ELSE 5
           END, d.date_expiration ASC";

try {
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $error_message = "Erreur lors de la récupération des documents: " . $e->getMessage();
  $documents = [];
}

// Requête pour les documents expirés (historique)
$sql_expired = "SELECT d.*, 
       v.marque, v.modele, v.immatriculation,
       CONCAT(u.nom, ' ', u.prenom) as nom_utilisateur
       FROM documents_administratifs d
       LEFT JOIN vehicules v ON d.id_vehicule = v.id_vehicule
       LEFT JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
       WHERE d.statut = 'expire'
       ORDER BY d.date_expiration DESC";

try {
  $stmt_expired = $pdo->prepare($sql_expired);
  $stmt_expired->execute();
  $expired_documents = $stmt_expired->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $error_message = "Erreur lors de la récupération des documents expirés: " . $e->getMessage();
  $expired_documents = [];
}

// Obtenir les paramètres des notifications
$sql_params = "SELECT * FROM parametres_systeme WHERE cle LIKE 'delai_alerte_document%'";
try {
  $stmt_params = $pdo->prepare($sql_params);
  $stmt_params->execute();
  $alert_params = $stmt_params->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
  $alert_params = [
    'delai_alerte_document_1' => 60,
    'delai_alerte_document_2' => 30,
    'delai_alerte_document_3' => 7
  ];
}

// Inclure le header
include_once("includes" . DIRECTORY_SEPARATOR . "header.php");
?>

<?php
// Au début du fichier, ajoutez ceci après l'inclusion du header
if (!isset($roleAccess)) {
  require_once 'includes/RoleAccess.php';
  $roleAccess = new RoleAccess($_SESSION['role']);
}
?>

<div class="container-fluid py-4">
  <h1 class="text-center mb-4">
    <i class="fas fa-file-alt me-2"></i>Gestion des Documents Administratifs
  </h1>

  <!-- Onglets de navigation -->
  <ul class="nav nav-tabs mb-4" id="documentTabs" role="tablist">

    <?php if ($roleAccess->hasPermission('tracking')): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button"
          role="tab">
          <i class="fas fa-list me-2"></i>Documents Actifs
        </button>
      </li>
    <?php endif; ?>

    <?php if ($roleAccess->hasPermission('form')): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab">
          <i class="fas fa-plus me-2"></i>Ajouter un Document
        </button>
      </li>
    <?php endif; ?>

    <li class="nav-item" role="presentation">
      <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
        <i class="fas fa-history me-2"></i>Historique
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button"
        role="tab">
        <i class="fas fa-cog me-2"></i>Paramètres
      </button>
    </li>
  </ul>

  <!-- Contenu des onglets -->
  <div class="tab-content" id="documentTabsContent">
    <!-- Documents Actifs -->
    <div class="tab-pane fade show active" id="list" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Documents Actifs</h5>
            <div>
              <button class="btn btn-sm btn-outline-primary" id="btnExport">
                <i class="fas fa-file-export me-1"></i>Exporter
              </button>
              <button class="btn btn-sm btn-outline-secondary" id="btnPrint">
                <i class="fas fa-print me-1"></i>Imprimer
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="documentsTable" class="table table-striped">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Véhicule</th>
                  <th>Utilisateur</th>
                  <th>Émission</th>
                  <th>Expiration</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($documents as $doc): ?>
                  <tr class="<?=
                    $doc['statut_alerte'] == 'urgente' ? 'table-danger' :
                    ($doc['statut_alerte'] == 'proche' ? 'table-warning' :
                      ($doc['statut_alerte'] == 'attention' ? 'table-info' : ''))
                    ?>">
                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['type_document']))) ?></td>
                    <td>
                      <?= $doc['marque'] ? htmlspecialchars($doc['marque'] . ' ' . $doc['modele'] . ' (' . $doc['immatriculation'] . ')') : 'N/A' ?>
                    </td>
                    <td><?= htmlspecialchars($doc['nom_utilisateur'] ?? 'N/A') ?></td>
                    <td><?= date('d/m/Y', strtotime($doc['date_emission'])) ?></td>
                    <td>
                      <?php if ($doc['frequence_renouvellement'] == 0): ?>
                        <span class="badge bg-secondary" style="color: white !important;">
                          <i class="fas fa-file-alt"></i> Permanent
                        </span>
                      <?php else: ?>
                        <?= date('d/m/Y', strtotime($doc['date_expiration'])) ?>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($doc['statut_alerte'] == 'urgente'): ?>
                        <span class="badge bg-danger">
                          <i class="fas fa-exclamation-triangle fa-pulse"></i>
                          Expire dans <?= ceil((strtotime($doc['date_expiration']) - time()) / 86400) ?> jours
                        </span>
                      <?php elseif ($doc['statut_alerte'] == 'proche'): ?>
                        <span class="badge bg-warning text-dark">
                          <i class="fas fa-exclamation-circle"></i>
                          Expire dans <?= ceil((strtotime($doc['date_expiration']) - time()) / 86400) ?> jours
                        </span>
                      <?php elseif ($doc['statut_alerte'] == 'attention'): ?>
                        <span class="badge bg-info">
                          <i class="fas fa-info-circle"></i>
                          Expire dans <?= ceil((strtotime($doc['date_expiration']) - time()) / 86400) ?> jours
                        </span>
                      <?php else: ?>
                        <span class="badge bg-success">Valide</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="btn-group">

                        <?php if (!empty($doc['fichier_url'])): ?>

                          <a href="uploads/documents/<?= htmlspecialchars($doc['fichier_url']) ?>" download
                            class="btn btn-sm btn-secondary" title="Télécharger">
                            <i class="fas fa-download"></i>
                          </a>
                          <!-- Voir les details  -->
                          <!-- Ligne corrigée -->
                          <a href="javascript:void(0)" onclick="viewDocumentDetails(<?= $doc['id_document'] ?>)"
                            class="btn btn-sm btn-info" title="Visualiser">
                            <i class="fas fa-eye"></i>
                          </a>

                        <?php else: ?>
                          <span class="text-muted">Aucun fichier</span>
                        <?php endif; ?>


                        <?php if ($doc['frequence_renouvellement'] != 0): // Afficher le bouton uniquement si non permanent ?>
                          <a href="renouveler_document.php?id=<?= $doc['id_document'] ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-sync-alt"></i>
                          </a>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrateur'): ?>
                          <button type="button" class="btn btn-sm btn-danger delete-doc"
                            data-id="<?= $doc['id_document'] ?>" data-type="<?= htmlspecialchars($doc['type_document']) ?>">
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

    <!-- Ajouter un Document -->
    <div class="tab-pane fade" id="add" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Ajouter un Document</h5>
        </div>
        <div class="card-body">
          <form id="addDocumentForm" action="actions/documents/add_document.php" method="POST"
            enctype="multipart/form-data">
            <div class="row">
              <input type="hidden" name="id_utilisateur" value="<?= $_SESSION['id_utilisateur'] ?>">

              <!-- Informations principales -->
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="type_document" class="form-label">Type de Document</label>
                  <select class="form-select" id="type_document" name="type_document" required>
                    <option value="">Sélectionnez un type</option>
                    <option value="carte_transport">Carte de transport</option>
                    <option value="carte_grise">Carte grise</option>
                    <option value="visite_technique">Vignette de visite technique</option>
                    <option value="assurance">Assurance</option>
                    <option value="carte_stationnement">Carte de stationnement</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="numero_document" class="form-label">Numéro du Document</label>
                  <input type="text" class="form-control" id="numero_document" name="numero_document" required>
                </div>
                <!-- Nouveau champ pour la fréquence de renouvellement -->
                <div class="mb-3">
                  <label for="frequence_renouvellement" class="form-label">Fréquence de Renouvellement</label>
                  <select class="form-select" id="frequence_renouvellement" name="frequence_renouvellement" required>
                    <option value="permanent">Permanent</option>
                    <option value="mensuel">Mensuel</option>
                    <option value="trimestriel">Trimestriel</option>
                    <option value="semestriel">Semestriel</option>
                    <option value="annuel" selected>Annuel</option>
                  </select>
                  <small class="form-text text-muted">Fréquence à laquelle ce document doit être renouvelé</small>
                </div>
                <div class="mb-3">
                  <label for="date_emission" class="form-label">Date d'émission</label>
                  <input type="date" class="form-control" id="date_emission" name="date_emission"
                    max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-3">
                  <label for="date_expiration" class="form-label">Date d'expiration</label>
                  <input type="date" class="form-control" id="date_expiration" name="date_expiration"
                    min="<?= date('Y-m-d') ?>" required>
                </div>
              </div>

              <!-- Informations complémentaires -->
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="id_vehicule" class="form-label">Véhicule (si applicable)</label>
                  <select class="form-select" id="id_vehicule" name="id_vehicule">
                    <option value="">Sélectionnez un véhicule</option>
                    <?php
                    $stmt_vehicules = $pdo->query("SELECT id_vehicule, marque, modele, immatriculation FROM vehicules");
                    while ($vehicule = $stmt_vehicules->fetch()):
                      ?>
                      <option value="<?= $vehicule['id_vehicule'] ?>">
                        <?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['immatriculation'] . ')') ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="fournisseur" class="form-label">Fournisseur</label>
                  <input type="text" class="form-control" id="fournisseur" name="fournisseur" required>
                </div>
                <div class="mb-3">
                  <label for="prix" class="form-label">Prix</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="prix" name="prix" step="0.01"> <!-- required -->
                    <span class="input-group-text">FCFA</span>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="fichier" class="form-label">Document</label>
                  <input type="file" class="form-control" id="fichier" name="fichier" accept=".pdf,.jpg,.jpeg,.png"
                    required>
                  <small class="form-text">Formats acceptés: PDF, JPG, JPEG, PNG (max 5MB)</small>
                </div>
                <div class="mb-3">
                  <label for="note" class="form-label">Notes</label>
                  <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                </div>
              </div>
            </div>
            <div class="text-end mt-3">
              <button type="reset" class="btn btn-secondary">
                <i class="fas fa-undo me-2"></i>Réinitialiser
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Enregistrer
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Historique -->
    <div class="tab-pane fade" id="history" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des Documents</h5>
            <div>
              <button class="btn btn-sm btn-outline-primary" id="btnExportHistory">
                <i class="fas fa-file-export me-1"></i>Exporter
              </button>
              <button class="btn btn-sm btn-outline-secondary" id="btnPrintHistory">
                <i class="fas fa-print me-1"></i>Imprimer
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="historyTable" class="table table-striped">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Véhicule</th>
                  <th>Utilisateur</th>
                  <th>Émission</th>
                  <th>Expiration</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($expired_documents as $doc): ?>
                  <tr>
                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['type_document']))) ?></td>
                    <td>
                      <?= $doc['marque'] ? htmlspecialchars($doc['marque'] . ' ' . $doc['modele'] . ' (' . $doc['immatriculation'] . ')') : 'N/A' ?>
                    </td>
                    <td><?= htmlspecialchars($doc['nom_utilisateur']) ?></td>
                    <td><?= date('d/m/Y', strtotime($doc['date_emission'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($doc['date_expiration'])) ?></td>
                    <td>
                      <div class="btn-group">
                        <a href="uploads/documents/<?= htmlspecialchars($doc['fichier_url']) ?>"
                          class="btn btn-sm btn-success" download>
                          <i class="fas fa-download"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-info view-document" data-bs-toggle="modal"
                          data-bs-target="#documentModal" data-id="<?= $doc['id_document'] ?>">
                          <i class="fas fa-eye"></i>
                        </button>
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

    <!-- Paramètres -->
    <div class="tab-pane fade" id="settings" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Paramètres des Notifications</h5>
        </div>
        <div class="card-body">
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrateur'): ?>
            <form action="actions/documents/update_notification_settings.php" method="POST">
              <div class="row">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="delai_alerte_document_1" class="form-label">
                      <i class="fas fa-info-circle text-info"></i> Première alerte (jours)
                    </label>
                    <input type="number" class="form-control" id="delai_alerte_document_1" name="delai_alerte_document_1"
                      min="1" max="365" value="<?= $alert_params['delai_alerte_document_1'] ?? 60 ?>">
                    <small class="form-text text-muted">Par défaut: 60 jours avant expiration</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="delai_alerte_document_2" class="form-label">
                      <i class="fas fa-exclamation-circle text-warning"></i> Deuxième alerte (jours)
                    </label>
                    <input type="number" class="form-control" id="delai_alerte_document_2" name="delai_alerte_document_2"
                      min="1" max="60" value="<?= $alert_params['delai_alerte_document_2'] ?? 30 ?>">
                    <small class="form-text text-muted">Par défaut: 30 jours avant expiration</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="delai_alerte_document_3" class="form-label">
                      <i class="fas fa-exclamation-triangle text-danger"></i> Alerte urgente (jours)
                    </label>
                    <input type="number" class="form-control" id="delai_alerte_document_3" name="delai_alerte_document_3"
                      min="1" max="30" value="<?= $alert_params['delai_alerte_document_3'] ?? 7 ?>">
                    <small class="form-text text-muted">Par défaut: 7 jours avant expiration</small>
                  </div>
                </div>
              </div>
              <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                </button>
              </div>
            </form>
          <?php else: ?>
            <div class="alert alert-info">
              <i class="fas fa-lock me-2"></i>Seuls les administrateurs peuvent modifier les paramètres de notification.
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle text-info me-2"></i>Première alerte</h6>
                    <p class="card-text">Notification <?= $alert_params['delai_alerte_document_1'] ?? 60 ?> jours avant
                      expiration</p>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-exclamation-circle text-warning me-2"></i>Deuxième alerte</h6>
                    <p class="card-text">Notification <?= $alert_params['delai_alerte_document_2'] ?? 30 ?> jours avant
                      expiration</p>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Alerte urgente</h6>
                    <p class="card-text">Notification <?= $alert_params['delai_alerte_document_3'] ?? 7 ?> jours avant
                      expiration</p>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal pour afficher les détails d'un document -->
<div class="modal fade" id="documentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Détails du Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
        </div>
        <div id="documentDetails" class="d-none">
          <!-- Le contenu sera chargé dynamiquement -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <a href="#" class="btn btn-success" id="downloadDocumentBtn">
          <i class="fas fa-download me-2"></i>Télécharger
        </a>
        <a href="#" class="btn btn-warning" id="renewDocumentBtn">
          <i class="fas fa-sync-alt me-2"></i>Renouveler
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Scripts spécifiques à la page -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Initialisation des DataTables
    $('#documentsTable, #historyTable').DataTable({
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
      },
      dom: 'Bfrtip',
      buttons: ['copy', 'csv', 'print']
    });

    // Gestion de la vue des documents
    $('.view-document').click(function () {
      const documentId = $(this).data('id');
      const modal = $('#documentModal');

      // Réinitialiser et afficher le spinner
      $('#documentDetails').addClass('d-none');
      $('.spinner-border').removeClass('d-none');

      // Configurer les boutons
      $('#downloadDocumentBtn').attr('href', `actions/documents/download_document.php?id=${documentId}`);
      $('#renewDocumentBtn').attr('href', `actions/documents/renew_document.php?id=${documentId}`);

      // Charger les détails
      fetch(`actions/documents/get_document_details.php?id=${documentId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            $('#documentDetails').html(data.html).removeClass('d-none');
          } else {
            $('#documentDetails').html(
              '<div class="alert alert-danger">Erreur lors du chargement des détails.</div>'
            ).removeClass('d-none');
          }
          $('.spinner-border').addClass('d-none');
        })
        .catch(error => {
          console.error('Erreur:', error);
          $('#documentDetails').html(
            '<div class="alert alert-danger">Erreur de connexion.</div>'
          ).removeClass('d-none');
          $('.spinner-border').addClass('d-none');
        });
    });

    // Confirmation de suppression
    $('.delete-doc').click(function (e) {
      e.preventDefault();
      const docId = $(this).data('id');
      const docType = $(this).data('type');

      Swal.fire({
        title: 'Êtes-vous sûr?',
        text: `Voulez-vous vraiment supprimer ce document (${docType})?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = `actions/documents/delete_document.php?id=${docId}`;
        }
      });
    });
  });
</script>

<script>
  // Ajouter après les autres scripts dans la même page
  document.addEventListener('DOMContentLoaded', function () {
    // Gestion de la fréquence de renouvellement
    function handleFrequencyChange() {
      const frequenceSelect = document.getElementById('frequence_renouvellement');
      const dateExpirationInput = document.getElementById('date_expiration');
      const dateExpirationContainer = dateExpirationInput.closest('.mb-3');

      if (frequenceSelect.value === 'permanent') {
        dateExpirationContainer.style.display = 'none';
        dateExpirationInput.removeAttribute('required');
      } else {
        dateExpirationContainer.style.display = 'block';
        dateExpirationInput.setAttribute('required', 'required');
        calculateExpirationDate();
      }
    }

    // Calcul automatique de la date d'expiration
    function calculateExpirationDate() {
      const dateEmission = document.getElementById('date_emission').value;
      const frequence = document.getElementById('frequence_renouvellement').value;

      if (dateEmission && frequence !== 'permanent') {
        const dateObj = new Date(dateEmission);

        switch (frequence) {
          case 'mensuel':
            dateObj.setMonth(dateObj.getMonth() + 1);
            break;
          case 'trimestriel':
            dateObj.setMonth(dateObj.getMonth() + 3);
            break;
          case 'semestriel':
            dateObj.setMonth(dateObj.getMonth() + 6);
            break;
          case 'annuel':
            dateObj.setFullYear(dateObj.getFullYear() + 1);
            break;
        }

        const expYear = dateObj.getFullYear();
        let expMonth = dateObj.getMonth() + 1;
        let expDay = dateObj.getDate();

        // Formater avec des zéros
        expMonth = expMonth < 10 ? '0' + expMonth : expMonth;
        expDay = expDay < 10 ? '0' + expDay : expDay;

        document.getElementById('date_expiration').value = `${expYear}-${expMonth}-${expDay}`;
      }
    }

    // Ajouter les écouteurs d'événements
    const frequenceSelect = document.getElementById('frequence_renouvellement');
    const dateEmissionInput = document.getElementById('date_emission');

    if (frequenceSelect && dateEmissionInput) {
      frequenceSelect.addEventListener('change', handleFrequencyChange);
      dateEmissionInput.addEventListener('change', calculateExpirationDate);

      // Initialiser l'état du formulaire
      handleFrequencyChange();
    }
  });
</script>

<!-- Message d'alert avec sweet alert  -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Configuration pour les messages de session
    <?php if (isset($_SESSION['success'])): ?>
      Swal.fire({
        icon: 'success',
        title: 'Succès',
        text: '<?= addslashes($_SESSION['success']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      Swal.fire({
        icon: 'error',
        title: 'Erreur',
        text: '<?= addslashes($_SESSION['error']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
  });
</script>

<?php include_once("includes/documents/modal/document_details.php"); ?>
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php"); ?>