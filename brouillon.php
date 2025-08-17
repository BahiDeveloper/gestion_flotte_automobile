// gestion_documents.php
<?php
include_once("database" . DIRECTORY_SEPARATOR . "config.php");
include_once("request/request_documents.php");
include_once("alerts/alert_documents.php");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Documents</title>
    <!-- Inclure les CSS nécessaires -->
</head>
<body>
    <?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>

    <div class="container mt-4">
        <h2 class="text-center mb-4">
            <i class="fas fa-file-alt"></i> Gestion des Documents
        </h2>

        <!-- Onglets de navigation -->
        <ul class="nav nav-tabs" id="documentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ajouter-tab" data-bs-toggle="tab" 
                        data-bs-target="#ajouter" type="button" role="tab">
                    <i class="fas fa-plus"></i> Ajouter un Document
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="liste-tab" data-bs-toggle="tab" 
                        data-bs-target="#liste" type="button" role="tab">
                    <i class="fas fa-list"></i> Liste des Documents
                </button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="documentTabsContent">
            <!-- Onglet Ajout -->
            <div class="tab-pane fade show active" id="ajouter" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-plus"></i> Nouveau Document
                    </div>
                    <div class="card-body">
                        <form method="POST" action="actions/add_document.php" enctype="multipart/form-data" id="documentForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="type_document" class="form-label">Type de Document</label>
                                        <select class="form-select" id="type_document" name="type_document" required>
                                            <option value="carte_transport">Carte de transport</option>
                                            <option value="carte_grise">Carte grise</option>
                                            <option value="visite_technique">Visite technique</option>
                                            <option value="assurance">Assurance</option>
                                            <option value="carte_stationnement">Carte de stationnement</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="numero_document" class="form-label">Numéro du document</label>
                                        <input type="text" class="form-control" id="numero_document" 
                                               name="numero_document" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="id_vehicule" class="form-label">Véhicule</label>
                                        <select class="form-select" id="id_vehicule" name="id_vehicule" required>
                                            <?php
                                            $sql_vehicules = "SELECT id_vehicule, marque, modele, immatriculation 
                                                            FROM vehicules WHERE statut != 'hors_service'";
                                            $stmt_vehicules = $connexion->prepare($sql_vehicules);
                                            $stmt_vehicules->execute();
                                            while($vehicule = $stmt_vehicules->fetch()) {
                                                echo "<option value='" . $vehicule['id_vehicule'] . "'>" 
                                                    . htmlspecialchars($vehicule['marque']) . " " 
                                                    . htmlspecialchars($vehicule['modele']) . " - " 
                                                    . htmlspecialchars($vehicule['immatriculation']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_emission" class="form-label">Date d'émission</label>
                                        <input type="date" class="form-control" id="date_emission" 
                                               name="date_emission" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="date_expiration" class="form-label">Date d'expiration</label>
                                        <input type="date" class="form-control" id="date_expiration" 
                                               name="date_expiration" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fournisseur" class="form-label">Fournisseur</label>
                                        <input type="text" class="form-control" id="fournisseur" name="fournisseur">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prix" class="form-label">Prix</label>
                                        <input type="number" step="0.01" class="form-control" id="prix" name="prix">
                                    </div>

                                    <div class="mb-3">
                                        <label for="frequence_renouvellement" class="form-label">
                                            Fréquence de renouvellement (jours)
                                        </label>
                                        <input type="number" class="form-control" id="frequence_renouvellement" 
                                               name="frequence_renouvellement">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fichier" class="form-label">Document (PDF)</label>
                                        <input type="file" class="form-control" id="fichier" name="fichier" 
                                               accept=".pdf" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="note" class="form-label">Note</label>
                                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Réinitialiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Onglet Liste -->
            <div class="tab-pane fade" id="liste" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-list"></i> Documents Enregistrés
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="documentsTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>N° Document</th>
                                        <th>Véhicule</th>
                                        <th>Émission</th>
                                        <th>Expiration</th>
                                        <th>Statut</th>
                                        <th>Fournisseur</th>
                                        <th>Prix</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $document): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($document['type_document']) ?></td>
                                            <td><?= htmlspecialchars($document['numero_document']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($document['marque']) ?> 
                                                <?= htmlspecialchars($document['modele']) ?> - 
                                                <?= htmlspecialchars($document['immatriculation']) ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($document['date_emission'])) ?></td>
                                            <td><?= date('d/m/Y', strtotime($document['date_expiration'])) ?></td>
                                            <td>
                                                <span class="badge <?= getStatusBadgeClass($document['statut']) ?>">
                                                    <?= htmlspecialchars($document['statut']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($document['fournisseur']) ?></td>
                                            <td><?= number_format($document['prix'], 2, ',', ' ') ?> €</td>
                                            <td>
                                                <div class="btn-group">
                                                    <?php if($document['fichier_url']): ?>
                                                        <a href="<?= htmlspecialchars($document['fichier_url']) ?>" 
                                                           class="btn btn-sm btn-success" target="_blank" 
                                                           title="Télécharger">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="edit_document.php?id=<?= $document['id_document'] ?>" 
                                                       class="btn btn-sm btn-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger delete-document" 
                                                            data-id="<?= $document['id_document'] ?>" 
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
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
        </div>
    </div>

    <?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
</body>
</html>

// request_documents.php
<?php
try {
    $sql = "SELECT 
        da.*,
        v.marque,
        v.modele,
        v.immatriculation,
        u.nom AS nom_utilisateur,
        u.prenom AS prenom_utilisateur
    FROM documents_administratifs da
    LEFT JOIN vehicules v ON da.id_vehicule = v.id_vehicule
    LEFT JOIN utilisateurs u ON da.id_utilisateur = u.id_utilisateur
    ORDER BY da.date_expiration ASC";
    
    $stmt = $connexion->prepare($sql);
    $stmt->execute();
    $documents = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Erreur lors de la récupération des documents : " . $e->getMessage());
    $documents = [];
}

// Fonction utilitaire pour les badges de statut
function getStatusBadgeClass($status) {
    switch($status) {
        case 'valide':
            return 'bg-success';
        case 'expire':
            return 'bg-danger';
        case 'a_renouveler':
            return 'bg-warning text-dark';
        default:
            return 'bg-secondary';
    }
}
?>

// assets/js/gestion_documents.js
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des composants
    initDateValidation();
    initDeleteConfirmation();
    initFileValidation();
    initFormValidation();
});

