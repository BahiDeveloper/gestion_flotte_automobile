<?php
// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Vérifier si l'ID est fourni dans l'URL
$id_document = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id_document) {
    // Récupérer les informations du document à renouveler
    $query = "SELECT * FROM documents_administratifs WHERE id_document = :id_document";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_document', $id_document, PDO::PARAM_INT);
    $stmt->execute();

    $document_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($document_data) {
        // Stocker les informations du document dans la session
        $_SESSION['document_to_renew'] = [
            'id_document' => $document_data['id_document'],
            'id_vehicule' => $document_data['id_vehicule'],
            'id_chauffeur' => $document_data['id_chauffeur'],
            'id_utilisateur' => $document_data['id_utilisateur'],
            'type_document' => $document_data['type_document'],
            'numero_document' => $document_data['numero_document'],
            'fournisseur' => $document_data['fournisseur'],
            'frequence_renouvellement' => $document_data['frequence_renouvellement'],
            'fichier_url' => $document_data['fichier_url'],
            'note' => $document_data['note'],
            'prix' => $document_data['prix']
        ];

        // Déterminer le texte de la fréquence pour l'affichage
        $frequence_text = 'permanent';
        switch ($document_data['frequence_renouvellement']) {
            case 1:
                $frequence_text = 'mensuel';
                break;
            case 3:
                $frequence_text = 'trimestriel';
                break;
            case 6:
                $frequence_text = 'semestriel';
                break;
            case 12:
                $frequence_text = 'annuel';
                break;
        }
        $_SESSION['document_to_renew']['frequence_text'] = $frequence_text;

        // Journaliser l'accès
        if (isset($_SESSION['id_utilisateur'])) {
            $action_description = "Consultation pour renouvellement du document #{$document_data['id_document']} - " .
                ucfirst(str_replace('_', ' ', $document_data['type_document']));

            $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                         VALUES (:id_utilisateur, 'view_renew_document', :description, :ip)";
            $log_stmt = $pdo->prepare($log_query);
            $log_stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur'], PDO::PARAM_INT);
            $log_stmt->bindParam(':description', $action_description, PDO::PARAM_STR);
            $log_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
            $log_stmt->execute();
        }
    }
}

// Vérifier si un document à renouveler est présent dans la session
if (!isset($_SESSION['document_to_renew'])) {
    $_SESSION['error'] = "Aucun document à renouveler.";
    header('Location: gestion_documents.php');
    exit;
}

$document = $_SESSION['document_to_renew'];

if ($document['frequence_renouvellement'] === 0) {
    $_SESSION['error'] = "Les documents permanents ne peuvent pas être renouvelés.";
    header('Location: gestion_documents.php');
    exit;
}

// Inclure le header
include_once("includes" . DIRECTORY_SEPARATOR . "header.php");
?>

