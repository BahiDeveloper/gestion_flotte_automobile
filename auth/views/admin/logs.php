<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../controllers/auth_controller.php';
AuthController::requireRole('administrateur');

$title = "Journal d'activités";
require_once '../../includes/header.php';

// Paramètres de pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
$offset = ($page - 1) * $limit;

// Filtres
$type = isset($_GET['type']) ? $_GET['type'] : '';
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
$utilisateur = isset($_GET['utilisateur']) ? $_GET['utilisateur'] : '';

// Construction de la requête SQL
$sql = "SELECT ja.*, u.nom, u.prenom, u.email 
        FROM journal_activites ja 
        LEFT JOIN utilisateurs u ON ja.id_utilisateur = u.id_utilisateur 
        WHERE 1=1";
$params = [];

if ($type) {
    $sql .= " AND ja.type_activite = :type";
    $params[':type'] = $type;
}

if ($date_debut) {
    $sql .= " AND DATE(ja.date_activite) >= :date_debut";
    $params[':date_debut'] = $date_debut;
}

if ($date_fin) {
    $sql .= " AND DATE(ja.date_activite) <= :date_fin";
    $params[':date_fin'] = $date_fin;
}

if ($utilisateur) {
    $sql .= " AND (u.nom LIKE :utilisateur OR u.prenom LIKE :utilisateur OR u.email LIKE :utilisateur)";
    $params[':utilisateur'] = "%$utilisateur%";
}

// Récupérer le nombre total d'enregistrements
$countSql = str_replace("ja.*, u.nom, u.prenom, u.email", "COUNT(*) as total", $sql);
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $limit);

// Ajout de l'ordre et de la limite
$sql .= " ORDER BY ja.date_activite DESC LIMIT :offset, :limit";
$params[':offset'] = $offset;
$params[':limit'] = $limit;

// Exécution de la requête principale
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    if ($key == ':offset' || $key == ':limit') {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
}
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les types d'activités uniques
try {
    $typesStmt = $pdo->query("SELECT DISTINCT type_activite FROM journal_activites ORDER BY type_activite");
    $types = $typesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des types d'activités : " . $e->getMessage());
    $types = [];
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>
                <i class="fas fa-history me-2"></i>Journal d'activités
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-danger" onclick="confirmClearLogs()">
                <i class="fas fa-trash me-2"></i>Vider le journal
            </button>
            <button type="button" class="btn btn-success" onclick="exportLogs()">
                <i class="fas fa-file-export me-2"></i>Exporter
            </button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filtres
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Type d'activité</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tous</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>" <?= $type === $t ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($t)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="date_debut" class="form-label">Date début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                        value="<?= htmlspecialchars($date_debut) ?>">
                </div>

                <div class="col-md-3">
                    <label for="date_fin" class="form-label">Date fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                        value="<?= htmlspecialchars($date_fin) ?>">
                </div>

                <div class="col-md-3">
                    <label for="utilisateur" class="form-label">Utilisateur</label>
                    <input type="text" class="form-control" id="utilisateur" name="utilisateur"
                        value="<?= htmlspecialchars($utilisateur) ?>" placeholder="Nom, prénom ou email">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrer
                    </button>
                    <a href="logs.php" class="btn btn-secondary">
                        <i class="fas fa-undo me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des activités -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Adresse IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i:s', strtotime($log['date_activite'])) ?></td>
                                <td>
                                    <?php if ($log['nom'] && $log['prenom']): ?>
                                        <?= htmlspecialchars($log['nom'] . ' ' . $log['prenom']) ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($log['email']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Utilisateur supprimé</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = match ($log['type_activite']) {
                                        'connexion' => 'bg-success',
                                        'deconnexion' => 'bg-danger',
                                        'modification' => 'bg-warning',
                                        'suppression' => 'bg-danger',
                                        default => 'bg-info'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst(htmlspecialchars($log['type_activite'])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navigation des pages">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?page=<?= $page - 1 ?>&type=<?= urlencode($type) ?>&date_debut=<?= urlencode($date_debut) ?>&date_fin=<?= urlencode($date_fin) ?>&utilisateur=<?= urlencode($utilisateur) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $i ?>&type=<?= urlencode($type) ?>&date_debut=<?= urlencode($date_debut) ?>&date_fin=<?= urlencode($date_fin) ?>&utilisateur=<?= urlencode($utilisateur) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?page=<?= $page + 1 ?>&type=<?= urlencode($type) ?>&date_debut=<?= urlencode($date_debut) ?>&date_fin=<?= urlencode($date_fin) ?>&utilisateur=<?= urlencode($utilisateur) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialisation de DataTables
        const table = $('table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[0, 'desc']],
            pageLength: 50,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy"></i> Copier',
                    className: 'btn btn-secondary'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimer',
                    className: 'btn btn-info'
                }
            ]
        });

        // Fonction pour vider les logs
        window.confirmClearLogs = function () {
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action va supprimer définitivement tous les logs !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, vider',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ici, ajoutez la logique pour vider les logs
                    Swal.fire(
                        'Succès !',
                        'Le journal a été vidé.',
                        'success'
                    );
                }
            });
        };

        // Fonction pour exporter les logs
        window.exportLogs = function () {
            // Utilisez les boutons DataTables existants
            table.button('.buttons-excel').trigger();
        };
    });
</script>

<?php require_once '../../includes/footer.php'; ?>