function initDateValidation() {
    const dateEmission = document.getElementById('date_emission');
    const dateExpiration = document.getElementById('date_expiration');

    if (dateEmission && dateExpiration) {
        dateEmission.addEventListener('change', function() {
            dateExpiration.min = this.value;
            validateDates();
        });

        dateExpiration.addEventListener('change', validateDates);
    }
}

function validateDates() {
    const dateEmission = document.getElementById('date_emission');
    const dateExpiration = document.getElementById('date_expiration');

    if (dateExpiration.value && dateEmission.value > dateExpiration.value) {
        dateExpiration.setCustomValidity('La date d\'expiration doit être postérieure à la date d\'émission');
    } else {
        dateExpiration.setCustomValidity('');
    }
}

function initDeleteConfirmation() {
    document.querySelectorAll('.delete-document').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const documentId = this.dataset.id;
            
            if (confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
                window.location.href = `actions/delete_document.php?id=${documentId}`;
            }
        });
    });
}

function initFileValidation() {
    const fileInput = document.getElementById('fichier');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            const maxSize = 5 * 1024 * 1024; // 5 MB
            
            if (file) {
                if (file.type !== 'application/pdf') {
                    alert('Seuls les fichiers PDF sont acceptés');
                    this.value = '';
                    return;
                }
                
                if (file.size > maxSize) {
                    alert('Le fichier est trop volumineux. Taille maximum : 5 MB');
                    this.value = '';
                    return;
                }
            }
        });
    }
}

function initFormValidation() {
    const form = document.getElementById('documentForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Suite de assets/js/gestion_documents.js

            // Mettre en évidence les champs invalides
            form.classList.add('was-validated');

            // Afficher un message d'erreur général
            const invalidFields = form.querySelectorAll(':invalid');
            if (invalidFields.length > 0) {
                alert('Veuillez remplir tous les champs obligatoires correctement.');
            }
        });
    }
}

