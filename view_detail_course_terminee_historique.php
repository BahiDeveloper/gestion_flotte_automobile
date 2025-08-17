<!--start header  -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "header.php") ?>
<!--end header  -->

<?php
// Récupérer l'ID de la réservation depuis l'URL
$id_reservation = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si pas d'ID valide, rediriger vers la page de planification
if ($id_reservation <= 0) {
    echo '<script>window.location.href = "planification.php#historique";</script>';
    exit;
}

// Inclure explicitement le fichier de configuration
require_once "./database/config.php";

try {
    // Requête pour récupérer les détails de la réservation
    $query = "SELECT 
    r.id_reservation,
    r.date_demande,
    r.date_depart,
    r.date_debut_effective,
    r.date_retour_prevue,
    r.date_retour_effective,
    r.statut,
    r.km_depart,
    r.km_retour,
    r.note,
    r.objet_demande,
    r.materiel,
    r.acteurs,
    r.materiel_retour,
    v.marque,
    v.modele,
    v.immatriculation,
    v.logo_marque_vehicule,
    CONCAT(c.nom, ' ', c.prenoms) as chauffeur_nom,
    c.photo_profil,
    CONCAT(u.nom, ' ', u.prenom) as demandeur_nom,
    i.point_depart,
    i.point_arrivee
FROM 
    reservations_vehicules r
LEFT JOIN vehicules v ON r.id_vehicule = v.id_vehicule
LEFT JOIN chauffeurs c ON r.id_chauffeur = c.id_chauffeur
LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
LEFT JOIN itineraires i ON r.id_reservation = i.id_itineraire
WHERE 
    r.id_reservation = :id_reservation
    AND r.statut IN ('terminee', 'annulee')";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();

    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si pas de résultat, rediriger
    if (!$course) {
        echo '<script>
            alert("Détails de la course non trouvés");
            window.location.href = "planification.php#historique";
        </script>';
        exit;
    }

    // Formatage des données
    $logo = !empty($course['logo_marque_vehicule']) ?
        'uploads/vehicules/logo_marque/' . $course['logo_marque_vehicule'] :
        'uploads/vehicules/logo_marque/default.png';

    $photo_chauffeur = !empty($course['photo_profil']) ?
        'uploads/chauffeurs/profils/' . $course['photo_profil'] :
        'assets/images/profils/default_profile.jpg';

    // Formatage des dates
    $date_demande = !empty($course['date_demande']) ?
        (new DateTime($course['date_demande']))->format('d/m/Y H:i') : '---';

    $date_depart = !empty($course['date_depart']) ?
        (new DateTime($course['date_depart']))->format('d/m/Y H:i') : '---';

    $date_retour_prevue = !empty($course['date_retour_prevue']) ?
        (new DateTime($course['date_retour_prevue']))->format('d/m/Y H:i') : '---';

    $date_debut_effective = !empty($course['date_debut_effective']) ?
        (new DateTime($course['date_debut_effective']))->format('d/m/Y H:i') : '---';

    $date_retour_effective = !empty($course['date_retour_effective']) ?
        (new DateTime($course['date_retour_effective']))->format('d/m/Y H:i') : '---';

    // Calculer la distance parcourue si disponible
    $distance_parcourue = '';
    if (!empty($course['km_depart']) && !empty($course['km_retour']) && $course['statut'] === 'terminee') {
        $distance_parcourue = $course['km_retour'] - $course['km_depart'] . ' km';
    }

    // Formatage du statut
    $statut_libelle = $course['statut'] === 'terminee' ? 'Terminée' : 'Annulée';
    $statut_badge_class = $course['statut'] === 'terminee' ? 'badge-success' : 'badge-danger';

} catch (PDOException $e) {
    // Erreur de récupération des données
    echo '<div class="alert alert-danger">Erreur lors de la récupération des détails: ' . $e->getMessage() . '</div>';
    echo '<p class="text-center mt-3"><a href="planification.php#historique" class="btn btn-primary">Retour à l\'historique</a></p>';
    include_once("includes" . DIRECTORY_SEPARATOR . "footer.php");
    exit;
}
?>

