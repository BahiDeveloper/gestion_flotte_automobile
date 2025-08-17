// notifications.js
// Système de polling pour les notifications en temps réel

document.addEventListener('DOMContentLoaded', function () {
    // Configuration
    const POLLING_INTERVAL = 10000; // Vérifier toutes les 10 secondes
    let lastNotificationCount = 0;
    let isInitialized = false;

    // Elements DOM
    const notificationBadge = document.querySelector('.notification-badge');
    const notificationDropdown = document.querySelector('.notification-dropdown');

    // Fonction pour charger les notifications
    function checkNotifications() {
        fetch('api/check-notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mise à jour du compteur de notifications
                    updateNotificationBadge(data.count);

                    // Mise à jour du contenu de la dropdown si nécessaire
                    if (data.count !== lastNotificationCount) {
                        updateNotificationContent(data.notifications);
                        lastNotificationCount = data.count;

                        // Afficher une notification visuelle si ce n'est pas la première initialisation
                        if (isInitialized && data.count > 0) {
                            showNewNotificationAlert();
                        }
                    }

                    isInitialized = true;
                }
            })
            .catch(error => {
                console.error('Erreur lors de la vérification des notifications:', error);
            });
    }

    // Mettre à jour le badge de notifications
    function updateNotificationBadge(count) {
        if (notificationBadge) {
            if (count > 0) {
                notificationBadge.textContent = count > 99 ? '99+' : count;
                notificationBadge.style.display = 'block';
            } else {
                notificationBadge.style.display = 'none';
            }
        }
    }

    // Mettre à jour le contenu de la dropdown de notifications
    function updateNotificationContent(notifications) {
        if (notificationDropdown) {
            // Conserver l'en-tête et le pied de la dropdown
            const header = notificationDropdown.querySelector('.notification-header');
            const footer = notificationDropdown.querySelector('a.text-primary');

            // Vider la dropdown sauf pour l'en-tête et le pied
            notificationDropdown.innerHTML = '';

            // Réajouter l'en-tête s'il existe
            if (header) {
                const headerClone = header.cloneNode(true);
                notificationDropdown.appendChild(headerClone);

                // Mettre à jour le compteur dans l'en-tête
                const headerBadge = headerClone.querySelector('.badge');
                if (headerBadge) {
                    headerBadge.textContent = notifications.length;
                    headerBadge.style.display = notifications.length > 0 ? 'block' : 'none';
                }

                // Ajouter un séparateur
                const divider = document.createElement('li');
                divider.innerHTML = '<hr class="dropdown-divider my-1">';
                notificationDropdown.appendChild(divider);
            }

            // Ajouter les notifications ou un message si aucune notification
            if (notifications.length > 0) {
                notifications.forEach(notification => {
                    const notificationItem = document.createElement('li');
                    notificationItem.innerHTML = `
                        <a class="dropdown-item notification-item py-2" href="planification.php?tab=validation">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 notification-icon">
                                    <div class="icon-circle bg-primary">
                                        <i class="fas fa-car-alt text-white"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <div class="fw-bold">${notification.title || 'Réservations en attente'}</div>
                                    <div class="text-muted small">${notification.description || `${notifications.length} réservation(s) à valider`}</div>
                                </div>
                            </div>
                        </a>
                    `;
                    notificationDropdown.appendChild(notificationItem);
                });
            } else {
                const emptyNotification = document.createElement('li');
                emptyNotification.innerHTML = `
                    <div class="dropdown-item text-center text-muted py-3">
                        <i class="fas fa-check-circle me-1"></i> Aucune notification
                    </div>
                `;
                notificationDropdown.appendChild(emptyNotification);
            }

            // Réajouter le pied de page s'il existe
            if (footer) {
                // Ajouter un séparateur
                const divider = document.createElement('li');
                divider.innerHTML = '<hr class="dropdown-divider my-1">';
                notificationDropdown.appendChild(divider);

                const footerItem = document.createElement('li');
                footerItem.appendChild(footer.cloneNode(true));
                notificationDropdown.appendChild(footerItem);
            }
        }
    }

    // Afficher une alerte pour les nouvelles notifications
    function showNewNotificationAlert() {
        // Vérifier si le navigateur prend en charge les notifications
        if ('Notification' in window) {
            // Vérifier l'autorisation
            if (Notification.permission === 'granted') {
                new Notification('Nouvelle demande de réservation', {
                    body: 'Vous avez une nouvelle demande de réservation à traiter.',
                    icon: '/assets/images/logo-1.png'
                });
            } else if (Notification.permission !== 'denied') {
                // Demander l'autorisation
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('Nouvelle demande de réservation', {
                            body: 'Vous avez une nouvelle demande de réservation à traiter.',
                            icon: '/assets/images/logo-1.png'
                        });
                    }
                });
            }
        }

        // Alternative: alerte visuelle dans l'interface
        const notificationIcon = document.querySelector('.notification-icon-container');
        if (notificationIcon) {
            // Ajouter une animation de pulsation
            notificationIcon.classList.add('pulse-animation');

            // Supprimer l'animation après quelques secondes
            setTimeout(() => {
                notificationIcon.classList.remove('pulse-animation');
            }, 5000);
        }
    }

    // Vérifier si l'utilisateur a un rôle qui nécessite des notifications
    const userRole = document.body.dataset.userRole || '';
    if (['administrateur', 'gestionnaire', 'validateur'].includes(userRole)) {
        // Vérifier les notifications immédiatement
        checkNotifications();

        // Configurer le polling pour vérifier régulièrement
        setInterval(checkNotifications, POLLING_INTERVAL);
    }
});