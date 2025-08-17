<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['role'])) {
        header('Location: auth/views/login.php');
        exit;
    }
}

// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

// Assurez-vous que l'objet $roleAccess est disponible
if (!isset($roleAccess)) {
    include_once("includes" . DIRECTORY_SEPARATOR . "RoleAccess.php");
    $roleAccess = new RoleAccess($_SESSION['role']);
}

// Vérifier si l'ID du chauffeur est fourni dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Rediriger vers la page de gestion des chauffeurs avec un message d'erreur
    header("Location: gestion_chauffeurs.php?error=invalid_id");
    exit();
}

$id_chauffeur = intval($_GET['id']);

// Requête pour récupérer les informations du chauffeur
$query = "SELECT c.*, 
          TIMESTAMPDIFF(YEAR, c.date_naissance, CURDATE()) as age,
          CONCAT(v.marque, ' ', v.modele, ' (', v.immatriculation, ')') as vehicule_info
          FROM chauffeurs c
          LEFT JOIN vehicules v ON c.vehicule_attribue = v.id_vehicule
          WHERE c.id_chauffeur = :id_chauffeur";

$stmt = $pdo->prepare($query);
$stmt->execute(['id_chauffeur' => $id_chauffeur]);
$chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le chauffeur existe
if (!$chauffeur) {
    // Rediriger vers la page de gestion des chauffeurs avec un message d'erreur
    header("Location: gestion_chauffeurs.php?error=not_found");
    exit();
}

// Requête pour récupérer les documents administratifs du chauffeur
$query_documents = "SELECT * FROM documents_administratifs 
                   WHERE id_chauffeur = :id_chauffeur
                   ORDER BY date_expiration DESC";
$stmt_documents = $pdo->prepare($query_documents);
$stmt_documents->execute(['id_chauffeur' => $id_chauffeur]);
$documents = $stmt_documents->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'historique des réservations du chauffeur
$query_reservations = "SELECT r.*, 
                      v.marque, v.modele, v.immatriculation,
                      i.point_depart, i.point_arrivee, i.distance_prevue,
                      (r.km_retour - r.km_depart) as distance_parcourue,
                      TIMESTAMPDIFF(HOUR, r.date_depart, COALESCE(r.date_retour_effective, r.date_retour_prevue)) as duree_heures
                      FROM reservations_vehicules r
                      LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
                      LEFT JOIN itineraires i ON r.id_reservation = i.id_reservation
                      WHERE r.id_chauffeur = :id_chauffeur
                      ORDER BY r.date_depart DESC";
                      
$stmt_reservations = $pdo->prepare($query_reservations);
$stmt_reservations->execute(['id_chauffeur' => $id_chauffeur]);
$reservations = $stmt_reservations->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la consommation de carburant pour ce chauffeur
$query_carburant = "SELECT 
                   SUM(a.quantite_litres) as total_litres,
                   SUM(a.prix_total) as total_cout,
                   AVG(a.prix_unitaire) as prix_moyen_litre,
                   COUNT(a.id_approvisionnement) as nb_approvisionnements,
                   v.marque, v.modele, v.immatriculation
                   FROM approvisionnements_carburant a
                   JOIN vehicules v ON a.id_vehicule = v.id_vehicule
                   WHERE a.id_chauffeur = :id_chauffeur
                   GROUP BY a.id_vehicule
                   ORDER BY total_litres DESC";
                   
$stmt_carburant = $pdo->prepare($query_carburant);
$stmt_carburant->execute(['id_chauffeur' => $id_chauffeur]);
$consommations = $stmt_carburant->fetchAll(PDO::FETCH_ASSOC);

// Calculer les statistiques globales du chauffeur
$total_km_parcourus = 0;
$total_trajets = count($reservations);
$total_trajets_termines = 0;
$total_passagers = 0;