// assets/js/dataTable_for_doc_car.js
$(document).ready(function() {
    $('#documentsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        responsive: true,
        order: [[4, 'asc']], // Trier par date d'expiration par défaut
        columns: [
            { data: 'type_document' },
            { data: 'numero_document' },
            { data: 'vehicule' },
            { 
                data: 'date_emission',
                render: function(data) {
                    return moment(data).format('DD/MM/YYYY');
                }
            },
            { 
                data: 'date_expiration',
                render: function(data) {
                    return moment(data).format('DD/MM/YYYY');
                }
            },
            { 
                data: 'statut',
                render: function(data) {
                    let badgeClass = '';
                    switch(data) {
                        case 'valide':
                            badgeClass = 'bg-success';
                            break;
                        case 'expire':
                            badgeClass = 'bg-danger';
                            break;
                        case 'a_renouveler':
                            badgeClass = 'bg-warning text-dark';
                            break;
                        default:
                            badgeClass = 'bg-secondary';
                    }
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { data: 'fournisseur' },
            { 
                data: 'prix',
                render: function(data) {
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'XOF'
                    }).format(data);
                }
            },
            {
                data: 'actions',
                orderable: false,
                searchable: false
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimer',
                className: 'btn btn-primary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            }
        ]
    });
});

// alerts/alert_documents.php
<?php
// Fonction pour générer les alertes pour les documents
function genererAlertesDocuments() {
    global $connexion;
    
    try {
        // Récupérer les délais d'alerte depuis les paramètres système
        $sql_params = "SELECT cle, valeur FROM parametres_systeme WHERE cle LIKE 'delai_alerte_document%'";
        $stmt_params = $connexion->prepare($sql_params);
        $stmt_params->execute();
        $params = $stmt_params->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Récupérer les documents qui vont expirer
        $sql_documents = "SELECT 
            da.*,
            v.marque, 
            v.modele, 
            v.immatriculation
        FROM documents_administratifs da
        LEFT JOIN vehicules v ON da.id_vehicule = v.id_vehicule
        WHERE da.date_expiration >= CURRENT_DATE
        AND da.date_expiration <= DATE_ADD(CURRENT_DATE, INTERVAL :delai DAY)
        AND da.statut = 'valide'";
        
        $stmt_documents = $connexion->prepare($sql_documents);
        
        // Pour chaque délai d'alerte
        foreach ($params as $key => $delai) {
            $stmt_documents->bindParam(':delai', $delai, PDO::PARAM_INT);
            $stmt_documents->execute();
            $documents = $stmt_documents->fetchAll();
            
            foreach ($documents as $doc) {
                // Vérifier si une alerte existe déjà
                $sql_check = "SELECT id_alerte FROM alertes_documents 
                            WHERE id_document = :id_document 
                            AND type_alerte = :type_alerte 
                            AND statut = 'active'";
                $stmt_check = $connexion->prepare($sql_check);
                $type_alerte = substr($key, -1); // Récupère le dernier caractère (1, 2 ou 3)
                $stmt_check->execute([
                    ':id_document' => $doc['id_document'],
                    ':type_alerte' => $type_alerte
                ]);
                
                if (!$stmt_check->fetch()) {
                    // Créer une nouvelle alerte
                    $sql_insert = "INSERT INTO alertes_documents 
                                 (id_document, type_alerte, date_alerte, statut) 
                                 VALUES (:id_document, :type_alerte, :date_alerte, 'active')";
                    $stmt_insert = $connexion->prepare($sql_insert);
                    $stmt_insert->execute([
                        ':id_document' => $doc['id_document'],
                        ':type_alerte' => $type_alerte,
                        ':date_alerte' => date('Y-m-d')
                    ]);
                }
            }
        }
        
        // Mettre à jour le statut des documents expirés
        $sql_update = "UPDATE documents_administratifs 
                      SET statut = 'expire' 
                      WHERE date_expiration < CURRENT_DATE 
                      AND statut != 'expire'";
        $connexion->exec($sql_update);
        
        return true;
    } catch(PDOException $e) {
        error_log("Erreur lors de la génération des alertes : " . $e->getMessage());
        return false;
    }
}

// Générer les alertes au chargement de la page
genererAlertesDocuments();

