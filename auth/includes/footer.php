</main>
        
</div>

<!-- Footer -->
<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <span class="text-muted">
                    © <?= date('Y') ?> Gestion de Flotte. Tous droits réservés.
                </span>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span class="text-muted">
                    Version 1.0.0
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrateur'): ?>
                        | <a href="/admin/logs.php" class="text-muted text-decoration-none">
                            <i class="fas fa-history me-1"></i>Journal d'activités
                        </a>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts communs -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Activer tous les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Activer tous les popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Configuration globale pour SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Gérer les messages de succès/erreur
        <?php if (isset($_SESSION['success'])): ?>
            Toast.fire({
                icon: 'success',
                title: <?= json_encode($_SESSION['success']) ?>
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Toast.fire({
                icon: 'error',
                title: <?= json_encode($_SESSION['error']) ?>
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // Configurer les DataTables
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            pageLength: 10,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy"></i> Copier',
                    className: 'btn btn-sm btn-secondary'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-sm btn-success'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-sm btn-danger'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimer',
                    className: 'btn btn-sm btn-info'
                }
            ]
        });

        // Fonction pour confirmer une action
        window.confirmAction = function (message, callback) {
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, confirmer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        };

        // Fonction pour afficher une notification
        window.showNotification = function (message, type = 'info') {
            Toast.fire({
                icon: type,
                title: message
            });
        };

        // Gérer le mode sombre
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function () {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
            });

            // Appliquer le mode sombre au chargement si activé
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        }
    });
</script>

<script src="<?= $base_url ?>/auth/assets/js/customer.js"></script>
<script src="<?= $base_url ?>/assets/js/planification/notifications.js"></script>
</body>
</html>