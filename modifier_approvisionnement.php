<?php

session_start();

// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Inclure les requêtes nécessaires
include_once("request" . DIRECTORY_SEPARATOR . "vehicules" . DIRECTORY_SEPARATOR . "vehicules.php");

// Inclure les messages d'alerte
include_once("alerts" . DIRECTORY_SEPARATOR . "alert_vehicule.php");

// Vérifier si l'ID de l'approvisionnement est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Rediriger vers la page de gestion des véhicules si aucun ID n'est fourni
    header('Location: gestion_vehicules.php#approvisionnements');
    exit;
}

$id_approvisionnement = intval($_GET['id']);

// Récupérer les informations de l'approvisionnement
$stmt = $pdo->prepare("
    SELECT * FROM approvisionnements_carburant 
    WHERE id_approvisionnement = ?
");
$stmt->execute([$id_approvisionnement]);
$approvisionnement = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'approvisionnement existe
if (!$approvisionnement) {
    // Rediriger vers la page de gestion des véhicules si l'approvisionnement n'existe pas
    header('Location: gestion_vehicules.php#approvisionnements');
    exit;
}

// Récupérer la liste des véhicules pour le formulaire
$stmt_vehicules = $pdo->query("
    SELECT id_vehicule, marque, modele, immatriculation 
    FROM vehicules 
    ORDER BY marque, modele
");
$vehicules = $stmt_vehicules->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des chauffeurs pour le formulaire
$stmt_chauffeurs = $pdo->query("
    SELECT id_chauffeur, nom, prenoms 
    FROM chauffeurs 
    ORDER BY nom, prenoms
");
$chauffeurs = $stmt_chauffeurs->fetchAll(PDO::FETCH_ASSOC);

?>

<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->

<?php
// On vérifie que l'objet $roleAccess est bien défini (il devrait l'être dans header.php)
if (!isset($roleAccess)) {
    require_once 'includes/RoleAccess.php';
    $roleAccess = new RoleAccess($_SESSION['role']);
}

// Vérifier si l'utilisateur a les permissions nécessaires
if ($roleAccess->getUserRole() !== 'administrateur' && $roleAccess->getUserRole() !== 'gestionnaire') {
    echo '<div class="alert alert-danger">Vous n\'avez pas les permissions nécessaires pour accéder à cette page.</div>';
    include_once("includes" . DIRECTORY_SEPARATOR . "footer.php");
    exit;
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="gestion_vehicules.php">Gestion des véhicules</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Modifier un approvisionnement</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-gas-pump me-2"></i>Modifier l'approvisionnement
            </h3>
        </div>
        <div class="card-body">
            <form action="actions/vehicules/update_approvisionnement.php" method="POST">
                <input type="hidden" name="id_approvisionnement" value="<?= $approvisionnement['id_approvisionnement'] ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_vehicule" class="form-label">Véhicule</label>
                            <select class="form-select" id="id_vehicule" name="id_vehicule" required>
                                <option value="">Sélectionner un véhicule</option>
                                <?php foreach ($vehicules as $vehicule): ?>
                                    <option value="<?= $vehicule['id_vehicule'] ?>" 
                                        <?= ($vehicule['id_vehicule'] == $approvisionnement['id_vehicule']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['immatriculation'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_chauffeur" class="form-label">Chauffeur</label>
                            <select class="form-select" id="id_chauffeur" name="id_chauffeur">
                                <option value="">Aucun chauffeur</option>
                                <?php foreach ($chauffeurs as $chauffeur): ?>
                                    <option value="<?= $chauffeur['id_chauffeur'] ?>" 
                                        <?= ($chauffeur['id_chauffeur'] == $approvisionnement['id_chauffeur']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($chauffeur['nom'] . ' ' . $chauffeur['prenoms']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date_approvisionnement" class="form-label">Date d'approvisionnement</label>
                            <input type="datetime-local" class="form-control" id="date_approvisionnement" 
                                   name="date_approvisionnement" 
                                   value="<?= date('Y-m-d\TH:i', strtotime($approvisionnement['date_approvisionnement'])) ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="type_carburant" class="form-label">Type de carburant</label>
                            <select class="form-select" id="type_carburant" name="type_carburant" required>
                                <option value="essence" <?= ($approvisionnement['type_carburant'] == 'essence') ? 'selected' : '' ?>>Super</option>
                                <option value="diesel" <?= ($approvisionnement['type_carburant'] == 'diesel') ? 'selected' : '' ?>>Gasoil</option>
                                <option value="hybride" <?= ($approvisionnement['type_carburant'] == 'hybride') ? 'selected' : '' ?>>Essence</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="quantite_litres" class="form-label">Quantité (litres)</label>
                            <input type="number" step="0.01" class="form-control" id="quantite_litres" 
                                   name="quantite_litres" value="<?= $approvisionnement['quantite_litres'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="prix_unitaire" class="form-label">Prix unitaire (FCFA)</label>
                            <input type="number" class="form-control" id="prix_unitaire" 
                                   name="prix_unitaire" value="<?= $approvisionnement['prix_unitaire'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="prix_total" class="form-label">Prix total (FCFA)</label>
                            <input type="number" class="form-control" id="prix_total" 
                                   name="prix_total" value="<?= $approvisionnement['prix_total'] ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kilometrage" class="form-label">Kilométrage</label>
                            <input type="number" class="form-control" id="kilometrage" 
                                   name="kilometrage" value="<?= $approvisionnement['kilometrage'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="station_service" class="form-label">Station-service</label>
                            <input type="text" class="form-control" id="station_service" 
                                   name="station_service" value="<?= htmlspecialchars($approvisionnement['station_service'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                    <a href="gestion_vehicules.php#approvisionnements" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i>Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script pour calculer automatiquement le prix total -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantiteInput = document.getElementById('quantite_litres');
    const prixUnitaireInput = document.getElementById('prix_unitaire');
    const prixTotalInput = document.getElementById('prix_total');
    
    // Fonction pour calculer le prix total
    const calculerPrixTotal = () => {
        const quantite = parseFloat(quantiteInput.value) || 0;
        const prixUnitaire = parseFloat(prixUnitaireInput.value) || 0;
        const prixTotal = Math.round(quantite * prixUnitaire);
        prixTotalInput.value = prixTotal;
    };
    
    // Fonction pour calculer le prix unitaire
    const calculerPrixUnitaire = () => {
        const quantite = parseFloat(quantiteInput.value) || 0;
        const prixTotal = parseFloat(prixTotalInput.value) || 0;
        
        if (quantite > 0) {
            const prixUnitaire = Math.round(prixTotal / quantite);
            prixUnitaireInput.value = prixUnitaire;
        }
    };
    
    // Ajouter des écouteurs d'événements
    quantiteInput.addEventListener('input', calculerPrixTotal);
    prixUnitaireInput.addEventListener('input', calculerPrixTotal);
    prixTotalInput.addEventListener('input', calculerPrixUnitaire);
});
</script>

<!--start footer -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php"); ?>
<!--end footer -->