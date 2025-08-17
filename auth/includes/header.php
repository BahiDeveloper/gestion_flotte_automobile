<?php
// Au début du header.php
require_once dirname(__DIR__) . '/config/config.php';

// Déterminer le niveau du dossier actuel
$current_path = $_SERVER['PHP_SELF'];
$is_admin_page = strpos($current_path, '/auth/views/admin/') !== false;
$is_auth_page = strpos($current_path, '/auth/views/') !== false;

// Définir les chemins de base
$base_url = PROJECT_ROOT;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?>Gestion de Flotte</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $base_url ?>/assets/images/favicons/favicon-4.png" class="favicon">


    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= $base_url ?>/auth/assets/css/style.css" rel="stylesheet">
    <link href="<?= $base_url ?>/auth/assets/css/dashboard.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/notifications.css">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
</head>

<body data-user-role="<?= htmlspecialchars($_SESSION['role'] ?? '') ?>">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= $base_url ?>/index.php">
                <img src="<?= $base_url ?>/assets/images/logo-2.png" class="logo-site" alt="Logo de gestion de flotte">
                Gestion de Flotte
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <?php if (isset($_SESSION['id_utilisateur'])): ?>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav m-auto my_ul">

                        <?php if ($_SESSION['role'] === 'administrateur'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_url ?>/index.php"><i class="fas fa-home"></i> Accueil</a>
                            </li>
                            <!-- Tableau de bord -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_url ?>/auth/views/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>Tableau de bord
                                </a>
                            </li>
                        <?php endif ?>

                        <!-- Menu Véhicules -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-car me-1"></i>Véhicules
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= $base_url ?>/gestion_vehicules.php">
                                        <i class="fas fa-list me-1"></i>Liste des véhicules
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= $base_url ?>/maintenance_vehicule.php">
                                        <i class="fas fa-tools me-1"></i>Maintenance
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Menu Chauffeurs -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-users me-1"></i>Chauffeurs
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= $base_url ?>/gestion_chauffeurs.php">
                                        <i class="fas fa-list me-1"></i>Liste des chauffeurs
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= $base_url ?>/planning_chauffeurs.php">
                                        <i class="fas fa-calendar-alt me-1"></i>Planning
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Documents -->
                         <?php if (isset($_SESSION['role']) && !in_array($_SESSION['role'], ['utilisateur', 'validateur'])):?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>/gestion_documents.php">
                                <i class="fas fa-file-alt me-1"></i>Documents
                            </a>
                        </li>
                        <?php endif ?>

                        <!-- Menu Administration -->
                        <?php if ($_SESSION['role'] === 'administrateur'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cogs me-1"></i>Administration
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?= $base_url ?>/auth/views/admin/users_list.php">
                                            <i class="fas fa-users-cog me-1"></i>Utilisateurs
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= $base_url ?>/auth/views/admin/settings.php">
                                            <i class="fas fa-sliders-h me-1"></i>Paramètres
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= $base_url ?>/auth/views/admin/logs.php">
                                            <i class="fas fa-history me-1"></i>Journal d'activités
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- À placer juste avant la fermeture de la balise </ul> du menu de navigation principal -->
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'administrateur' || $_SESSION['role'] === 'gestionnaire' || $_SESSION['role'] === 'validateur')): ?>
                            <li class="nav-item dropdown mx-2">
                                <a class="nav-link position-relative p-0 mt-1" href="#" id="notificationsDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="notification-icon-container">
                                        <i class="fas fa-bell fa-lg"></i>
                                        <span
                                            class="position-absolute top-0 start-100 badge rounded-pill bg-danger notification-badge"
                                            style="display: none;">
                                            0
                                        </span>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow-lg border-0"
                                    aria-labelledby="notificationsDropdown">
                                    <li class="notification-header py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-bold text-primary">Notifications</h6>
                                            <span class="badge bg-primary rounded-pill" style="display: none;">0</span>
                                        </div>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider my-1">
                                    </li>
                                    <li>
                                        <div class="dropdown-item text-center text-muted py-3">
                                            <i class="fas fa-spinner fa-spin me-1"></i> Chargement des notifications...
                                        </div>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider my-1">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-center text-primary small py-2"
                                            href="planification.php?tab=validation">
                                            <i class="fas fa-arrow-right me-1"></i> Voir toutes les réservations
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Menu utilisateur -->
                    <ul class="navbar-nav ms-auto my_ul">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['nom'] . ' ' . $_SESSION['prenom']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= $base_url ?>/profile.php">
                                        <i class="fas fa-user me-1"></i>Profil
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= $base_url ?>/auth/views/logout.php">
                                        <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Alertes globales -->
    <?php if (isset($_SESSION['global_alert'])): ?>
        <div class="alert alert-<?= $_SESSION['global_alert']['type'] ?> alert-dismissible fade show m-3" role="alert">
            <?= $_SESSION['global_alert']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['global_alert']); ?>
    <?php endif; ?>

    <div class="container">
        <main class="py-4">