<div class="container my-4">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center flex-wrap">
                <div class="card-img logo_marque_vehicule me-3">
                    <img src="<?php echo $logo; ?>" class="img-fluid rounded-circle" alt="Logo du véhicule">
                </div>
                <h4><?php echo htmlspecialchars($course['marque'] . ' - ' . $course['modele'] . ' | ' . $course['immatriculation']); ?>
                </h4>
            </div>

            <div class="d-flex align-items-center flex-wrap">
                <h5 class="me-3"><?php echo htmlspecialchars($course['chauffeur_nom'] ?? '---'); ?></h5>
                <div class="card-img photo_profil_chauffeur">
                    <img src="<?php echo $photo_chauffeur; ?>" class="img-fluid" alt="Photo du chauffeur">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">Informations générales</h5>
                    <ul class="list-group mb-4">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Date demande:</strong>
                            <span><?php echo $date_demande; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Demandeur:</strong>
                            <span><?php echo htmlspecialchars($course['demandeur_nom'] ?? '---'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Chauffeur:</strong>
                            <span><?php echo htmlspecialchars($course['chauffeur_nom'] ?? '---'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Trajet:</strong>
                            <span>
                                <?php echo htmlspecialchars(($course['point_depart'] ?? '---') . ' - ' . ($course['point_arrivee'] ?? '---')); ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Statut:</strong>
                            <span class="badge <?php echo $statut_badge_class; ?>"><?php echo $statut_libelle; ?></span>
                        </li>
                    </ul>
                </div>

                <div class="col-md-6">
                    <h5 class="card-title">Détails du trajet</h5>
                    <ul class="list-group mb-4">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Date de départ prévue:</strong>
                            <span><?php echo $date_depart; ?></span>
                        </li>
                        <?php if ($date_debut_effective !== '---'): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date de départ effective:</strong>
                                <span><?php echo $date_debut_effective; ?></span>
                            </li>
                        <?php endif; ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Date d'arrivée prévue:</strong>
                            <span><?php echo $date_retour_prevue; ?></span>
                        </li>
                        <?php if ($date_retour_effective !== '---'): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date d'arrivée effective:</strong>
                                <span><?php echo $date_retour_effective; ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if ($course['statut'] === 'terminee'): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Kilométrage départ:</strong>
                                <span><?php echo number_format($course['km_depart'] ?? 0, 0, ',', ' ') . ' km'; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Kilométrage retour:</strong>
                                <span><?php echo number_format($course['km_retour'] ?? 0, 0, ',', ' ') . ' km'; ?></span>
                            </li>
                            <?php if (!empty($distance_parcourue)): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Distance parcourue:</strong>
                                    <span><?php echo $distance_parcourue; ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <hr>
            <div class="row">
                <?php if (!empty($course['note'])): ?>
                    <div class="col-md-6">
                        <h5 class="card-title">Notes</h5>
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($course['note'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($course['objet_demande'])): ?>
                    <div class="col-md-6">
                        <h5 class="card-title">Objet de la demande</h5>
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($course['objet_demande'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($course['acteurs'])): ?>
                    <div class="col-md-6">
                        <h5 class="card-title">Acteurs ayant participé</h5>
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($course['acteurs'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($course['materiel'])): ?>
                    <div class="col-md-6">
                        <h5 class="card-title">Matériel emporté</h5>
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($course['materiel'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($course['materiel_retour'])): ?>
                    <div class="col-md-6">
                        <h5 class="card-title">Matériel retourné</h5>
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($course['materiel_retour'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                // Calcul du temps de trajet réellement effectué si les dates sont disponibles
                if (!empty($course['date_debut_effective']) && !empty($course['date_retour_effective'])):
                    try {
                        $debut = new DateTime($course['date_debut_effective']);
                        $fin = new DateTime($course['date_retour_effective']);
                        $interval = $debut->diff($fin);

                        // Formatage de l'intervalle de temps
                        $duree = '';
                        if ($interval->d > 0) {
                            $duree .= $interval->d . ' jour(s) ';
                        }
                        if ($interval->h > 0) {
                            $duree .= $interval->h . ' heure(s) ';
                        }
                        if ($interval->i > 0) {
                            $duree .= $interval->i . ' minute(s)';
                        }
                        if (empty($duree)) {
                            $duree = 'Moins d\'une minute';
                        }
                        ?>
                        <div class="col-md-6">
                            <h5 class="card-title">Temps de trajet réel</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <span><?php echo htmlspecialchars($duree); ?></span>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        De <?php echo date('d/m/Y H:i', strtotime($course['date_debut_effective'])); ?>
                                        à <?php echo date('d/m/Y H:i', strtotime($course['date_retour_effective'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php
                    } catch (Exception $e) {
                        // Journaliser l'erreur plutôt que de l'afficher à l'utilisateur
                        error_log('Erreur de calcul du temps de trajet réel: ' . $e->getMessage());
                    }
                endif; ?>

                <?php
                // Afficher aussi le temps de trajet estimé à partir des données d'itinéraire
                if (!empty($course['itineraire']) && isset($course['itineraire']['temps_trajet_prevu'])):
                    try {
                        $temps_estime = $course['itineraire']['temps_trajet_prevu'];
                        $heures = floor($temps_estime / 60);
                        $minutes = $temps_estime % 60;
                        $temps_formaté = '';

                        if ($heures > 0) {
                            $temps_formaté .= $heures . ' heure(s) ';
                        }
                        if ($minutes > 0 || $temps_formaté == '') {
                            $temps_formaté .= $minutes . ' minute(s)';
                        }
                        ?>
                        <div class="col-md-6">
                            <h5 class="card-title">Temps de trajet estimé</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-hourglass-half text-info me-2"></i>
                                        <span><?php echo htmlspecialchars($temps_formaté); ?></span>
                                    </div>
                                    <?php if (isset($course['itineraire']['distance_prevue'])): ?>
                                        <small class="text-muted mt-2 d-block">
                                            Distance estimée:
                                            <?php echo htmlspecialchars($course['itineraire']['distance_prevue']); ?> km
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    } catch (Exception $e) {
                        // Journaliser l'erreur plutôt que de l'afficher à l'utilisateur
                        error_log('Erreur de calcul du temps de trajet estimé: ' . $e->getMessage());
                    }
                endif; ?>
            </div>
            <hr>

            <!-- Section pour l'historique d'activité de cette course -->
            <div class="row" id="activitesCourse">
                <div class="col-12">
                    <h5 class="card-title">Historique d'activité</h5>
                    <div class="table-responsive">
                        <table id="tableActivites" class="table table-striped table-bordered display">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Utilisateur</th>
                                </tr>
                            </thead>
                            <tbody id="activitesTableBody">
                                <tr>
                                    <td colspan="4" class="text-center">Chargement de l'historique...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="planification.php#historique" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Retour à l'historique
            </a>

            <?php if ($course['statut'] === 'terminee'): ?>
                <button class="btn btn-success" id="btnExportPDF">
                    <i class="fas fa-file-pdf"></i>
                    Exporter en PDF
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Charger les activités liées à cette course
        const idReservation = <?php echo $id_reservation; ?>;

        fetch(`api/detail-course.php?id=${idReservation}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.activites && data.activites.length > 0) {
                    const activites = data.activites.map(activite => {
                        // Formater le type d'activité pour l'affichage
                        let typeActivite = activite.type_activite;
                        switch (activite.type_activite) {
                            case 'debut_course':
                                typeActivite = 'Début de course';
                                break;
                            case 'fin_course':
                                typeActivite = 'Fin de course';
                                break;
                            case 'annulation_course':
                                typeActivite = 'Annulation';
                                break;
                            case 'modification_reservation':
                                typeActivite = 'Modification';
                                break;
                            // Ajouter d'autres types si nécessaire
                        }

                        return [
                            activite.date_formatee || activite.date_activite,
                            typeActivite,
                            activite.description,
                            activite.utilisateur || '---'
                        ];
                    });

                    // Initialiser DataTables avec les données récupérées
                    $('#tableActivites').DataTable({
                        data: activites,
                        responsive: true,
                        language: {
                            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json"
                        },
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'excel', 'pdf', 'print'
                        ],
                        order: [[0, 'desc']], // Trier par date (première colonne) en ordre décroissant
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tous"]]
                    });
                } else {
                    // Si aucune activité n'est trouvée, initialiser quand même DataTable avec un message
                    $('#tableActivites').DataTable({
                        language: {
                            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json",
                            emptyTable: "Aucune activité trouvée pour cette course"
                        },
                        responsive: true
                    });
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des activités:', error);
                // En cas d'erreur, initialiser DataTable avec un message d'erreur
                $('#tableActivites').DataTable({
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json",
                        emptyTable: "Erreur lors du chargement des activités"
                    },
                    responsive: true
                });
            });

        // Fonction pour exporter les détails de la course en PDF
        document.getElementById('btnExportPDF')?.addEventListener('click', function () {
            // Création d'un élément div temporaire pour le contenu du PDF
            const pdfContent = document.createElement('div');
            pdfContent.className = 'pdf-content';
            pdfContent.style.width = '100%';
            pdfContent.style.padding = '20px';
            pdfContent.style.fontFamily = 'Arial, sans-serif';

            // En-tête du PDF avec le logo et les infos du véhicule
            const header = document.createElement('div');
            header.style.display = 'flex';
            header.style.justifyContent = 'space-between';
            header.style.alignItems = 'center';
            header.style.marginBottom = '20px';
            header.style.borderBottom = '1px solid #ddd';
            header.style.paddingBottom = '10px';

            // Titre du PDF
            const title = document.createElement('h2');
            title.textContent = 'Détails de la course #<?php echo $id_reservation; ?>';
            title.style.color = '#333';
            title.style.margin = '0 0 5px 0';

            // Infos véhicule
            const vehicleInfo = document.createElement('p');
            vehicleInfo.textContent = '<?php echo htmlspecialchars($course['marque'] . ' - ' . $course['modele'] . ' | ' . $course['immatriculation']); ?>';
            vehicleInfo.style.margin = '0';
            vehicleInfo.style.fontSize = '14px';

            // Date d'impression
            const printDate = document.createElement('p');
            printDate.textContent = 'Document généré le ' + new Date().toLocaleDateString('fr-FR') + ' à ' + new Date().toLocaleTimeString('fr-FR');
            printDate.style.fontSize = '12px';
            printDate.style.color = '#666';
            printDate.style.margin = '5px 0 0 0';

            const leftHeader = document.createElement('div');
            leftHeader.appendChild(title);
            leftHeader.appendChild(vehicleInfo);
            leftHeader.appendChild(printDate);

            header.appendChild(leftHeader);
            pdfContent.appendChild(header);

            // Créer la section des informations générales
            const generalInfoSection = document.createElement('div');
            generalInfoSection.style.marginBottom = '20px';

            const generalInfoTitle = document.createElement('h3');
            generalInfoTitle.textContent = 'Informations générales';
            generalInfoTitle.style.borderBottom = '1px solid #eee';
            generalInfoTitle.style.paddingBottom = '5px';
            generalInfoSection.appendChild(generalInfoTitle);

            const generalInfoContent = document.createElement('div');
            generalInfoContent.style.display = 'grid';
            generalInfoContent.style.gridTemplateColumns = 'repeat(2, 1fr)';
            generalInfoContent.style.gap = '10px';

            // Ajouter les informations générales
            const infoItems = [
                { label: 'Date demande', value: '<?php echo $date_demande; ?>' },
                { label: 'Demandeur', value: '<?php echo htmlspecialchars($course['demandeur_nom'] ?? "---"); ?>' },
                { label: 'Chauffeur', value: '<?php echo htmlspecialchars($course['chauffeur_nom'] ?? "---"); ?>' },
                { label: 'Trajet', value: '<?php echo htmlspecialchars(($course['point_depart'] ?? "---") . " - " . ($course['point_arrivee'] ?? "---")); ?>' },
                { label: 'Statut', value: '<?php echo $statut_libelle; ?>' }
            ];

            infoItems.forEach(item => {
                const infoItem = document.createElement('div');
                infoItem.style.padding = '8px';
                infoItem.style.backgroundColor = '#f9f9f9';
                infoItem.style.borderRadius = '4px';

                const label = document.createElement('strong');
                label.textContent = item.label + ': ';

                const value = document.createTextNode(item.value);

                infoItem.appendChild(label);
                infoItem.appendChild(value);
                generalInfoContent.appendChild(infoItem);
            });

            generalInfoSection.appendChild(generalInfoContent);
            pdfContent.appendChild(generalInfoSection);

            // Créer la section des détails du trajet
            const tripDetailsSection = document.createElement('div');
            tripDetailsSection.style.marginBottom = '20px';

            const tripDetailsTitle = document.createElement('h3');
            tripDetailsTitle.textContent = 'Détails du trajet';
            tripDetailsTitle.style.borderBottom = '1px solid #eee';
            tripDetailsTitle.style.paddingBottom = '5px';
            tripDetailsSection.appendChild(tripDetailsTitle);

            const tripDetailsContent = document.createElement('div');
            tripDetailsContent.style.display = 'grid';
            tripDetailsContent.style.gridTemplateColumns = 'repeat(2, 1fr)';
            tripDetailsContent.style.gap = '10px';

            // Ajouter les détails du trajet
            const tripItems = [
                { label: 'Date de départ prévue', value: '<?php echo $date_depart; ?>' },
                { label: 'Date d\'arrivée prévue', value: '<?php echo $date_retour_prevue; ?>' }
            ];

            <?php if ($date_debut_effective !== '---'): ?>
                tripItems.push({ label: 'Date de départ effective', value: '<?php echo $date_debut_effective; ?>' });
            <?php endif; ?>

            <?php if ($date_retour_effective !== '---'): ?>
                tripItems.push({ label: 'Date d\'arrivée effective', value: '<?php echo $date_retour_effective; ?>' });
            <?php endif; ?>

            <?php if ($course['statut'] === 'terminee'): ?>
                tripItems.push(
                    { label: 'Kilométrage départ', value: '<?php echo number_format($course['km_depart'] ?? 0, 0, ',', ' ') . ' km'; ?>' },
                    { label: 'Kilométrage retour', value: '<?php echo number_format($course['km_retour'] ?? 0, 0, ',', ' ') . ' km'; ?>' }
                );

                <?php if (!empty($distance_parcourue)): ?>
                    tripItems.push(
                        { label: 'Distance parcourue', value: '<?php echo $distance_parcourue; ?>' }
                    );
                <?php endif; ?>
            <?php endif; ?>

            tripItems.forEach(item => {
                const tripItem = document.createElement('div');
                tripItem.style.padding = '8px';
                tripItem.style.backgroundColor = '#f9f9f9';
                tripItem.style.borderRadius = '4px';

                const label = document.createElement('strong');
                label.textContent = item.label + ': ';

                const value = document.createTextNode(item.value);

                tripItem.appendChild(label);
                tripItem.appendChild(value);
                tripDetailsContent.appendChild(tripItem);
            });

            tripDetailsSection.appendChild(tripDetailsContent);
            pdfContent.appendChild(tripDetailsSection);

            <?php if (!empty($course['objet_demande'])): ?>
                // Ajouter l'objet de la demande
                const objectSection = document.createElement('div');
                objectSection.style.marginBottom = '20px';

                const objectTitle = document.createElement('h3');
                objectTitle.textContent = 'Objet de la demande';
                objectTitle.style.borderBottom = '1px solid #eee';
                objectTitle.style.paddingBottom = '5px';
                objectSection.appendChild(objectTitle);

                const objectContent = document.createElement('div');
                objectContent.style.padding = '15px';
                objectContent.style.backgroundColor = '#f9f9f9';
                objectContent.style.borderRadius = '4px';
                objectContent.style.whiteSpace = 'pre-wrap';
                objectContent.textContent = '<?php echo addslashes(preg_replace("/\r\n|\r|\n/", "\\n", $course['objet_demande'])); ?>';

                objectSection.appendChild(objectContent);
                pdfContent.appendChild(objectSection);
            <?php endif; ?>

            <?php if (!empty($course['acteurs'])): ?>
    // Ajouter les acteurs
    const acteursSection = document.createElement('div');
    acteursSection.style.marginBottom = '20px';

    const acteursTitle = document.createElement('h3');
    acteursTitle.textContent = 'Acteurs ayant participé';
    acteursTitle.style.borderBottom = '1px solid #eee';
    acteursTitle.style.paddingBottom = '5px';
    acteursSection.appendChild(acteursTitle);

    const acteursContent = document.createElement('div');
    acteursContent.style.padding = '15px';
    acteursContent.style.backgroundColor = '#f9f9f9';
    acteursContent.style.borderRadius = '4px';
    acteursContent.style.whiteSpace = 'pre-wrap';
    acteursContent.textContent = '<?php echo addslashes(preg_replace("/\r\n|\r|\n/", "\\n", $course['acteurs'])); ?>';

    acteursSection.appendChild(acteursContent);
    pdfContent.appendChild(acteursSection);
<?php endif; ?>

<?php if (!empty($course['materiel'])): ?>
    // Ajouter le matériel emporté
    const materielSection = document.createElement('div');
    materielSection.style.marginBottom = '20px';

    const materielTitle = document.createElement('h3');
    materielTitle.textContent = 'Matériel emporté';
    materielTitle.style.borderBottom = '1px solid #eee';
    materielTitle.style.paddingBottom = '5px';
    materielSection.appendChild(materielTitle);

    const materielContent = document.createElement('div');
    materielContent.style.padding = '15px';
    materielContent.style.backgroundColor = '#f9f9f9';
    materielContent.style.borderRadius = '4px';
    materielContent.style.whiteSpace = 'pre-wrap';
    materielContent.textContent = '<?php echo addslashes(preg_replace("/\r\n|\r|\n/", "\\n", $course['materiel'])); ?>';

    materielSection.appendChild(materielContent);
    pdfContent.appendChild(materielSection);
<?php endif; ?>

<?php if (!empty($course['materiel_retour'])): ?>
    // Ajouter le matériel retourné
    const materielRetourSection = document.createElement('div');
    materielRetourSection.style.marginBottom = '20px';

    const materielRetourTitle = document.createElement('h3');
    materielRetourTitle.textContent = 'Matériel retourné';
    materielRetourTitle.style.borderBottom = '1px solid #eee';
    materielRetourTitle.style.paddingBottom = '5px';
    materielRetourSection.appendChild(materielRetourTitle);

    const materielRetourContent = document.createElement('div');
    materielRetourContent.style.padding = '15px';
    materielRetourContent.style.backgroundColor = '#f9f9f9';
    materielRetourContent.style.borderRadius = '4px';
    materielRetourContent.style.whiteSpace = 'pre-wrap';
    materielRetourContent.textContent = '<?php echo addslashes(preg_replace("/\r\n|\r|\n/", "\\n", $course['materiel_retour'])); ?>';

    materielRetourSection.appendChild(materielRetourContent);
    pdfContent.appendChild(materielRetourSection);
<?php endif; ?>

<?php if (!empty($course['note'])): ?>
    // Créer la section des notes
    const notesSection = document.createElement('div');
    notesSection.style.marginBottom = '20px';

    const notesTitle = document.createElement('h3');
    notesTitle.textContent = 'Notes';
    notesTitle.style.borderBottom = '1px solid #eee';
    notesTitle.style.paddingBottom = '5px';
    notesSection.appendChild(notesTitle);

    const notesContent = document.createElement('div');
    notesContent.style.padding = '15px';
    notesContent.style.backgroundColor = '#f9f9f9';
    notesContent.style.borderRadius = '4px';
    notesContent.style.whiteSpace = 'pre-wrap';
    notesContent.textContent = '<?php echo addslashes(preg_replace("/\r\n|\r|\n/", "\\n", $course['note'])); ?>';

    notesSection.appendChild(notesContent);
    pdfContent.appendChild(notesSection);
<?php endif; ?>

            // Créer la section de l'historique d'activité
            const activitiesSection = document.createElement('div');

            const activitiesTitle = document.createElement('h3');
            activitiesTitle.textContent = 'Historique d\'activité';
            activitiesTitle.style.borderBottom = '1px solid #eee';
            activitiesTitle.style.paddingBottom = '5px';
            activitiesSection.appendChild(activitiesTitle);

            // Créer une table pour l'historique
            const activitiesTable = document.createElement('table');
            activitiesTable.style.width = '100%';
            activitiesTable.style.borderCollapse = 'collapse';
            activitiesTable.style.marginTop = '10px';

            // En-tête de la table
            const tableHeader = document.createElement('thead');
            const headerRow = document.createElement('tr');

            ['Date', 'Type', 'Description', 'Utilisateur'].forEach(text => {
                const th = document.createElement('th');
                th.textContent = text;
                th.style.backgroundColor = '#f2f2f2';
                th.style.padding = '8px';
                th.style.border = '1px solid #ddd';
                th.style.textAlign = 'left';
                headerRow.appendChild(th);
            });

            tableHeader.appendChild(headerRow);
            activitiesTable.appendChild(tableHeader);

            // Corps de la table (sera rempli par les données de l'API)
            const tableBody = document.createElement('tbody');
            tableBody.id = 'pdfActivitiesTableBody';
            activitiesTable.appendChild(tableBody);

            activitiesSection.appendChild(activitiesTable);
            pdfContent.appendChild(activitiesSection);

            // Pied de page avec signature
            const footer = document.createElement('div');
            footer.style.marginTop = '30px';
            footer.style.paddingTop = '20px';
            footer.style.borderTop = '1px solid #ddd';

            const signatureSection = document.createElement('div');
            signatureSection.style.display = 'flex';
            signatureSection.style.justifyContent = 'space-between';

            // Zone de signature du chauffeur
            const driverSignature = document.createElement('div');
            driverSignature.style.width = '45%';

            const driverSignatureTitle = document.createElement('p');
            driverSignatureTitle.textContent = 'Signature du chauffeur:';
            driverSignatureTitle.style.marginBottom = '50px';

            driverSignature.appendChild(driverSignatureTitle);

            // Zone de signature du responsable
            const managerSignature = document.createElement('div');
            managerSignature.style.width = '45%';

            const managerSignatureTitle = document.createElement('p');
            managerSignatureTitle.textContent = 'Signature du responsable:';
            managerSignatureTitle.style.marginBottom = '50px';

            managerSignature.appendChild(managerSignatureTitle);

            signatureSection.appendChild(driverSignature);
            signatureSection.appendChild(managerSignature);
            footer.appendChild(signatureSection);

            pdfContent.appendChild(footer);

            // Attachement du contenu temporaire au body pour permettre à html2pdf de le voir
            document.body.appendChild(pdfContent);

            // Charger les activités pour le PDF
            fetch(`api/detail-course.php?id=${<?php echo $id_reservation; ?>}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.activites && data.activites.length > 0) {
                        const tbody = document.getElementById('pdfActivitiesTableBody');
                        tbody.innerHTML = ''; // Vider le tableau

                        data.activites.forEach(activite => {
                            // Formater le type d'activité pour l'affichage
                            let typeActivite = activite.type_activite;
                            switch (activite.type_activite) {
                                case 'debut_course':
                                    typeActivite = 'Début de course';
                                    break;
                                case 'fin_course':
                                    typeActivite = 'Fin de course';
                                    break;
                                case 'annulation_course':
                                    typeActivite = 'Annulation';
                                    break;
                                case 'modification_reservation':
                                    typeActivite = 'Modification';
                                    break;
                                // Ajouter d'autres types si nécessaire
                            }

                            const row = document.createElement('tr');

                            [
                                activite.date_formatee || activite.date_activite,
                                typeActivite,
                                activite.description,
                                activite.utilisateur || '---'
                            ].forEach(text => {
                                const td = document.createElement('td');
                                td.textContent = text;
                                td.style.padding = '8px';
                                td.style.border = '1px solid #ddd';
                                row.appendChild(td);
                            });

                            tbody.appendChild(row);
                        });

                        // Une fois les données chargées, générer le PDF
                        generatePDF(pdfContent);
                    } else {
                        // Aucune activité trouvée
                        const tbody = document.getElementById('pdfActivitiesTableBody');
                        const row = document.createElement('tr');
                        const td = document.createElement('td');
                        td.colSpan = 4;
                        td.textContent = 'Aucune activité trouvée pour cette course';
                        td.style.textAlign = 'center';
                        td.style.padding = '8px';
                        td.style.border = '1px solid #ddd';
                        row.appendChild(td);
                        tbody.appendChild(row);

                        // Générer le PDF même s'il n'y a pas d'activités
                        generatePDF(pdfContent);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des activités pour le PDF:', error);

                    // Afficher un message d'erreur dans le tableau
                    const tbody = document.getElementById('pdfActivitiesTableBody');
                    const row = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 4;
                    td.textContent = 'Erreur lors du chargement des activités';
                    td.style.textAlign = 'center';
                    td.style.padding = '8px';
                    td.style.border = '1px solid #ddd';
                    row.appendChild(td);
                    tbody.appendChild(row);

                    // Générer le PDF malgré l'erreur
                    generatePDF(pdfContent);
                });

            function generatePDF(content) {
                // Configuration de html2pdf
                const options = {
                    margin: 10,
                    filename: 'course_<?php echo $id_reservation; ?>_<?php echo date('Ymd_His'); ?>.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                // Générer le PDF
                html2pdf().from(content).set(options).save()
                    .then(() => {
                        // Nettoyer le DOM en supprimant l'élément temporaire
                        document.body.removeChild(content);
                    });
            }
        });

    });
</script>

<!--start footer   -->
<?php include_once("includes" . DIRECTORY_SEPARATOR . "footer.php") ?>
<!--end footer   -->