foreach ($reservations as $reservation) {
    if (isset($reservation['distance_parcourue']) && $reservation['distance_parcourue'] > 0) {
        $total_km_parcourus += $reservation['distance_parcourue'];
    } elseif (isset($reservation['distance_prevue']) && $reservation['distance_prevue'] > 0) {
        $total_km_parcourus += $reservation['distance_prevue'];
    }
    
    if ($reservation['statut'] == 'terminee') {
        $total_trajets_termines++;
    }
    
    $total_passagers += $reservation['nombre_passagers'];
}

// Formater le type de permis pour affichage
$types_permis = explode(',', $chauffeur['type_permis']);
$permis_labels = [
    'A' => 'A - Motos',
    'B' => 'B - Véhicules légers',
    'C' => 'C - Poids lourds',
    'D' => 'D - Transport en commun',
    'E' => 'E - Remorques'
];
$formatted_permis = [];
foreach ($types_permis as $type) {
    $formatted_permis[] = isset($permis_labels[$type]) ? $permis_labels[$type] : $type;
}
$formatted_permis_str = implode(', ', $formatted_permis);

// Déterminer la classe CSS pour l'état du permis
$statut_permis_class = '';
$statut_permis_text = '';
switch ($chauffeur['statut_permis']) {
    case 'valide':
        $statut_permis_class = 'bg-success';
        $statut_permis_text = 'Valide';
        break;
    case 'expire':
        $statut_permis_class = 'bg-danger';
        $statut_permis_text = 'Expiré';
        break;
    case 'permanant':
        $statut_permis_class = 'bg-info';
        $statut_permis_text = 'Permanent';
        break;
    default:
        $statut_permis_class = 'bg-secondary';
        $statut_permis_text = 'Non spécifié';
}

// Déterminer la classe CSS pour le statut du chauffeur
$statut_class = '';
$statut_text = '';
switch ($chauffeur['statut']) {
    case 'disponible':
        $statut_class = 'bg-success';
        $statut_text = 'Disponible';
        break;
    case 'en_course':
        $statut_class = 'bg-warning';
        $statut_text = 'En course';
        break;
    case 'conge':
        $statut_class = 'bg-info';
        $statut_text = 'En congé';
        break;
    case 'indisponible':
        $statut_class = 'bg-secondary';
        $statut_text = 'Indisponible';
        break;
    default:
        $statut_class = 'bg-secondary';
        $statut_text = 'Non spécifié';
}

