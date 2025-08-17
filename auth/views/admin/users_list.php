<?php
// Au début du fichier users_list.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../controllers/auth_controller.php';
AuthController::requireRole('administrateur');

// Récupérer les utilisateurs
$users = [];
if (isset($_SESSION['users'])) {
    $users = $_SESSION['users'];
    unset($_SESSION['users']); // Nettoyage de la session
} else {
    // Si les utilisateurs ne sont pas dans la session, les charger directement
    require_once '../../config/db_connect.php';
    $stmt = $pdo->query("
        SELECT id_utilisateur, nom, prenom, email, telephone, role, date_creation, actif 
        FROM utilisateurs 
        ORDER BY date_creation DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Débogage temporaire
error_log("Nombre d'utilisateurs dans la vue : " . count($users));

$title = "Gestion des utilisateurs";
require_once '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="fas fa-users me-2"></i>Gestion des utilisateurs</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="user_add.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Liste des utilisateurs</h5>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" id="btnExport">
                            <i class="fas fa-file-export me-1"></i>Exporter
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnPrint">
                            <i class="fas fa-print me-1"></i>Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id_utilisateur']) ?></td>
                                <td><?= htmlspecialchars($user['nom']) ?></td>
                                <td><?= htmlspecialchars($user['prenom']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars(formatIvorianPhone($user['telephone']) ?? 'Non renseigné') ?></td>
                                <td>
                                    <?php
                                    switch ($user['role']) {
                                        case 'administrateur':
                                            echo '<span class="badge bg-danger">Administrateur</span>';
                                            break;
                                        case 'gestionnaire':
                                            echo '<span class="badge bg-warning">Gestionnaire</span>';
                                            break;
                                        case 'validateur':
                                            echo '<span class="badge bg-primary">Validateur</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-info">Utilisateur</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($user['actif']): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($user['date_creation'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="../../controllers/user_controller.php?action=edit&id=<?= $user['id_utilisateur'] ?>"
                                            class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id_utilisateur'] != $_SESSION['id_utilisateur']): ?>
                                            <?php if ($user['actif']): ?>
                                                <button type="button" class="btn btn-sm btn-secondary"
                                                    onclick="confirmDisable(<?= $user['id_utilisateur'] ?>, '<?= htmlspecialchars($user['nom']) ?> <?= htmlspecialchars($user['prenom']) ?>')"
                                                    title="Désactiver">
                                                    <i class="fas fa-user-slash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-success"
                                                    onclick="confirmEnable(<?= $user['id_utilisateur'] ?>, '<?= htmlspecialchars($user['nom']) ?> <?= htmlspecialchars($user['prenom']) ?>')"
                                                    title="Activer">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <!-- Ajout du bouton de suppression -->
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete(<?= $user['id_utilisateur'] ?>, '<?= htmlspecialchars($user['nom']) ?> <?= htmlspecialchars($user['prenom']) ?>')"
                                                title="Supprimer">
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
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="#" class="btn btn-danger" id="confirmBtn">Confirmer</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialiser DataTable
        const table = $('#usersTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[0, 'desc']],
            pageLength: 10,
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

        // Boutons d'export
        $('#btnExport').click(function () {
            table.button('.buttons-excel').trigger();
        });

        $('#btnPrint').click(function () {
            table.button('.buttons-print').trigger();
        });
    });

    // Fonction de confirmation de désactivation
    function confirmDisable(userId, userName) {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        document.querySelector('#confirmModal .modal-title').innerHTML =
            '<i class="fas fa-user-slash text-danger me-2"></i>Désactiver un utilisateur';
        document.querySelector('#confirmModal .modal-body').innerHTML =
            `Êtes-vous sûr de vouloir désactiver l'utilisateur <strong>${userName}</strong> ?`;
        document.getElementById('confirmBtn').className = 'btn btn-success';
        document.getElementById('confirmBtn').innerHTML =
            '<i class="fas fa-user-check me-2"></i>Activer';
        modal.show();
    }

    // Filtres de recherche avancés
    document.querySelectorAll('.filter-input').forEach(input => {
        input.addEventListener('input', function () {
            const column = table.column(this.dataset.column);
            column.search(this.value).draw();
        });
    });


    // Gestion des notifications temps réel avec WebSocket (si implémenté)
    function initWebSocket() {
        if ('WebSocket' in window) {
            const ws = new WebSocket('ws://your-websocket-server');

            ws.onmessage = function (event) {
                const data = JSON.parse(event.data);
                if (data.type === 'user_update') {
                    // Rafraîchir la table ou mettre à jour la ligne spécifique
                    window.location.reload();
                }
            };

            ws.onerror = function (error) {
                console.error('WebSocket Error:', error);
            };
        }
    }

    // Fonction pour mettre à jour le statut en temps réel
    function updateUserStatus(userId, status) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (row) {
            const statusBadge = row.querySelector('.status-badge');
            statusBadge.className = `badge ${status ? 'bg-success' : 'bg-secondary'}`;
            statusBadge.textContent = status ? 'Actif' : 'Inactif';
        }
    }

    // Gestion des exports personnalisés
    document.getElementById('customExport').addEventListener('click', function () {
        const selectedColumns = Array.from(document.querySelectorAll('.export-column:checked'))
            .map(checkbox => parseInt(checkbox.value));

        const exportData = table.data().toArray().map(row =>
            selectedColumns.map(colIndex => row[colIndex])
        );

        // Créer et télécharger le fichier CSV
        const csv = exportData.map(row => row.join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('hidden', '');
        a.setAttribute('href', url);
        a.setAttribute('download', 'export_utilisateurs.csv');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // Gestion du tri multi-colonnes
    let sortColumns = [];
    table.on('click', 'th', function () {
        const columnIndex = table.column(this).index();
        if (!sortColumns.includes(columnIndex)) {
            sortColumns.push(columnIndex);
        }
        table.order(sortColumns.map(col => [col, 'asc'])).draw();
    });

    // Double-clic pour édition rapide
    table.on('dblclick', 'td', function () {
        const cell = table.cell(this);
        const isEditable = cell.index().column in [1, 2, 4]; // Nom, Prénom, Téléphone

        if (isEditable) {
            const originalValue = cell.data();
            const input = document.createElement('input');
            input.value = originalValue;
            input.className = 'form-control form-control-sm';

            input.addEventListener('blur', function () {
                if (input.value !== originalValue) {
                    // Envoyer la mise à jour au serveur
                    updateCellValue(cell.index().row, cell.index().column, input.value);
                }
                cell.data(input.value);
            });

            $(this).html(input);
            input.focus();
        }
    });

    // Fonction pour mettre à jour une cellule
    async function updateCellValue(rowIndex, colIndex, newValue) {
        const userId = table.row(rowIndex).data()[0];
        const fields = ['nom', 'prenom', '', 'telephone'];
        const field = fields[colIndex];

        if (!field) return;

        try {
            const response = await fetch('../../controllers/user_controller.php?action=update_field', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    userId: userId,
                    field: field,
                    value: newValue
                })
            });

            if (!response.ok) throw new Error('Erreur lors de la mise à jour');

            // Afficher une notification de succès
            showNotification('Mise à jour réussie', 'success');
        } catch (error) {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la mise à jour', 'error');
            // Recharger la page pour afficher les données originales
            window.location.reload();
        }
    }

    // Fonction pour afficher les notifications
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Ajouter cette fonction dans la section script existante
    function confirmDelete(userId, userName) {
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Voulez-vous vraiment supprimer l'utilisateur ${userName} ? Cette action est irréversible.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../../controllers/user_controller.php?action=delete&id=${userId}`;
            }
        });
    }

</script>



<?php require_once '../../includes/footer.php'; ?>