// Récupérer les alertes actives pour affichage
try {
    $sql_alertes = "SELECT 
        ad.id_alerte,
        ad.type_alerte,
        ad.date_alerte,
        da.type_document,
        da.numero_document,
        da.date_expiration,
        v.marque,
        v.modele,
        v.immatriculation
    FROM alertes_documents ad
    JOIN documents_administratifs da ON ad.id_document = da.id_document
    LEFT JOIN vehicules v ON da.id_vehicule = v.id_vehicule
    WHERE ad.statut = 'active'
    ORDER BY da.date_expiration ASC";
    
    $stmt_alertes = $connexion->prepare($sql_alertes);
    $stmt_alertes->execute();
    $alertes = $stmt_alertes->fetchAll();
    
    if (count($alertes) > 0) {
        foreach ($alertes as $alerte) {
            $jours_restants = (strtotime($alerte['date_expiration']) - time()) / (60 * 60 * 24);
            $type_badge = $jours_restants <= 7 ? 'danger' : ($jours_restants <= 30 ? 'warning' : 'info');
            
            echo "<div class='alert alert-{$type_badge} alert-dismissible fade show' role='alert'>";
            echo "<strong>Attention !</strong> Le document {$alerte['type_document']} ";
            echo "n° {$alerte['numero_document']} pour le véhicule {$alerte['marque']} {$alerte['modele']} ";
            echo "({$alerte['immatriculation']}) expire le " . date('d/m/Y', strtotime($alerte['date_expiration']));
            echo " <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
            echo "</div>";
        }
    }
} catch(PDOException $e) {
    error_log("Erreur lors de la récupération des alertes : " . $e->getMessage());
}
?>

// actions/add_document.php
<?php
include_once("../database/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validation des données
        $required_fields = ['type_document', 'numero_document', 'date_emission', 
                          'date_expiration', 'id_vehicule'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Le champ $field est obligatoire");
            }
        }

        // Validation du fichier
        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur lors du téléchargement du fichier");
        }

        $file = $_FILES['fichier'];
        if ($file['type'] !== 'application/pdf') {
            throw new Exception("Seuls les fichiers PDF sont acceptés");
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5 MB
            throw new Exception("Le fichier est trop volumineux (max 5 MB)");
        }

        // Générer un nom de fichier unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nouveau_nom = uniqid() . '.' . $extension;
        $dossier_upload = "../uploads/documents/";
        
        // Créer le dossier s'il n'existe pas
        if (!file_exists($dossier_upload)) {
            mkdir($dossier_upload, 0777, true);
        }

        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $dossier_upload . $nouveau_nom)) {
            throw new Exception("Erreur lors de l'enregistrement du fichier");
        }

        // Préparation de la requête d'insertion
        $sql = "INSERT INTO documents_administratifs (
            type_document, numero_document, id_vehicule, id_utilisateur,
            date_emission, date_expiration, fournisseur, prix,
            frequence_renouvellement, fichier_url, statut, note
        ) VALUES (
            :type_document, :numero_document, :id_vehicule, :id_utilisateur,
            :date_emission, :date_expiration, :fournisseur, :prix,
            :frequence_renouvellement, :fichier_url, :statut, :note
        )";

        $stmt = $connexion->prepare($sql);
        
        // Déterminer le statut en fonction de la date d'expiration
        $statut = 'valide';
        if (strtotime($_POST['date_expiration']) < time()) {
            $statut = 'expire';
        }

        // Exécution de la requête
        $stmt->execute([
            ':type_document' => $_POST['type_document'],
            ':numero_document' => $_POST['numero_document'],
            ':id_vehicule' => $_POST['id_vehicule'],
            ':id_utilisateur' => $_SESSION['user_id'] ?? null,
            ':date_emission' => $_POST['date_emission'],
            ':date_expiration' => $_POST['date_expiration'],
            ':fournisseur' => $_POST['fournisseur'] ?? null,
            ':prix' => $_POST['prix'] ?? null,
            ':frequence_renouvellement' => $_POST['frequence_renouvellement'] ?? null,
            ':fichier_url' => 'uploads/documents/' . $nouveau_nom,
            ':statut' => $statut,
            ':note' => $_POST['note'] ?? null
        ]);

        // Enregistrer l'action dans le journal
        $sql_log = "INSERT INTO journal_activites (
            id_utilisateur, type_activite, description
        ) VALUES (
            :id_utilisateur, 'ajout_document', 
            :description
        )";
        
        $stmt_log = $connexion->prepare($sql_log);
        $stmt_log->execute([
            ':id_utilisateur' => $_SESSION['user_id'] ?? null,
            ':description' => "Ajout du document {$_POST['type_document']} n° {$_POST['numero_document']}"
        ]);

        // Redirection avec message de succès
        $_SESSION['success'] = "Le document a été ajouté avec succès";
        header('Location: ../gestion_documents.php');
        exit();

    } catch (Exception $e) {
        // En cas d'erreur, supprimer le fichier si déjà uploadé
        if (isset($nouveau_nom) && file_exists($dossier_upload . $nouveau_nom)) {
            unlink($dossier_upload . $nouveau_nom);
        }
        
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header('Location: ../gestion_documents.php');
        exit();
    }
} else {
    header('Location: ../gestion_documents.php');
    exit();
}
?>