<h2 class="text-center my-4"><i class="fas fa-sync-alt"></i> Renouvellement de Document</h2>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error'] ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <i class="fas fa-sync-alt"></i> Renouveler un Document
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Vous êtes sur le point de renouveler un document.
            Veuillez compléter les nouvelles informations ci-dessous.
        </div>

        <!-- Chemin absolu vers le script de traitement -->
        <form method="POST" action="actions/documents/process_renew_document.php" enctype="multipart/form-data">
            <input type="hidden" name="original_document_id" value="<?= $document['id_document'] ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Type de Document</label>
                        <input type="text" class="form-control"
                            value="<?= ucfirst(str_replace('_', ' ', $document['type_document'])) ?>" readonly>
                        <input type="hidden" name="type_document" value="<?= $document['type_document'] ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rattaché à</label>
                        <?php
                        // Afficher les informations de rattachement
                        if ($document['id_vehicule']) {
                            $query_vehicule = "SELECT marque, modele, immatriculation FROM vehicules WHERE id_vehicule = :id";
                            $stmt_vehicule = $pdo->prepare($query_vehicule);
                            $stmt_vehicule->bindParam(':id', $document['id_vehicule'], PDO::PARAM_INT);
                            $stmt_vehicule->execute();
                            $vehicule = $stmt_vehicule->fetch(PDO::FETCH_ASSOC);

                            if ($vehicule) {
                                echo '<input type="text" class="form-control" value="Véhicule: ' .
                                    $vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['immatriculation'] . ')" readonly>';
                            }
                        } elseif ($document['id_chauffeur']) {
                            $query_chauffeur = "SELECT nom, prenoms FROM chauffeurs WHERE id_chauffeur = :id";
                            $stmt_chauffeur = $pdo->prepare($query_chauffeur);
                            $stmt_chauffeur->bindParam(':id', $document['id_chauffeur'], PDO::PARAM_INT);
                            $stmt_chauffeur->execute();
                            $chauffeur = $stmt_chauffeur->fetch(PDO::FETCH_ASSOC);

                            if ($chauffeur) {
                                echo '<input type="text" class="form-control" value="Chauffeur: ' .
                                    $chauffeur['nom'] . ' ' . $chauffeur['prenoms'] . '" readonly>';
                            }
                        } else {
                            echo '<input type="text" class="form-control" value="Document général" readonly>';
                        }
                        ?>
                        <input type="hidden" name="id_vehicule" value="<?= $document['id_vehicule'] ?>">
                        <input type="hidden" name="id_chauffeur" value="<?= $document['id_chauffeur'] ?>">
                        <input type="hidden" name="id_utilisateur" value="<?= $_SESSION['id_utilisateur'] ?>">
                    </div>

                    <div class="mb-3">
                        <label for="frequence_renouvellement" class="form-label">Fréquence de Renouvellement</label>
                        <select class="form-select" id="frequence_renouvellement" name="frequence_renouvellement"
                            required>
                            <option value="permanent" <?= $document['frequence_text'] == 'permanent' ? 'selected' : '' ?>>
                                Permanent</option>
                            <option value="mensuel" <?= $document['frequence_text'] == 'mensuel' ? 'selected' : '' ?>>
                                Mensuel</option>
                            <option value="trimestriel" <?= $document['frequence_text'] == 'trimestriel' ? 'selected' : '' ?>>Trimestriel</option>
                            <option value="semestriel" <?= $document['frequence_text'] == 'semestriel' ? 'selected' : '' ?>>Semestriel</option>
                            <option value="annuel" <?= $document['frequence_text'] == 'annuel' ? 'selected' : '' ?>>Annuel
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="numero_document" class="form-label">Numéro du Document</label>
                        <input type="text" class="form-control" id="numero_document" name="numero_document"
                            value="<?= htmlspecialchars($document['numero_document']) ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_emission" class="form-label">Date d'Émission</label>
                        <input type="date" class="form-control" id="date_emission" name="date_emission"
                            value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3" id="date_expiration_container">
                        <label for="date_expiration" class="form-label">Date d'Expiration</label>
                        <input type="date" class="form-control" id="date_expiration" name="date_expiration"
                            min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="fournisseur" class="form-label">Fournisseur</label>
                        <input type="text" class="form-control" id="fournisseur" name="fournisseur"
                            value="<?= htmlspecialchars($document['fournisseur']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="prix" class="form-label">Prix</label>
                        <input type="number" class="form-control" id="prix" name="prix" step="0.01"
                            value="<?= htmlspecialchars($document['prix']) ?>" min="0" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fichier" class="form-label">Nouveau Fichier</label>
                        <input type="file" class="form-control" id="fichier" name="fichier" required>
                        <small class="form-text text-muted">Formats acceptés: PDF, JPG, PNG (max 5MB)</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="note" class="form-label">Notes</label>
                        <textarea class="form-control" id="note" name="note"
                            rows="3"><?= htmlspecialchars($document['note']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="border-bottom pb-2">Document Original</h5>
                <div class="row">
                    <div class="col-md-6">
                        <a href="actions/documents/download_document.php?id=<?= $document['id_document'] ?>"
                            class="btn btn-secondary mb-3">
                            <i class="fas fa-download"></i> Télécharger le document original
                        </a>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="gestion_documents.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Annuler
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-sync-alt"></i> Renouveler le Document
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gestion de la fréquence de renouvellement
        function handleFrequencyChange() {
            const frequenceSelect = document.getElementById('frequence_renouvellement');
            const dateExpirationContainer = document.getElementById('date_expiration_container');
            const dateExpirationInput = document.getElementById('date_expiration');

            if (frequenceSelect.value === 'permanent') {
                dateExpirationContainer.style.display = 'none';
                dateExpirationInput.removeAttribute('required');
                dateExpirationInput.value = '';
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
        document.getElementById('frequence_renouvellement').addEventListener('change', handleFrequencyChange);
        document.getElementById('date_emission').addEventListener('change', calculateExpirationDate);
        document.getElementById('frequence_renouvellement').addEventListener('change', calculateExpirationDate);

        // Initialiser l'état du formulaire
        handleFrequencyChange();
    });
</script>

<?php
// Inclure le footer
include_once("includes" . DIRECTORY_SEPARATOR . "footer.php");
?>