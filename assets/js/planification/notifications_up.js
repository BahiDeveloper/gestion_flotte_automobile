// notifications_up.js
// Système de polling pour les notifications en temps réel avec gestion des notifications lues

document.addEventListener('DOMContentLoaded', function () {
    // Configuration
    const POLLING_INTERVAL = 10000; // Vérifier toutes les 10 secondes
    let lastNotificationCount = 0;
    let isInitialized = false;
    let readNotifications = getReadNotifications(); // Récupérer les notifications déjà lues

    // Elements DOM
    const notificationBadge = document.querySelector('.notification-badge');
    const notificationDropdown = document.querySelector('.notification-dropdown');
    const markAllAsReadBtn = document.getElementById('markAllAsReadBtn');

    // Fonction pour récupérer les notifications lues du localStorage
    function getReadNotifications() {
        const stored = localStorage.getItem('readNotifications');
        return stored ? JSON.parse(stored) : [];
    }

    // Fonction pour sauvegarder les notifications lues dans le localStorage
    function saveReadNotifications(notificationIds) {
        localStorage.setItem('readNotifications', JSON.stringify(notificationIds));
    }

    // Fonction pour marquer une notification comme lue
    function markAsRead(notificationId) {
        const readNotifications = getReadNotifications();
        if (!readNotifications.includes(notificationId)) {
            readNotifications.push(notificationId);
            saveReadNotifications(readNotifications);

            // Synchroniser avec le serveur
            syncReadNotificationWithServer(notificationId);
            
            return true; // La notification a été nouvellement marquée comme lue
        }
        return false; // La notification était déjà marquée comme lue
    }

    // Synchroniser avec le serveur
    function syncReadNotificationWithServer(notificationId, isMarkAll = false) {
        const formData = new FormData();
        formData.append('notification_id', notificationId);
        if (isMarkAll) {
            formData.append('mark_all', 'true');
        }

        fetch('api/mark-notification-read.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Erreur lors du marquage de la notification:', data.message);
            }
        })
        .catch(error => {
            console.error('Erreur réseau:', error);
        });
    }

    // Fonction pour marquer toutes les notifications comme lues
    function markAllAsRead(notifications) {
        const readNotifications = getReadNotifications();
        let updated = false;

        notifications.forEach(notification => {
            const notificationId = `${notification.type}_${notification.id}`;
            if (!readNotifications.includes(notificationId)) {
                readNotifications.push(notificationId);
                updated = true;
            }
        });

        if (updated) {
            saveReadNotifications(readNotifications);

            // Synchroniser avec le serveur
            syncReadNotificationWithServer('all', true);
        }

        return updated;
    }

    // Fonction pour vérifier si une notification a été lue
    function isNotificationRead(notification) {
        const notificationId = `${notification.type}_${notification.id}`;
        return readNotifications.includes(notificationId);
    }

    // Fonction pour charger les notifications
    function checkNotifications() {
        fetch('api/check-notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour la liste des notifications lues depuis le localStorage
                    readNotifications = getReadNotifications();
                    
                    // Filtrer les notifications non lues seulement
                    const unreadNotifications = data.notifications.filter(notification => !isNotificationRead(notification));
                    const unreadCount = unreadNotifications.length;

                    // Mise à jour du compteur de notifications
                    updateNotificationBadge(unreadCount);

                    // Mise à jour du contenu de la dropdown (uniquement notifications non lues)
                    updateNotificationContent(unreadNotifications);

                    // Si ce n'est pas la première initialisation et qu'il y a de nouvelles notifications
                    if (isInitialized && unreadCount > lastNotificationCount) {
                        showNewNotificationAlert(unreadNotifications);
                    }

                    lastNotificationCount = unreadCount;
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
                
                // Mettre à jour le badge dans l'en-tête du dropdown également
                const headerBadge = notificationDropdown?.querySelector('.badge');
                if (headerBadge) {
                    headerBadge.textContent = count;
                    headerBadge.style.display = 'block';
                }
                
                // Afficher le bouton "Tout marquer comme lu" si il existe
                const markAllBtn = notificationDropdown?.querySelector('#markAllAsReadBtn');
                if (markAllBtn) {
                    markAllBtn.style.display = 'block';
                }
            } else {
                notificationBadge.style.display = 'none';
                
                // Masquer le badge dans l'en-tête également
                const headerBadge = notificationDropdown?.querySelector('.badge');
                if (headerBadge) {
                    headerBadge.style.display = 'none';
                }
                
                // Masquer le bouton "Tout marquer comme lu" si il existe
                const markAllBtn = notificationDropdown?.querySelector('#markAllAsReadBtn');
                if (markAllBtn) {
                    markAllBtn.style.display = 'none';
                }
            }
        }
    }

    // Mettre à jour le contenu de la dropdown de notifications
    function updateNotificationContent(unreadNotifications) {
        if (!notificationDropdown) return;
        
        // Conserver l'en-tête et le pied de la dropdown
        const header = notificationDropdown.querySelector('.notification-header');
        const footer = notificationDropdown.querySelector('a.text-primary');

        // Sélectionner ou créer le conteneur pour les notifications
        let notificationsContainer = notificationDropdown.querySelector('.notifications-container');
        if (!notificationsContainer) {
            notificationsContainer = document.createElement('li');
            notificationsContainer.className = 'notifications-container';
            notificationDropdown.appendChild(notificationsContainer);
        } else {
            // Vider le conteneur existant
            notificationsContainer.innerHTML = '';
        }

        // Vider la dropdown mais conserver structure
        const listItems = notificationDropdown.querySelectorAll('li:not(.notifications-container)');
        listItems.forEach(item => {
            if (!item.classList.contains('notification-header') && 
                !item.querySelector('a.text-primary')) {
                item.remove();
            }
        });

        // Mettre à jour l'en-tête s'il existe
        if (header) {
            // Mettre à jour le compteur dans l'en-tête
            const headerBadge = header.querySelector('.badge');
            if (headerBadge) {
                headerBadge.textContent = unreadNotifications.length;
                headerBadge.style.display = unreadNotifications.length > 0 ? 'block' : 'none';
            }

            // Mettre à jour le bouton "Marquer tout comme lu"
            let markAllBtn = header.querySelector('#markAllAsReadBtn');
            if (!markAllBtn && unreadNotifications.length > 0) {
                markAllBtn = document.createElement('button');
                markAllBtn.className = 'btn btn-sm btn-outline-primary ms-2';
                markAllBtn.id = 'markAllAsReadBtn';
                markAllBtn.innerHTML = '<i class="fas fa-check-double me-1"></i>Tout marquer comme lu';
                header.querySelector('.d-flex')?.appendChild(markAllBtn);
            } else if (markAllBtn) {
                markAllBtn.style.display = unreadNotifications.length > 0 ? 'block' : 'none';
            }

            // Ajouter l'écouteur d'événement au bouton
            if (markAllBtn) {
                // Supprimer les anciens écouteurs d'événements pour éviter les doublons
                const newMarkAllBtn = markAllBtn.cloneNode(true);
                markAllBtn.parentNode.replaceChild(newMarkAllBtn, markAllBtn);
                markAllBtn = newMarkAllBtn;
                
                markAllBtn.addEventListener('click', function() {
                    if (markAllAsRead(unreadNotifications)) {
                        // Ajouter l'animation de disparition à toutes les notifications
                        const notificationItems = notificationsContainer.querySelectorAll('.notification-item');
                        notificationItems.forEach(item => {
                            item.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                            item.style.opacity = '0';
                            item.style.transform = 'translateX(-20px)';
                        });

                        // Mettre à jour les compteurs
                        updateNotificationBadge(0);
                        
                        // Remplacer par le message "Aucune notification" après l'animation
                        setTimeout(() => {
                            notificationsContainer.innerHTML = '';
                            const emptyNotification = document.createElement('div');
                            emptyNotification.className = 'dropdown-item text-center text-muted py-3';
                            emptyNotification.innerHTML = '<i class="fas fa-check-circle me-1"></i> Aucune notification';
                            notificationsContainer.appendChild(emptyNotification);
                        }, 300);
                    }
                });
            }
        }

        // Ajouter les notifications non lues ou un message si aucune notification
        if (unreadNotifications.length > 0) {
            unreadNotifications.forEach(notification => {
                const notificationItem = createNotificationElement(notification);
                notificationsContainer.appendChild(notificationItem);
            });
        } else {
            const emptyNotification = document.createElement('div');
            emptyNotification.className = 'dropdown-item text-center text-muted py-3';
            emptyNotification.innerHTML = '<i class="fas fa-check-circle me-1"></i> Aucune notification';
            notificationsContainer.appendChild(emptyNotification);
        }
    }

    // Créer un élément de notification avec des icônes et styles dynamiques
    function createNotificationElement(notification) {
        const notificationItem = document.createElement('li');
        const notificationId = `${notification.type}_${notification.id}`;

        // Configurer l'icône et le style en fonction du type de notification
        let iconClass = 'fas fa-info-circle';
        let bgClass = 'bg-info';
        let linkUrl = 'planification.php';

        switch (notification.type) {
            case 'reservation':
                iconClass = 'fas fa-calendar-alt';
                bgClass = 'bg-warning';
                linkUrl += '?tab=validation';
                break;
            case 'deplacement_en_cours':
                iconClass = 'fas fa-road';
                bgClass = 'bg-success';
                linkUrl += '?tab=tracking';
                break;
            case 'maintenance_en_cours':
                iconClass = 'fas fa-tools';
                bgClass = 'bg-danger';
                linkUrl = 'gestion_vehicules.php';
                break;
            case 'document_a_renouveler':
                iconClass = 'fas fa-file-alt';
                bgClass = 'bg-primary';
                linkUrl = 'gestion_documents.php';
                break;
        }

        notificationItem.innerHTML = `
            <a class="dropdown-item notification-item py-2 unread" href="${linkUrl}" 
               data-notification-id="${notificationId}">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 notification-icon">
                        <div class="icon-circle ${bgClass}">
                            <i class="${iconClass} text-white"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <div class="fw-bold">${notification.title}</div>
                        <div class="text-muted small">${notification.description}</div>
                        <small class="text-muted">${formatTimeAgo(notification.timestamp)}</small>
                    </div>
                </div>
            </a>
        `;

        // Ajouter l'écouteur d'événement pour marquer comme lu au clic
        const notificationLink = notificationItem.querySelector('a.notification-item');
        notificationLink.addEventListener('click', function(e) {
            // Empêcher le comportement par défaut pour gérer nous-mêmes l'action
            e.preventDefault();

            // Récupérer l'URL de destination
            const destination = this.getAttribute('href');
            
            // Marquer cette notification comme lue
            if (markAsRead(notificationId)) {
                // Réduire le compteur et mettre à jour l'affichage
                const currentCount = parseInt(notificationBadge.textContent || '0') - 1;
                updateNotificationBadge(currentCount > 0 ? currentCount : 0);
                
                // Animation de disparition
                this.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                this.style.opacity = '0';
                this.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    // Supprimer l'élément de notification
                    const listItem = this.closest('li');
                    if (listItem) {
                        listItem.remove();
                        
                        // Si c'était la dernière notification, afficher "Aucune notification"
                        const remainingNotifications = notificationDropdown.querySelectorAll('.notification-item');
                        if (remainingNotifications.length === 0) {
                            const notificationsContainer = notificationDropdown.querySelector('.notifications-container');
                            if (notificationsContainer) {
                                const emptyNotification = document.createElement('div');
                                emptyNotification.className = 'dropdown-item text-center text-muted py-3';
                                emptyNotification.innerHTML = '<i class="fas fa-check-circle me-1"></i> Aucune notification';
                                notificationsContainer.appendChild(emptyNotification);
                            }
                        }
                    }
                    
                    // Naviguer vers la destination après une petite animation
                    window.location.href = destination;
                }, 300);
            } else {
                window.location.href = destination;
            }
        });

        return notificationItem;
    }

    // Formater le temps écoulé
    function formatTimeAgo(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;

        if (diff < 60) return 'À l\'instant';
        if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
        return `Il y a ${Math.floor(diff / 86400)} j`;
    }

    // Afficher une alerte pour les nouvelles notifications
    function showNewNotificationAlert(notifications) {
        // Vérifier si le navigateur prend en charge les notifications
        if ('Notification' in window) {
            // Préparer le message de notification
            const messageTypes = {
                'reservation': 'Nouvelle demande de réservation',
                'deplacement_en_cours': 'Déplacement en cours',
                'maintenance_en_cours': 'Maintenance en cours',
                'document_a_renouveler': 'Document à renouveler'
            };

            const notificationMessages = notifications
                .map(notification => messageTypes[notification.type] || 'Nouvelle notification')
                .join(', ');

            // Vérifier l'autorisation
            if (Notification.permission === 'granted') {
                new Notification('Notifications', {
                    body: `Vous avez de nouvelles notifications : ${notificationMessages}`,
                    icon: '/assets/images/logo-1.png'
                });
            } else if (Notification.permission !== 'denied') {
                // Demander l'autorisation
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('Notifications', {
                            body: `Vous avez de nouvelles notifications : ${notificationMessages}`,
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