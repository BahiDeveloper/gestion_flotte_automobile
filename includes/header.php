<?php
// Assurez-vous que la session est démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    if (!isset($_SESSION['role'])) {
        header('Location: auth/views/login.php');
        exit;
    }

    include_once("includes" . DIRECTORY_SEPARATOR . "RoleAccess.php");
    $roleAccess = new RoleAccess($_SESSION['role']);
}
// Au début du header.php
require_once dirname(__DIR__) . '/includes/config.php';
// Définir les chemins de base
$base_url = PROJECT_ROOT;

// $title = 'Gestion des véhicules';
?>

<?php include_once("alerts" . DIRECTORY_SEPARATOR . "alert_vehicule.php"); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gestion des véhicules' ?></title>

    <link rel="icon" type="image/png" href="<?= $base_url ?>/assets/images/favicons/favicon-3.png" class="favicon">

    <!-- Ajouter ces liens dans l'en-tête -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

    <!-- Bootstrap CSS (version la plus récente stable) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert CSS (alertes interactives) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- DataTables CSS (pour les tables dynamiques) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- Chart.js (pour les graphiques) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.8.0/dist/chart.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">

    <!-- Fichiers JavaScript nécessaires pour DataTables et autres fonctionnalités -->
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- ApexCharts (optionnel) -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <!-- ----------------- jquery ------------  -->
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">

    <!-- Custom CSS (vos styles personnalisés) -->
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">

    <link rel="stylesheet" href="assets/css/responsive-mobile.css">
    <!-- <link rel="stylesheet" href="assets/css/notifications.css"> -->
</head>

<style>
    /* Animation de pulsation pour les nouvelles notifications */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }

        100% {
            transform: scale(1);
        }
    }

    .notification-icon-container {
        position: relative;
        display: inline-block;
        padding: 5px;
    }

    .notification-badge {
        position: absolute;
        top: -3px;
        right: -3px;
        font-size: 0.65rem;
        padding: 0.25rem 0.4rem;
        min-width: 18px;
        min-height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .pulse-animation {
        animation: pulse 0.7s infinite;
    }

    /* Style pour la dropdown des notifications */
    .notification-dropdown {
        width: 320px;
        padding: 0;
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 0.5rem 1rem;
    }

    .notification-item {
        border-left: 3px solid transparent;
        transition: background-color 0.2s ease;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
    }

    .notification-icon {
        margin-right: 15px;
    }

    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Style pour les notifications non lues */
    .notification-item.unread {
        background-color: rgba(13, 110, 253, 0.05);
        border-left-color: #0d6efd;
        font-weight: 600;
    }

    /* Style pour les notifications lues */
    .notification-item.read {
        background-color: transparent;
        opacity: 0.8;
        font-weight: normal;
    }

    .notifications-container .d-flex.align-items-center {
        overflow: auto !important;
    }

    /* Animation d'entrée pour les nouvelles notifications */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notification-new {
        animation: fadeIn 0.3s ease-out;
    }

    /* Styles spécifiques pour les différents types de notifications */
    .icon-circle.bg-reservation {
        background-color: #ffc107;
        /* Jaune pour les réservations */
    }

    .icon-circle.bg-deplacement {
        background-color: #28a745;
        /* Vert pour les déplacements */
    }

    .icon-circle.bg-maintenance {
        background-color: #dc3545;
        /* Rouge pour les maintenances */
    }

    .icon-circle.bg-document {
        background-color: #007bff;
        /* Bleu pour les documents */
    }

    /* Ajout de style pour l'en-tête des notifications */
    .notification-dropdown .dropdown-header {
        font-size: 0.8rem;
        color: #6c757d;
        padding: 0.5rem 1rem;
        margin-top: 0;
        margin-bottom: 0;
        background-color: #f8f9fa;
    }

    /* Style pour le bouton "Tout marquer comme lu" */
    .notification-header .btn-outline-primary {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
    }

    /* Responsive fixes */
    @media (max-width: 576px) {
        .notification-dropdown {
            width: 280px;
        }
    }
</style>

<body data-user-role="<?= htmlspecialchars($_SESSION['role'] ?? '') ?>">
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="<?= $base_url ?>/index.php">
                <img src="<?= $base_url ?>/assets/images/logo-2.png" class="logo-site" alt="Logo de gestion de flotte">
                Gestion de Flotte
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto my_ul">
                    <?php if (isset($_SESSION['id_utilisateur'])): ?>
                        <!-- Si l'utilisateur est connecté -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>/index.php"><i class="fas fa-home"></i> Accueil</a>
                        </li>
                        <?php if ($_SESSION['role'] === 'administrateur'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_url ?>/auth/views/dashboard.php"><i
                                        class="fas fa-tachometer-alt"></i>
                                    Tableau de bord</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_url ?>/auth/views/admin/users_list.php"><i
                                        class="fas fa-users-cog"></i>
                                    Gestion des utilisateurs</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($_SESSION['nom'] . ' ' . $_SESSION['prenom']) ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="<?= $base_url ?>/profile.php"><i
                                            class="fas fa-user-circle"></i> Mon
                                        profil</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?= $base_url ?>/auth/views/logout.php"><i
                                            class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Si l'utilisateur n'est pas connecté -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>/auth/views/login.php"><i
                                    class="fas fa-sign-in-alt"></i> Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>/auth/views/register.php"><i
                                    class="fas fa-user-plus"></i>
                                Inscription</a>
                        </li>
                    <?php endif; ?>

                    <!-- À placer juste avant la fermeture de la balise </ul> du menu de navigation principal -->
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'administrateur' || $_SESSION['role'] === 'gestionnaire' || $_SESSION['role'] === 'validateur')): ?>
                        <li class="nav-item dropdown mx-2 p-1">
                            <a class="nav-link position-relative p-0 mt-1" href="#" id="notificationsDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="notification-icon-container">
                                    <i class="fas fa-bell fa-lg"></i>
                                    <span
                                        class="position-absolute top-0 start-100 badge rounded-pill bg-danger notification-badge"
                                        style="display: none;">0</span>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow-lg border-0"
                                aria-labelledby="notificationsDropdown">
                                <li class="notification-header py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0 fw-bold text-primary">Notifications</h6>
                                        <div>
                                            <span class="badge bg-primary rounded-pill me-2" style="display: none;">0</span>
                                            <button id="markAllAsReadBtn" class="btn btn-sm btn-outline-primary"
                                                style="display: none;">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>
                                <li class="notifications-container">
                                    <div class="dropdown-item text-center text-muted py-3">
                                        <i class="fas fa-spinner fa-spin me-1"></i> Chargement des notifications...
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>
                                <li>
                                    <a class="dropdown-item text-center text-primary small py-2"
                                        href="<?= $base_url ?>/planification.php?tab=validation">
                                        <i class="fas fa-arrow-right me-1"></i> Voir toutes les réservations
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container my-5">