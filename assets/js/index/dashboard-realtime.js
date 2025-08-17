// dashboard-realtime.js - Mise à jour dynamique du tableau de bord
// Ce script permet une mise à jour en temps réel des statistiques et notifications
// sans nécessiter le rafraîchissement manuel de la page

document.addEventListener('DOMContentLoaded', function () {
    // Intervalle de rafraîchissement des données (en millisecondes)
    const REFRESH_INTERVAL = 10000; // 10 secondes

    // Référence aux éléments du DOM qui affichent les statistiques
    const elements = {
        vehiculesDisponibles: document.querySelector('[data-stat="vehicules-disponibles"]'),
        vehiculesTotal: document.querySelector('[data-stat="vehicules-total"]'),
        chauffeursDisponibles: document.querySelector('[data-stat="chauffeurs-disponibles"]'),
        chauffeursTotal: document.querySelector('[data-stat="chauffeurs-total"]'),
        vehiculesMaintenance: document.querySelector('[data-stat="vehicules-maintenance"]'),
        reservationsAttente: document.querySelector('[data-stat="reservations-attente"]'),
        documentsAlerts: document.querySelector('#documents-alerts-container'),
        maintenancesEnCours: document.querySelector('#maintenances-container')
    };

    // Fonction pour charger les statistiques des véhicules et chauffeurs
    function chargerStatistiques() {
        fetch('api/index/dashboard-stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mise à jour des statistiques avec animation
                    updateWithAnimation(elements.vehiculesDisponibles, data.stats_vehicules.vehicules_disponibles);
                    updateWithAnimation(elements.vehiculesTotal, data.stats_vehicules.total_vehicules);
                    updateWithAnimation(elements.chauffeursDisponibles, data.stats_chauffeurs.chauffeurs_disponibles);
                    updateWithAnimation(elements.chauffeursTotal, data.stats_chauffeurs.total_chauffeurs);
                    updateWithAnimation(elements.vehiculesMaintenance, data.stats_vehicules.vehicules_maintenance);
                    updateWithAnimation(elements.reservationsAttente, data.reservations_attente.length);

                    // Mise à jour du conteneur de documents
                    updateDocumentsContainer(data.documents_alerts);

                    // Mise à jour du conteneur de maintenances
                    updateMaintenancesContainer(data.maintenances_en_cours);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des statistiques:', error));
    }

    // Fonction d'animation pour la mise à jour des nombres
    function updateWithAnimation(element, newValue) {
        if (!element) return;

        const currentValue = parseInt(element.textContent);
        if (currentValue !== newValue) {
            // Ajouter une classe pour l'animation
            element.classList.add('stat-changing');

            // Mettre à jour après une courte période
            setTimeout(() => {
                element.textContent = newValue;
                element.classList.remove('stat-changing');
                // Animation de highlight
                element.classList.add('stat-highlight');
                setTimeout(() => {
                    element.classList.remove('stat-highlight');
                }, 1000);
            }, 300);
        }
    }

    // Fonction pour mettre à jour le conteneur des alertes de documents
    function updateDocumentsContainer(documents) {
        if (!elements.documentsAlerts) return;

        // Si aucun document à afficher
        if (documents.length === 0) {
            elements.documentsAlerts.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>Aucun document n'arrive à expiration
                </div>
            `;
            return;
        }

        // Générer le HTML pour les documents
        let html = '';
        documents.forEach(doc => {
            const alertClass = doc.jours_restants <= 7 ? 'alert-danger' :
                (doc.jours_restants <= 30 ? 'alert-warning' : 'alert-info');

            html += `
                <div class="alert ${alertClass} doc-alert" data-doc-id="${doc.id_document}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${capitalizeFirstLetter(doc.type_document.replace('_', ' '))}</strong>
                            ${doc.marque ? `
                                <br>
                                <small>${doc.marque} ${doc.modele} (${doc.immatriculation})</small>
                            ` : ''}
                        </div>
                        <div class="text-end">
                            <strong>${doc.jours_restants} jours</strong><br>
                            <small>jusqu'à expiration</small>
                        </div>
                    </div>
                </div>
            `;
        });

        // Vérifier si le contenu a changé avant de le mettre à jour
        if (elements.documentsAlerts.innerHTML !== html) {
            const temp = document.createElement('div');
            temp.innerHTML = html;

            // Identifier les nouveaux éléments
            const currentDocIds = Array.from(elements.documentsAlerts.querySelectorAll('.doc-alert'))
                .map(el => el.dataset.docId);
            const newDocIds = Array.from(temp.querySelectorAll('.doc-alert'))
                .map(el => el.dataset.docId);

            // Mettre à jour le DOM
            elements.documentsAlerts.innerHTML = html;

            // Animer les nouveaux éléments
            newDocIds.forEach(id => {
                if (!currentDocIds.includes(id)) {
                    const newEl = elements.documentsAlerts.querySelector(`.doc-alert[data-doc-id="${id}"]`);
                    if (newEl) {
                        newEl.classList.add('fade-in-animation');
                    }
                }
            });
        }
    }

    // Fonction pour mettre à jour le conteneur des maintenances
    function updateMaintenancesContainer(maintenances) {
        if (!elements.maintenancesEnCours) return;

        // Si aucune maintenance à afficher
        if (maintenances.length === 0) {
            elements.maintenancesEnCours.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Aucune maintenance en cours
                </div>
            `;
            return;
        }

        // Générer le HTML pour les maintenances
        let html = '<div class="list-group">';
        maintenances.forEach(maintenance => {
            const dateFinPrevue = new Date(maintenance.date_fin_prevue).toLocaleDateString('fr-FR');

            html += `
                <div class="list-group-item maintenance-item" data-maintenance-id="${maintenance.id_maintenance}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">
                            ${maintenance.marque} ${maintenance.modele}
                        </h6>
                        <small class="text-muted">
                            ${dateFinPrevue}
                        </small>
                    </div>
                    <p class="mb-1">${maintenance.description}</p>
                    <small class="text-muted">
                        ${maintenance.type_maintenance} - 
                        ${maintenance.prestataire}
                    </small>
                </div>
            `;
        });
        html += '</div>';

        // Vérifier si le contenu a changé avant de le mettre à jour
        if (elements.maintenancesEnCours.innerHTML !== html) {
            const temp = document.createElement('div');
            temp.innerHTML = html;

            // Identifier les nouveaux éléments
            const currentMaintenanceIds = Array.from(elements.maintenancesEnCours.querySelectorAll('.maintenance-item'))
                .map(el => el.dataset.maintenanceId);
            const newMaintenanceIds = Array.from(temp.querySelectorAll('.maintenance-item'))
                .map(el => el.dataset.maintenanceId);

            // Mettre à jour le DOM
            elements.maintenancesEnCours.innerHTML = html;

            // Animer les nouveaux éléments
            newMaintenanceIds.forEach(id => {
                if (!currentMaintenanceIds.includes(id)) {
                    const newEl = elements.maintenancesEnCours.querySelector(`.maintenance-item[data-maintenance-id="${id}"]`);
                    if (newEl) {
                        newEl.classList.add('fade-in-animation');
                    }
                }
            });
        }
    }

    // Fonction d'aide pour capitaliser la première lettre
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // Charger les données initiales
    chargerStatistiques();

    // Configurer l'actualisation périodique
    setInterval(chargerStatistiques, REFRESH_INTERVAL);

    // Notifications WebSocket (pour une mise à jour instantanée)
    setupWebSocketNotifications();
});

// Configuration de WebSocket pour les notifications en temps réel
function setupWebSocketNotifications() {
    // Vérifier si le navigateur supporte WebSocket
    if ('WebSocket' in window) {
        // Créer une connexion WebSocket en utilisant un serveur sécurisé (wss)
        // Pour le développement, on peut utiliser une solution comme Pusher ou Socket.io
        // Sur certains hébergements, il peut être nécessaire d'utiliser des solutions alternatives comme Long Polling

        // Pour cette démonstration, nous allons simuler des mises à jour
        console.log('WebSocket ou équivalent serait configuré ici dans un environnement de production');

        // Pour un vrai environnement de production:
        // const socket = new WebSocket('wss://votre-serveur-websocket.com/dashboard');

        // socket.onopen = function() {
        //     console.log('Connexion WebSocket établie');
        // };

        // socket.onmessage = function(event) {
        //     const data = JSON.parse(event.data);
        //     if (data.type === 'stat_update') {
        //         updateStats(data.stats);
        //     } else if (data.type === 'new_notification') {
        //         showNewNotification(data.notification);
        //     }
        // };

        // socket.onclose = function() {
        //     console.log('Connexion WebSocket fermée. Tentative de reconnexion...');
        //     setTimeout(setupWebSocketNotifications, 3000);
        // };
    }
}

// Fonction pour afficher une nouvelle notification (toast)
function showNewNotification(notification) {
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-primary border-0';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${notification.message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        const newContainer = document.createElement('div');
        newContainer.id = 'toast-container';
        newContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(newContainer);
        newContainer.appendChild(toast);
    } else {
        toastContainer.appendChild(toast);
    }

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}