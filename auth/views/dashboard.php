<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once('../config/db_connect.php');

$title = "Tableau de bord administrateur";

require_once '../includes/header.php';

// Vérifier que l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'administrateur') {
    $_SESSION['error'] = "Accès non autorisé.";
    header('Location: login.php');
    exit;
}

// Statistiques des utilisateurs
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN role = 'administrateur' THEN 1 ELSE 0 END) as total_admins,
            SUM(CASE WHEN role = 'gestionnaire' THEN 1 ELSE 0 END) as total_managers,
            SUM(CASE WHEN role = 'utilisateur' THEN 1 ELSE 0 END) as total_users,
            SUM(CASE WHEN actif = 1 THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN actif = 0 THEN 1 ELSE 0 END) as inactive_users
        FROM utilisateurs
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = [
        'total_users' => 0,
        'total_admins' => 0,
        'total_managers' => 0,
        'total_users' => 0,
        'active_users' => 0,
        'inactive_users' => 0
    ];
}

// Activités récentes
try {
    $stmt = $pdo->query("
        SELECT ja.*, u.nom, u.prenom
        FROM journal_activites ja
        LEFT JOIN utilisateurs u ON ja.id_utilisateur = u.id_utilisateur
        ORDER BY ja.date_activite DESC
        LIMIT 10
    ");
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_activities = [];
}
?>

<div class="container-fluid py-4">
    <h1 class="text-center mb-4">
        <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord administrateur
    </h1>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Utilisateurs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_users'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Utilisateurs actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['active_users'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Administrateurs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_admins'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Gestionnaires</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_managers'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activités récentes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Activités récentes
                    </h6>
                    <a href="admin/logs.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-list me-1"></i>Voir tout
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($activity['nom'] . ' ' . $activity['prenom']) ?></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                $activity['type_activite'] === 'connexion' ? 'success' :
                                                ($activity['type_activite'] === 'deconnexion' ? 'danger' : 'info')
                                                ?>">
                                                <?= htmlspecialchars($activity['type_activite']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($activity['description']) ?></td>
                                        <td><?= date('d/m/Y H:i:s', strtotime($activity['date_activite'])) ?></td>
                                        <td><?= htmlspecialchars($activity['ip_address']) ?></td>
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

<?php require_once '../includes/footer.php'; ?>