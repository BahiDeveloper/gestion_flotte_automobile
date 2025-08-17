<?php
// Vérification si l'ID de réservation est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirection vers la page précédente avec un message d'erreur
    header("Location: javascript:history.back()");
    exit();
}

$id_reservation = intval($_GET['id']);

// Inclure le fichier de configuration de la base de données
include_once(dirname(__FILE__) . "/../../database/config.php");

// Vérification de l'authentification de l'utilisateur
if (session_status() === PHP_SESSION_NONE) {
    session_start();

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['role'])) {
        header('Location: ../../auth/views/login.php');
        exit;
    }
}

// Assurez-vous que l'objet $roleAccess est disponible
if (!isset($roleAccess)) {
    include_once(dirname(__FILE__) . "/../../includes/RoleAccess.php");
    $roleAccess = new RoleAccess($_SESSION['role']);
}

// Requête pour récupérer les détails de la réservation
$query = "SELECT r.*, 
          c.nom as chauffeur_nom, c.prenoms as chauffeur_prenoms, c.telephone as chauffeur_telephone, 
          c.photo_profil as chauffeur_photo,
          v.marque, v.modele, v.immatriculation, v.type_vehicule, v.capacite_passagers,
          u.nom as utilisateur_nom, u.prenom as utilisateur_prenom,
          i.point_depart, i.point_arrivee, i.distance_prevue, i.points_intermediaires,
          (r.km_retour - r.km_depart) as distance_parcourue,
          TIMESTAMPDIFF(MINUTE, r.date_depart, r.date_retour_prevue) as duree_prevue_minutes,
          TIMESTAMPDIFF(MINUTE, COALESCE(r.date_debut_effective, r.date_depart), 
                                COALESCE(r.date_retour_effective, r.date_retour_prevue)) as duree_reelle_minutes
          FROM reservations_vehicules r
          LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
          LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
          LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
          LEFT JOIN itineraires i ON r.id_reservation = i.id_reservation
          WHERE r.id_reservation = :id_reservation";