// Calculer la consommation moyenne de carburant
$total_litres_global = 0;
foreach ($consommations as $consommation) {
    $total_litres_global += $consommation['total_litres'];
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
            <i class="fas fa-user-tie me-2"></i>Détails du chauffeur
        </h1>
        <div>
            <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
            <a href="edit_chauffeur.php?id=<?= $id_chauffeur ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Modifier
            </a>
            <?php endif; ?>
            <button class="btn btn-outline-secondary ms-2" id="btnPrintChauffeurDetails">
                <i class="fas fa-print me-1"></i>Imprimer
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Carte d'informations du chauffeur -->
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-id-card me-2"></i>
                        <?= htmlspecialchars($chauffeur['nom']) ?> <?= htmlspecialchars($chauffeur['prenoms']) ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if (!empty($chauffeur['photo_profil'])): ?>
                            <img src="uploads/chauffeurs/profils/<?= htmlspecialchars($chauffeur['photo_profil']) ?>"
                                alt="Photo de <?= htmlspecialchars($chauffeur['nom']) ?>" 
                                class="img-fluid rounded-circle chauffeur-profile-pic">
                        <?php else: ?>
                            <div class="bg-secondary text-white rounded-circle chauffeur-profile-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <span class="badge <?= $statut_class ?> fs-6 p-2">
                                <i class="fas fa-circle me-1"></i><?= $statut_text ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-phone text-primary"></i>
                            <div>
                                <h6>Téléphone</h6>
                                <p><?= htmlspecialchars($chauffeur['telephone']) ?></p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-envelope text-primary"></i>
                            <div>
                                <h6>Email</h6>
                                <p><?= htmlspecialchars($chauffeur['email']) ?></p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt text-primary"></i>
                            <div>
                                <h6>Adresse</h6>
                                <p><?= htmlspecialchars($chauffeur['adresse']) ?></p>
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne de droite avec les onglets -->
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" id="chauffeurDetailsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="permis-tab" data-bs-toggle="tab" 
                                   data-bs-target="#permis" type="button" role="tab" 
                                   aria-controls="permis" aria-selected="true">
                                <i class="fas fa-id-card-alt me-2"></i>Permis
                            </button>
                        </li>
                        
                        <?php if ($roleAccess->hasPermission('tracking')): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" 
                                   data-bs-target="#stats" type="button" role="tab" 
                                   aria-controls="stats" aria-selected="false">
                                <i class="fas fa-chart-bar me-2"></i>Statistiques
                            </button>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($roleAccess->hasPermission('historique')): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="historique-tab" data-bs-toggle="tab" 
                                   data-bs-target="#historique" type="button" role="tab" 
                                   aria-controls="historique" aria-selected="false">
                                <i class="fas fa-history me-2"></i>Historique
                            </button>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" 
                                   data-bs-target="#documents" type="button" role="tab" 
                                   aria-controls="documents" aria-selected="false">
                                <i class="fas fa-file-alt me-2"></i>Documents
                            </button>
                        </li>
                        
                        <?php if ($roleAccess->hasPermission('tracking')): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="consommation-tab" data-bs-toggle="tab" 
                                   data-bs-target="#consommation" type="button" role="tab" 
                                   aria-controls="consommation" aria-selected="false">
                                <i class="fas fa-gas-pump me-2"></i>Carburant
                            </button>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="chauffeurDetailsTabContent">
                        <!-- Onglet Permis de conduire -->
                        <div class="tab-pane fade show active" id="permis" role="tabpanel" aria-labelledby="permis-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title border-bottom pb-2 mb-3">Informations du permis</h5>
                                            <div class="info-list permis-info">
                                                <div class="info-item">
                                                    <div>
                                                        <h6><i class="fas fa-hashtag text-primary me-2"></i>Numéro</h6>
                                                        <p><?= htmlspecialchars($chauffeur['numero_permis']) ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="info-item">
                                                    <div>
                                                        <h6><i class="fas fa-list-alt text-primary me-2"></i>Catégories</h6>
                                                        <p><?= $formatted_permis_str ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="info-item">
                                                    <div>
                                                        <h6><i class="fas fa-calendar-plus text-primary me-2"></i>Date de délivrance</h6>
                                                        <p><?= date('d/m/Y', strtotime($chauffeur['date_delivrance_permis'])) ?></p>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($chauffeur['date_expiration_permis'])): ?>
                                                <div class="info-item">
                                                    <div>
                                                        <h6><i class="fas fa-calendar-times text-primary me-2"></i>Date d'expiration</h6>
                                                        <p><?= date('d/m/Y', strtotime($chauffeur['date_expiration_permis'])) ?></p>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <div class="info-item">
                                                    <div>
                                                        <h6><i class="fas fa-certificate text-primary me-2"></i>Statut</h6>
                                                        <p><span class="badge <?= $statut_permis_class ?>"><?= $statut_permis_text ?></span></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($chauffeur['specialisation'])): ?>
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title border-bottom pb-2 mb-3">Spécialisation</h5>
                                            <p><?= nl2br(htmlspecialchars($chauffeur['specialisation'])) ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title border-bottom pb-2 mb-3">Photo du permis</h5>
                                            <?php if (!empty($chauffeur['photo_permis'])): ?>
                                                <div class="text-center">
                                                    <img src="uploads/chauffeurs/permis/<?= htmlspecialchars($chauffeur['photo_permis']) ?>"
                                                        alt="Permis de <?= htmlspecialchars($chauffeur['nom']) ?>" 
                                                        class="img-fluid permis-image">
                                                    <a href="uploads/chauffeurs/permis/<?= htmlspecialchars($chauffeur['photo_permis']) ?>" 
                                                       class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                                                        <i class="fas fa-external-link-alt me-1"></i>Voir en taille réelle
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    Aucune photo de permis disponible
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($roleAccess->hasPermission('tracking')): ?>
                        <!-- Onglet Statistiques -->
                        <div class="tab-pane fade" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                            <div class="row g-3">
                                <!-- Cartes de statistiques -->
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm bg-primary text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-2">Total km parcourus</h6>
                                                    <h3 class="card-title mb-0"><?= number_format($total_km_parcourus, 0, ',', ' ') ?> km</h3>
                                                </div>
                                                <div class="stat-icon">
                                                    <i class="fas fa-road"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm bg-success text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-2">Trajets terminés</h6>
                                                    <h3 class="card-title mb-0"><?= $total_trajets_termines ?>/<?= $total_trajets ?></h3>
                                                </div>
                                                <div class="stat-icon">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm bg-info text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-2">Passagers transportés</h6>
                                                    <h3 class="card-title mb-0"><?= $total_passagers ?></h3>
                                                </div>
                                                <div class="stat-icon">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm bg-warning text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-2">Expérience</h6>
                                                    <?php
                                                    $date_embauche = new DateTime($chauffeur['date_embauche']);
                                                    $today = new DateTime();
                                                    $experience = $date_embauche->diff($today);
                                                    $experience_str = '';
                                                    
                                                    if ($experience->y > 0) {
                                                        $experience_str .= $experience->y . ' an' . ($experience->y > 1 ? 's' : '');
                                                    }
                                                    if ($experience->m > 0) {
                                                        $experience_str .= ($experience_str ? ', ' : '') . $experience->m . ' mois';
                                                    }
                                                    if (empty($experience_str) && $experience->d > 0) {
                                                        $experience_str = $experience->d . ' jour' . ($experience->d > 1 ? 's' : '');
                                                    }
                                                    ?>
                                                    <h3 class="card-title mb-0"><?= $experience_str ?></h3>
                                                </div>
                                                <div class="stat-icon">
                                                    <i class="fas fa-business-time"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm bg-secondary text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-2">Conso. carburant</h6>
                                                    <?php
                                                    // Calculer la consommation moyenne de carburant
                                                    $consommation_moyenne = ($total_km_parcourus > 0 && $total_litres_global > 0) 
                                                        ? ($total_litres_global / $total_km_parcourus) * 100 
                                                        : 0;
                                                    ?>
                                                    <h3 class="card-title mb-0"><?= number_format($consommation_moyenne, 2, ',', ' ') ?> L/100km</h3>
                                                </div>
                                                <div class="stat-icon">
                                                    <i class="fas fa-gas-pump"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm bg-primary text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-2">Respect des délais</h6>
                                                    <?php
                                                        // Calculer le ratio de respect des délais
                                                        $trajets_a_temps = 0;
                                                        $total_trajets_avec_delai = 0;

                                                        foreach ($reservations as $reservation) {
                                                            // Vérifier si la réservation a un statut terminé
                                                            if ($reservation['statut'] == 'terminee') {
                                                                $date_depart_prevue = new DateTime($reservation['date_depart']);
                                                                $date_retour_prevue = new DateTime($reservation['date_retour_prevue']);

                                                                // Utiliser la date de début effective si disponible, sinon utiliser la date prévue
                                                                $date_debut_effective = !empty($reservation['date_debut_effective']) 
                                                                    ? new DateTime($reservation['date_debut_effective']) 
                                                                    : $date_depart_prevue;

                                                                // Utiliser la date de retour effective si disponible, sinon utiliser la date prévue
                                                                $date_retour_effective = !empty($reservation['date_retour_effective']) 
                                                                    ? new DateTime($reservation['date_retour_effective']) 
                                                                    : $date_retour_prevue;

                                                                // Calculer le temps prévu pour la course
                                                                $duree_prevue = $date_depart_prevue->diff($date_retour_prevue);
                                                                $minutes_prevues = ($duree_prevue->days * 24 * 60) + ($duree_prevue->h * 60) + $duree_prevue->i;

                                                                // Calculer le temps réel de la course
                                                                $duree_reelle = $date_debut_effective->diff($date_retour_effective);
                                                                $minutes_reelles = ($duree_reelle->days * 24 * 60) + ($duree_reelle->h * 60) + $duree_reelle->i;

                                                                // Calculer la marge de tolérance (par exemple, 10% du temps prévu)
                                                                $tolerance = $minutes_prevues * 0.10;

                                                                // Si le temps réel est inférieur ou égal au temps prévu + tolérance
                                                                if ($minutes_reelles <= ($minutes_prevues + $tolerance)) {
                                                                    $trajets_a_temps++;
                                                                }

                                                                $total_trajets_avec_delai++;
                                                            }
                                                        }

                                                        // Calculer le pourcentage de respect des délais
                                                        $ratio_delais = ($total_trajets_avec_delai > 0) 
                                                            ? ($trajets_a_temps / $total_trajets_avec_delai) * 100 
                                                            : 0;
                                                    ?>
                                                    
                                                    <h3 class="card-title mb-0"><?= number_format($ratio_delais, 1, ',', ' ') ?> %</h3>
                                                </div>
                                                <div class="stat-icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Véhicule attribué -->
                                <div class="col-md-12 mt-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="fas fa-car me-2"></i>Véhicule attribué</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (!empty($chauffeur['vehicule_attribue']) && $chauffeur['vehicule_attribue'] != 0 && !empty($chauffeur['vehicule_info'])): ?>
                                                <div class="vehicle-info">
                                                    <div class="d-flex align-items-center">
                                                        <div class="vehicle-icon me-4">
                                                            <i class="fas fa-car-side"></i>
                                                        </div>
                                                        <div>
                                                            <h5><?= htmlspecialchars($chauffeur['vehicule_info']) ?></h5>
                                                            <a href="vehicule_details.php?id=<?= $chauffeur['vehicule_attribue'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                                <i class="fas fa-info-circle me-1"></i>Voir détails
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-info d-flex align-items-center">
                                                    <i class="fas fa-info-circle me-3 fs-4"></i>
                                                    <div>Aucun véhicule attribué actuellement à ce chauffeur.</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($roleAccess->hasPermission('historique')): ?>
                        <!-- Onglet Historique des trajets -->
                        <div class="tab-pane fade" id="historique" role="tabpanel" aria-labelledby="historique-tab">
                            <?php if (count($reservations) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="historiqueChauffeursTable">
                                        <thead>
                                            <tr>
                                                <th>Véhicule</th>
                                                <th>Trajet</th>
                                                <th>Date</th>
                                                <th>Distance</th>
                                                <th>Passagers</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservations as $reservation): ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($reservation['marque']) && !empty($reservation['modele'])): ?>
                                                            <?= htmlspecialchars($reservation['marque']) ?> <?= htmlspecialchars($reservation['modele']) ?>
                                                            <div class="small text-muted"><?= htmlspecialchars($reservation['immatriculation']) ?></div>
                                                        <?php else: ?>
                                                            <span class="text-muted">Non spécifié</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($reservation['point_depart']) && !empty($reservation['point_arrivee'])): ?>
                                                            <span data-bs-toggle="tooltip" title="De <?= htmlspecialchars($reservation['point_depart']) ?> à <?= htmlspecialchars($reservation['point_arrivee']) ?>">
                                                                <?= htmlspecialchars(substr($reservation['point_depart'], 0, 15)) ?> → 
                                                                <?= htmlspecialchars(substr($reservation['point_arrivee'], 0, 15)) ?>
                                                                <?php if (strlen($reservation['point_depart']) > 15 || strlen($reservation['point_arrivee']) > 15): ?>
                                                                    ...
                                                                <?php endif; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">Itinéraire non spécifié</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= date('d/m/Y', strtotime($reservation['date_depart'])) ?>
                                                        <div class="small text-muted">
                                                            <?= (!empty($reservation['duree_heures'])) ? $reservation['duree_heures'] . 'h' : '-' ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if (!empty($reservation['distance_parcourue'])) {
                                                            echo number_format($reservation['distance_parcourue'], 0, ',', ' ') . ' km';
                                                        } elseif (!empty($reservation['distance_prevue'])) {
                                                            echo number_format($reservation['distance_prevue'], 0, ',', ' ') . ' km (prévus)';
                                                        } else {
                                                            echo '<span class="text-muted">-</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= $reservation['nombre_passagers'] ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = 'bg-secondary';
                                                        $status_text = 'Non défini';

                                                        switch($reservation['statut']) {
                                                            case 'en_attente':
                                                                $status_class = 'bg-warning';
                                                                $status_text = 'En attente';
                                                                break;
                                                            case 'validee':
                                                                $status_class = 'bg-info';
                                                                $status_text = 'Validée';
                                                                break;
                                                            case 'en_cours':
                                                                $status_class = 'bg-primary';
                                                                $status_text = 'En cours';
                                                                break;
                                                            case 'terminee':
                                                                $status_class = 'bg-success';
                                                                $status_text = 'Terminée';
                                                                break;
                                                            case 'annulee':
                                                                $status_class = 'bg-danger';
                                                                $status_text = 'Annulée';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="includes/chauffeurs/reservation_details.php?id=<?= $reservation['id_reservation'] ?>" class="btn btn-sm btn-info" title="Voir les détails">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="fas fa-info-circle me-3 fs-4"></i>
                                    <div>Aucun historique de trajet disponible pour ce chauffeur.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Onglet Documents -->
                        <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                            <?php if ($roleAccess->hasPermission('form')): ?>
                            <div class="d-flex justify-content-end mb-3">
                                <a href="ajouter_document.php?type=chauffeur&id=<?= $id_chauffeur ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus me-1"></i>Ajouter un document
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (count($documents) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="documentsChauffeursTable">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Numéro</th>
                                                <th>Date d'émission</th>
                                                <th>Date d'expiration</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($documents as $document): ?>
                                                <?php
                                                // Déterminer le statut du document et la classe CSS correspondante
                                                $doc_status_class = '';
                                                switch ($document['statut']) {
                                                    case 'valide':
                                                        $doc_status_class = 'bg-success';
                                                        break;
                                                    case 'expire':
                                                        $doc_status_class = 'bg-danger';
                                                        break;
                                                    case 'a_renouveler':
                                                        $doc_status_class = 'bg-warning';
                                                        break;
                                                    default:
                                                        $doc_status_class = 'bg-secondary';
                                                }
                                                
                                                // Formater le type de document
                                                $doc_type_labels = [
                                                    'carte_transport' => 'Carte de transport',
                                                    'carte_grise' => 'Carte grise',
                                                    'visite_technique' => 'Visite technique',
                                                    'assurance' => 'Assurance',
                                                    'carte_stationnement' => 'Carte de stationnement'
                                                ];
                                                $doc_type = isset($doc_type_labels[$document['type_document']]) 
                                                    ? $doc_type_labels[$document['type_document']] 
                                                    : ucfirst(str_replace('_', ' ', $document['type_document']));
                                                
                                                // Formater le statut
                                                $doc_status_labels = [
                                                    'valide' => 'Valide',
                                                    'expire' => 'Expiré',
                                                    'a_renouveler' => 'À renouveler'
                                                ];
                                                $doc_status = isset($doc_status_labels[$document['statut']]) 
                                                    ? $doc_status_labels[$document['statut']] 
                                                    : ucfirst($document['statut']);
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($doc_type) ?></td>
                                                    <td><?= htmlspecialchars($document['numero_document']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($document['date_emission'])) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($document['date_expiration'])) ?></td>
                                                    <td><span class="badge <?= $doc_status_class ?>"><?= $doc_status ?></span></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <?php if (!empty($document['fichier_url'])): ?>
                                                                <a href="<?= htmlspecialchars($document['fichier_url']) ?>" class="btn btn-sm btn-info" target="_blank">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($roleAccess->hasPermission('modifyRequest')): ?>
                                                            <a href="edit_document.php?id=<?= $document['id_document'] ?>" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($roleAccess->hasPermission('deleteHistorique')): ?>
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="confirmDeleteDocument(<?= $document['id_document'] ?>, '<?= $doc_type ?>')">
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
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucun document administratif n'a été enregistré pour ce chauffeur.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($roleAccess->hasPermission('tracking')): ?>
                        <!-- Onglet Consommation de carburant -->
                        <div class="tab-pane fade" id="consommation" role="tabpanel" aria-labelledby="consommation-tab">
                            <?php if (count($consommations) > 0): ?>
                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Résumé de consommation</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <?php 
                                                    $total_cout_global = 0;
                                                    $total_litres_global = 0;
                                                    $total_approvisionnements = 0;
                                                    
                                                    foreach ($consommations as $consommation) {
                                                        $total_cout_global += $consommation['total_cout'];
                                                        $total_litres_global += $consommation['total_litres'];
                                                        $total_approvisionnements += $consommation['nb_approvisionnements'];
                                                    }
                                                    ?>
                                                    <div class="col-md-6 my-3">
                                                        <div class="fuel-stat">
                                                            <div class="fuel-stat-icon bg-primary">
                                                                <i class="fas fa-gas-pump"></i>
                                                            </div>
                                                            <div class="fuel-stat-info">
                                                                <h6>Total carburant</h6>
                                                                <h3><?= number_format($total_litres_global, 2, ',', ' ') ?> L</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6 my-3">
                                                        <div class="fuel-stat">
                                                            <div class="fuel-stat-icon bg-success">
                                                                <i class="fas fa-money-bill-wave"></i>
                                                            </div>
                                                            <div class="fuel-stat-info">
                                                                <h6>Coût total</h6>
                                                                <h3><?= number_format($total_cout_global, 0, ',', ' ') ?> FCFA</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6 my-3">
                                                        <div class="fuel-stat">
                                                            <div class="fuel-stat-icon bg-info">
                                                                <i class="fas fa-tachometer-alt"></i>
                                                            </div>
                                                            <div class="fuel-stat-info">
                                                                <h6>Consommation</h6>
                                                                <?php
                                                                // Calculer la consommation moyenne en L/100km
                                                                $consommation_moyenne = ($total_km_parcourus > 0 && $total_litres_global > 0) 
                                                                    ? ($total_litres_global / $total_km_parcourus) * 100 
                                                                    : 0;
                                                                ?>
                                                                <h3><?= number_format($consommation_moyenne, 2, ',', ' ') ?> L/100km</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6 my-3">
                                                        <div class="fuel-stat">
                                                            <div class="fuel-stat-icon bg-warning">
                                                                <i class="fas fa-fill-drip"></i>
                                                            </div>
                                                            <div class="fuel-stat-info">
                                                                <h6>Approvisionnements</h6>
                                                                <h3><?= $total_approvisionnements ?></h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0"><i class="fas fa-car me-2"></i>Détails par véhicule</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover" id="consommationTable">
                                                        <thead>
                                                            <tr>
                                                                <th>Véhicule</th>
                                                                <th>Litres</th>
                                                                <th>Coût total</th>
                                                                <th>Prix moyen/litre</th>
                                                                <th>Nb approvisionnements</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($consommations as $consommation): ?>
                                                                <tr>
                                                                    <td>
                                                                        <?= htmlspecialchars($consommation['marque']) ?> <?= htmlspecialchars($consommation['modele']) ?>
                                                                        <div class="small text-muted"><?= htmlspecialchars($consommation['immatriculation']) ?></div>
                                                                    </td>
                                                                    <td><?= number_format($consommation['total_litres'], 2, ',', ' ') ?> L</td>
                                                                    <td><?= number_format($consommation['total_cout'], 0, ',', ' ') ?> FCFA</td>
                                                                    <td><?= number_format($consommation['prix_moyen_litre'], 0, ',', ' ') ?> FCFA</td>
                                                                    <td><?= $consommation['nb_approvisionnements'] ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucune donnée de consommation de carburant disponible pour ce chauffeur.
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression de document -->
<?php if ($roleAccess->hasPermission('deleteHistorique')): ?>
<div class="modal fade" id="deleteDocumentModal" tabindex="-1" aria-labelledby="deleteDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteDocumentModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmation de suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteDocumentModalBody">
                Êtes-vous sûr de vouloir supprimer ce document?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="#" id="confirmDeleteDocumentBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- CSS personnalisé -->
<style>
.chauffeur-profile-pic {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border: 5px solid #fff;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.chauffeur-profile-placeholder {
    width: 150px;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 50px;
    margin: 0 auto;
}

.permis-image {
    max-height: 300px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.info-item i {
    font-size: 20px;
    min-width: 30px;
    text-align: center;
    margin-top: 4px;
}

.info-item h6 {
    margin-bottom: 2px;
    font-weight: 600;
}

.info-item p {
    margin-bottom: 0;
}

.permis-info .info-item {
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding-bottom: 12px;
}

.permis-info .info-item:last-child {
    border-bottom: none;
}

.stat-icon {
    font-size: 36px;
    opacity: 0.7;
}

.vehicle-icon {
    font-size: 36px;
    color: #3498db;
    background-color: rgba(52, 152, 219, 0.1);
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.fuel-stat {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap : wrap;
}

.fuel-stat-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    border-radius: 50%;
}

.fuel-stat-info h6 {
    margin-bottom: 5px;
    font-weight: 600;
    color: #6c757d;
}

.fuel-stat-info h3 {
    margin-bottom: 0;
    font-weight: 700;
}
</style>

<!-- JavaScript pour la page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialisation des tables pour l'exportation
    if (document.getElementById('historiqueChauffeursTable')) {
        new DataTable('#historiqueChauffeursTable', {
            language: {
                url: 'assets/js/fr-FR.json'
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'excel', 'pdf', 'print'
            ]
        });
    }
    
    if (document.getElementById('documentsChauffeursTable')) {
        new DataTable('#documentsChauffeursTable', {
            language: {
                url: 'assets/js/fr-FR.json'
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'excel', 'pdf', 'print'
            ]
        });
    }
    
    if (document.getElementById('consommationTable')) {
        new DataTable('#consommationTable', {
            language: {
                url: 'assets/js/fr-FR.json'
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'excel', 'pdf', 'print'
            ]
        });
    }
    
    <?php if ($roleAccess->hasPermission('deleteHistorique')): ?>
    // Fonction pour la suppression d'un document
    window.confirmDeleteDocument = function(id, type) {
        document.getElementById('deleteDocumentModalBody').innerHTML = 
            `Êtes-vous sûr de vouloir supprimer le document "${type}" ? Cette action est irréversible.`;
        document.getElementById('confirmDeleteDocumentBtn').href = 
            `actions/documents/supprimer_document.php?id=${id}&redirect=chauffeur_details.php?id=<?= $id_chauffeur ?>`;
        
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteDocumentModal'));
        deleteModal.show();
    };
    <?php endif; ?>
    
    // Gestion de l'impression des détails du chauffeur
    document.getElementById('btnPrintChauffeurDetails').addEventListener('click', function() {
        window.print();
    });
});
</script>

<script src="assets/js/chauffeurs/alert_handler.js"></script>
<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer   -->