$stmt = $pdo->prepare($query);
$stmt->execute(['id_reservation' => $id_reservation]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si la réservation existe
if (!$reservation) {
    // Redirection vers la page précédente avec un message d'erreur
    $_SESSION['error_message'] = "La réservation demandée n'existe pas.";
    header("Location: javascript:history.back()");
    exit();
}

// Formater l'affichage du statut de la réservation
$status_class = 'bg-secondary';
$status_text = 'Non défini';

switch ($reservation['statut']) {
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

// Calculer la durée en format lisible
function formatDuration($minutes)
{
    if (!$minutes)
        return '-';

    $hours = floor($minutes / 60);
    $mins = $minutes % 60;

    if ($hours == 0) {
        return "{$mins} min";
    } elseif ($mins == 0) {
        return "{$hours} h";
    } else {
        return "{$hours} h {$mins} min";
    }
}

$duree_prevue = formatDuration($reservation['duree_prevue_minutes']);
$duree_reelle = formatDuration($reservation['duree_reelle_minutes']);

// Formater les points intermédiaires s'ils existent
$points_intermediaires = [];
if (!empty($reservation['points_intermediaires'])) {
    $points_intermediaires = explode(',', $reservation['points_intermediaires']);
}

// Préparer le lien de retour
$return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../gestion_chauffeurs.php';

$title = 'Détails de la réservation #'.$id_reservation;
?>

<!--start header  -->
<?php include_once("../../includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->
    <!-- Custom CSS -->
    <style>
        .reservation-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .reservation-id {
            background-color: #6c757d;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .info-block {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .info-title {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #495057;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            color: #212529;
        }

        .chauffeur-info {
            display: flex;
            align-items: center;
        }

        .chauffeur-photo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .chauffeur-placeholder {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }

        .itinerary-map {
            height: 300px;
            background-color: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .route-point {
            border-left: 3px solid #6c757d;
            padding-left: 20px;
            margin-bottom: 15px;
            position: relative;
        }

        .route-point:before {
            content: '';
            position: absolute;
            left: -9px;
            top: 0;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: #6c757d;
        }

        .route-point:first-child:before {
            background-color: #28a745;
        }

        .route-point:last-child:before {
            background-color: #dc3545;
        }

        .status-timeline {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }

        .status-timeline:before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            z-index: 1;
        }

        .timeline-point {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .timeline-marker {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }

        .timeline-marker.active {
            background-color: #28a745;
            color: white;
        }

        .timeline-label {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .material-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 10px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .container {
                width: 100%;
                max-width: none;
            }

            .info-block {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>

    <div class="py-5">
        <!-- Boutons d'action -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <a href="<?= $return_url ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>

            <div>
                <button onclick="window.print()" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-print me-1"></i>Imprimer
                </button>
            </div>
        </div>

        <!-- En-tête de la réservation -->
        <div class="reservation-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-3">
                        <span class="reservation-id me-2">#<?= $id_reservation ?></span>
                        Réservation de véhicule
                    </h1>
                    <h5 class="text-muted">
                        <?= htmlspecialchars($reservation['objet_demande'] ?: 'Transport de passagers') ?>
                    </h5>
                </div>

                <div class="col-md-6 text-md-end">
                    <span class="badge <?= $status_class ?> fs-6 p-2 mb-2">
                        <i class="fas fa-circle me-1"></i><?= $status_text ?>
                    </span>
                    <div class="text-muted">
                        Demande enregistrée le <?= date('d/m/Y à H:i', strtotime($reservation['date_demande'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Colonne gauche -->
            <div class="col-md-8">
                <!-- Informations sur l'itinéraire -->
                <div class="info-block">
                    <h4 class="info-title">
                        <i class="fas fa-route me-2 text-primary"></i>Itinéraire
                    </h4>

                    <?php if (!empty($reservation['point_depart']) && !empty($reservation['point_arrivee'])): ?>
                        <!-- Visualisation de l'itinéraire (placeholder) -->
                        <div class="itinerary-map" id="map-canvas" style="height: 300px;"></div>

                        <!-- Points de l'itinéraire -->
                        <div class="route-details">
                            <div class="route-point">
                                <div class="info-label">Point de départ</div>
                                <div class="info-value"><?= htmlspecialchars($reservation['point_depart']) ?></div>
                            </div>

                            <?php foreach ($points_intermediaires as $point): ?>
                                <div class="route-point">
                                    <div class="info-label">Point intermédiaire</div>
                                    <div class="info-value"><?= htmlspecialchars($point) ?></div>
                                </div>
                            <?php endforeach; ?>

                            <div class="route-point">
                                <div class="info-label">Point d'arrivée</div>
                                <div class="info-value"><?= htmlspecialchars($reservation['point_arrivee']) ?></div>
                            </div>
                        </div>

                        <!-- Distances et durées -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-road me-1 text-muted"></i>Distance prévue
                                    </div>
                                    <div class="info-value">
                                        <?= !empty($reservation['distance_prevue']) ? number_format($reservation['distance_prevue'], 1, ',', ' ') . ' km' : '-' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-tachometer-alt me-1 text-muted"></i>Distance parcourue
                                    </div>
                                    <div class="info-value">
                                        <?= !empty($reservation['distance_parcourue']) ? number_format($reservation['distance_parcourue'], 1, ',', ' ') . ' km' : '-' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="far fa-clock me-1 text-muted"></i>Durée prévue
                                    </div>
                                    <div class="info-value"><?= $duree_prevue ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-stopwatch me-1 text-muted"></i>Durée réelle
                                    </div>
                                    <div class="info-value"><?= $duree_reelle ?></div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucune information d'itinéraire disponible pour cette réservation.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Chronologie du statut de la réservation -->
                <div class="info-block">
                    <h4 class="info-title">
                        <i class="fas fa-history me-2 text-primary"></i>Chronologie
                    </h4>

                    <div class="status-timeline">
                        <?php
                        $statuses = [
                            'en_attente' => ['label' => 'En attente', 'icon' => 'fa-clock'],
                            'validee' => ['label' => 'Validée', 'icon' => 'fa-check'],
                            'en_cours' => ['label' => 'En cours', 'icon' => 'fa-car'],
                            'terminee' => ['label' => 'Terminée', 'icon' => 'fa-flag-checkered'],
                        ];

                        $current_status_index = array_search($reservation['statut'], array_keys($statuses));
                        if ($current_status_index === false && $reservation['statut'] == 'annulee') {
                            $current_status_index = -1; // Statut spécial pour annulation
                        }

                        foreach ($statuses as $status_key => $status_info):
                            $is_active = $current_status_index !== false && array_search($status_key, array_keys($statuses)) <= $current_status_index;
                            ?>
                            <div class="timeline-point">
                                <div class="timeline-marker <?= $is_active ? 'active' : '' ?>">
                                    <i class="fas <?= $status_info['icon'] ?>"></i>
                                </div>
                                <div class="timeline-label"><?= $status_info['label'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Dates réelles -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="far fa-calendar-alt me-1 text-muted"></i>Date de départ prévue
                                </div>
                                <div class="info-value">
                                    <?= date('d/m/Y à H:i', strtotime($reservation['date_depart'])) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="far fa-calendar-check me-1 text-muted"></i>Date de retour prévue
                                </div>
                                <div class="info-value">
                                    <?= date('d/m/Y à H:i', strtotime($reservation['date_retour_prevue'])) ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($reservation['date_debut_effective'])): ?>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-play me-1 text-success"></i>Départ effectif
                                    </div>
                                    <div class="info-value">
                                        <?= date('d/m/Y à H:i', strtotime($reservation['date_debut_effective'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($reservation['date_retour_effective'])): ?>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-flag-checkered me-1 text-danger"></i>Retour effectif
                                    </div>
                                    <div class="info-value">
                                        <?= date('d/m/Y à H:i', strtotime($reservation['date_retour_effective'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kilométrage -->
                    <?php if (!empty($reservation['km_depart']) || !empty($reservation['km_retour'])): ?>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-tachometer-alt me-1 text-muted"></i>Kilométrage au départ
                                    </div>
                                    <div class="info-value">
                                        <?= !empty($reservation['km_depart']) ? number_format($reservation['km_depart'], 0, ',', ' ') . ' km' : '-' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-tachometer-alt me-1 text-muted"></i>Kilométrage au retour
                                    </div>
                                    <div class="info-value">
                                        <?= !empty($reservation['km_retour']) ? number_format($reservation['km_retour'], 0, ',', ' ') . ' km' : '-' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Informations supplémentaires et matériel -->
                    <?php if (!empty($reservation['note']) || !empty($reservation['materiel'])): ?>
                        <div class="mt-4">
                            <?php if (!empty($reservation['note'])): ?>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-sticky-note me-1 text-warning"></i>Notes
                                    </div>
                                    <div class="info-value">
                                        <?= nl2br(htmlspecialchars($reservation['note'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($reservation['materiel'])): ?>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-toolbox me-1 text-danger"></i>Matériel transporté
                                    </div>
                                    <div class="material-info">
                                        <?= nl2br(htmlspecialchars($reservation['materiel'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Personnes impliquées -->
                    <?php if (!empty($reservation['acteurs'])): ?>
                        <div class="mt-4">
                            <div class="info-label">
                                <i class="fas fa-users me-1 text-info"></i>Acteurs impliqués
                            </div>
                            <div class="info-value">
                                <?= nl2br(htmlspecialchars($reservation['acteurs'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="col-md-4">
                <!-- Informations sur le demandeur -->
                <div class="info-block">
                    <h4 class="info-title">
                        <i class="fas fa-user me-2 text-primary"></i>Demandeur
                    </h4>

                    <div class="info-item">
                        <div class="info-label">Nom</div>
                        <div class="info-value">
                            <?= htmlspecialchars($reservation['demandeur']) ?>
                        </div>
                    </div>

                    <?php if (!empty($reservation['utilisateur_nom'])): ?>
                        <div class="info-item">
                            <div class="info-label">Enregistré par</div>
                            <div class="info-value">
                                <?= htmlspecialchars($reservation['utilisateur_nom'] . ' ' . $reservation['utilisateur_prenom']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="info-item">
                        <div class="info-label">Nombre de passagers</div>
                        <div class="info-value"><?= $reservation['nombre_passagers'] ?></div>
                    </div>
                </div>

                <!-- Informations sur le chauffeur -->
                <div class="info-block">
                    <h4 class="info-title">
                        <i class="fas fa-id-card me-2 text-primary"></i>Chauffeur
                    </h4>

                    <?php if (!empty($reservation['chauffeur_nom'])): ?>
                        <div class="chauffeur-info mb-3">
                            <?php if (!empty($reservation['chauffeur_photo'])): ?>
                                <img src="../../uploads/chauffeurs/profils/<?= htmlspecialchars($reservation['chauffeur_photo']) ?>"
                                    alt="Photo du chauffeur" class="chauffeur-photo">
                            <?php else: ?>
                                <div class="chauffeur-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>

                            <div>
                                <h5 class="mb-1">
                                    <?= htmlspecialchars($reservation['chauffeur_nom'] . ' ' . $reservation['chauffeur_prenoms']) ?>
                                </h5>
                                <?php if (!empty($reservation['chauffeur_telephone'])): ?>
                                    <div class="text-muted">
                                        <i class="fas fa-phone me-1"></i>
                                        <?= htmlspecialchars($reservation['chauffeur_telephone']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Aucun chauffeur n'a été assigné à cette réservation.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Informations sur le véhicule -->
                <div class="info-block">
                    <h4 class="info-title">
                        <i class="fas fa-car me-2 text-primary"></i>Véhicule
                    </h4>

                    <?php if (!empty($reservation['marque'])): ?>
                        <div class="info-item">
                            <div class="info-label">Véhicule</div>
                            <div class="info-value">
                                <?= htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']) ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Immatriculation</div>
                            <div class="info-value fw-bold">
                                <?= htmlspecialchars($reservation['immatriculation']) ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Type</div>
                            <div class="info-value">
                                <?php
                                $vehicle_types = [
                                    'utilitaire' => 'Utilitaire',
                                    'berline' => 'Berline',
                                    'camion' => 'Camion',
                                    'bus' => 'Bus'
                                ];
                                echo isset($vehicle_types[$reservation['type_vehicule']]) ?
                                    $vehicle_types[$reservation['type_vehicule']] :
                                    ucfirst($reservation['type_vehicule']);
                                ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Capacité</div>
                            <div class="info-value">
                                <?= $reservation['capacite_passagers'] ?> passagers
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Aucun véhicule n'a été assigné à cette réservation.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


<!-- Remplacer le script Google Maps par celui-ci -->
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Coordonnées par défaut (Abidjan)
    const defaultLat = 5.349390;
    const defaultLng = -4.017050;
    
    // Points de départ et d'arrivée
    const startPoint = "<?= htmlspecialchars($reservation['point_depart']) ?>";
    const endPoint = "<?= htmlspecialchars($reservation['point_arrivee']) ?>";
    const intermediatePoints = <?= !empty($points_intermediaires) ? json_encode($points_intermediaires) : '[]' ?>;
    
    // Initialiser la carte
    const map = L.map('map-canvas').setView([defaultLat, defaultLng], 12);
    
    // Ajouter la couche OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    if (startPoint && endPoint) {
        // Utiliser le service de Nominatim pour géocoder les adresses
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(startPoint)}, Côte d'Ivoire`)
            .then(response => response.json())
            .then(startData => {
                if (startData && startData.length > 0) {
                    const startCoords = [parseFloat(startData[0].lat), parseFloat(startData[0].lon)];
                    
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(endPoint)}, Côte d'Ivoire`)
                        .then(response => response.json())
                        .then(endData => {
                            if (endData && endData.length > 0) {
                                const endCoords = [parseFloat(endData[0].lat), parseFloat(endData[0].lon)];
                                
                                // Ajouter les marqueurs
                                L.marker(startCoords).addTo(map)
                                    .bindPopup('Point de départ: ' + startPoint)
                                    .openPopup();
                                
                                L.marker(endCoords).addTo(map)
                                    .bindPopup('Point d\'arrivée: ' + endPoint);
                                
                                // Traiter les points intermédiaires si présents
                                const waypointsPromises = intermediatePoints.map(point => {
                                    return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(point)}, Côte d'Ivoire`)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data && data.length > 0) {
                                                return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                                            }
                                            return null;
                                        });
                                });
                                
                                // Traiter tous les points intermédiaires
                                Promise.all(waypointsPromises).then(waypointCoords => {
                                    const filteredWaypoints = waypointCoords.filter(coords => coords !== null);
                                    
                                    // Ajouter les marqueurs pour les points intermédiaires
                                    filteredWaypoints.forEach((coords, index) => {
                                        L.marker(coords).addTo(map)
                                            .bindPopup('Point intermédiaire ' + (index + 1));
                                    });
                                    
                                    // Préparer tous les points pour l'itinéraire
                                    let routePoints = [L.latLng(startCoords[0], startCoords[1])];
                                    
                                    // Ajouter les points intermédiaires à l'itinéraire
                                    filteredWaypoints.forEach(coords => {
                                        routePoints.push(L.latLng(coords[0], coords[1]));
                                    });
                                    
                                    // Ajouter le point d'arrivée
                                    routePoints.push(L.latLng(endCoords[0], endCoords[1]));
                                    
                                    // Créer l'itinéraire
                                    L.Routing.control({
                                        waypoints: routePoints,
                                        routeWhileDragging: false,
                                        lineOptions: {
                                            styles: [{ color: '#3388ff', weight: 6 }]
                                        },
                                        createMarker: function() { return null; } // Supprimer les marqueurs par défaut de l'itinéraire
                                    }).addTo(map);
                                    
                                    // Ajuster la vue pour montrer tout l'itinéraire
                                    const bounds = L.latLngBounds([startCoords, endCoords, ...filteredWaypoints]);
                                    map.fitBounds(bounds, { padding: [50, 50] });
                                });
                            } else {
                                handleMapError('Point d\'arrivée non trouvé');
                            }
                        })
                        .catch(error => handleMapError('Erreur lors de la géolocalisation: ' + error));
                } else {
                    handleMapError('Point de départ non trouvé');
                }
            })
            .catch(error => handleMapError('Erreur lors de la géolocalisation: ' + error));
    } else {
        handleMapError('Points de départ et/ou d\'arrivée non définis');
    }
    
    function handleMapError(message) {
        // Afficher un message d'erreur
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-warning mt-2';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> ${message}`;
        document.getElementById('map-canvas').after(errorDiv);
    }
});
</script>

<!--start footer  -->
<?php include_once("../../includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer  -->