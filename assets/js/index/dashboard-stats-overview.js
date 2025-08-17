/**
 * Script pour le tableau de bord statistique sur la page d'accueil
 */

document.addEventListener('DOMContentLoaded', function() {
    // Animation des statistiques au chargement
    animateStatsOverview();
    
    // Actualiser les statistiques toutes les 2 minutes
    setInterval(refreshStats, 120000);
});

/**
 * Anime les valeurs des statistiques
 */
function animateStatsOverview() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        const duration = 1000; // 1 seconde
        const steps = 25;
        const stepValue = finalValue / steps;
        let currentStep = 0;
        
        // Valeur initiale à 0
        stat.textContent = '0';
        
        // Animation
        const interval = setInterval(() => {
            currentStep++;
            const currentValue = Math.floor(stepValue * currentStep);
            stat.textContent = currentValue;
            
            if (currentStep >= steps) {
                clearInterval(interval);
                stat.textContent = finalValue; // Assurer la valeur finale exacte
            }
        }, duration / steps);
    });
    
    // Anime les barres de progression
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const widthValue = bar.getAttribute('style').split('width: ')[1].split('%')[0];
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease';
            bar.style.width = widthValue + '%';
        }, 200);
    });
}

/**
 * Rafraîchit les statistiques avec les données les plus récentes
 */
function refreshStats() {
    // Afficher un indicateur de chargement
    const container = document.querySelector('.dashboard-stats-overview');
    
    if (container) {
        container.style.opacity = '0.7';
        
        // Faire une requête AJAX pour récupérer les dernières statistiques
        fetch('ajax/get_dashboard_stats.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau lors de la récupération des statistiques');
                }
                return response.json();
            })
            .then(data => {
                // Mettre à jour les statistiques
                updateStats(data);
                
                // Afficher un toast de mise à jour
                showUpdateToast();
                
                // Restaurer l'opacité
                container.style.opacity = '1';
            })
            .catch(error => {
                console.error('Erreur:', error);
                // Restaurer l'opacité
                container.style.opacity = '1';
                
                // Afficher un toast d'erreur
                showErrorToast("Impossible d'actualiser les statistiques");
            });
    }
}

/**
 * Met à jour les statistiques avec les nouvelles données
 * @param {Object} data Les données de statistiques
 */
function updateStats(data) {
    // Mise à jour des statistiques de véhicules
    updateStatValue('total-vehicules', data.vehicules.total);
    updateStatValue('vehicules-disponibles', data.vehicules.disponibles);
    
    // Calculer les pourcentages pour les véhicules
    const vehicleAvailablePercent = (data.vehicules.total > 0) ? 
        Math.round((data.vehicules.disponibles / data.vehicules.total) * 100) : 0;
    const vehicleInUsePercent = (data.vehicules.total > 0) ? 
        Math.round((data.vehicules.en_course / data.vehicules.total) * 100) : 0;
    const vehicleMaintenancePercent = (data.vehicules.total > 0) ? 
        Math.round((data.vehicules.maintenance / data.vehicules.total) * 100) : 0;
    
    // Mettre à jour la barre de progression des véhicules
    updateProgressBar('vehicules-dispo-progress', vehicleAvailablePercent);
    updateProgressBar('vehicules-course-progress', vehicleInUsePercent);
    updateProgressBar('vehicules-maintenance-progress', vehicleMaintenancePercent);
    
    // Mise à jour des statistiques de chauffeurs
    updateStatValue('total-chauffeurs', data.chauffeurs.total);
    updateStatValue('chauffeurs-disponibles', data.chauffeurs.disponibles);
    
    // Calculer les pourcentages pour les chauffeurs
    const driverAvailablePercent = (data.chauffeurs.total > 0) ? 
        Math.round((data.chauffeurs.disponibles / data.chauffeurs.total) * 100) : 0;
    const driverInUsePercent = (data.chauffeurs.total > 0) ? 
        Math.round((data.chauffeurs.en_course / data.chauffeurs.total) * 100) : 0;
    const driverLeavePercent = (data.chauffeurs.total > 0) ? 
        Math.round((data.chauffeurs.conge / data.chauffeurs.total) * 100) : 0;
    
    // Mettre à jour la barre de progression des chauffeurs
    updateProgressBar('chauffeurs-dispo-progress', driverAvailablePercent);
    updateProgressBar('chauffeurs-course-progress', driverInUsePercent);
    updateProgressBar('chauffeurs-conge-progress', driverLeavePercent);
    
    // Mise à jour des statistiques de réservations
    updateStatValue('total-reservations', data.reservations.total);
    updateStatValue('reservations-terminees', data.reservations.terminees);
    
    // Calculer les pourcentages pour les réservations
    const reservationCompletedPercent = (data.reservations.total > 0) ? 
        Math.round((data.reservations.terminees / data.reservations.total) * 100) : 0;
    const reservationInProgressPercent = (data.reservations.total > 0) ? 
        Math.round((data.reservations.en_cours / data.reservations.total) * 100) : 0;
    const reservationWaitingPercent = (data.reservations.total > 0) ? 
        Math.round((data.reservations.en_attente / data.reservations.total) * 100) : 0;
    
    // Mettre à jour la barre de progression des réservations
    updateProgressBar('reservations-terminees-progress', reservationCompletedPercent);
    updateProgressBar('reservations-encours-progress', reservationInProgressPercent);
    updateProgressBar('reservations-attente-progress', reservationWaitingPercent);
    
    // Mise à jour des statistiques de maintenance
    updateStatValue('total-maintenances', data.maintenance.total);
    updateStatValue('maintenances-encours', data.maintenance.en_cours);
    
    // Calculer les pourcentages pour les maintenances
    const maintenanceCompletedPercent = (data.maintenance.total > 0) ? 
        Math.round((data.maintenance.terminees / data.maintenance.total) * 100) : 0;
    const maintenanceInProgressPercent = (data.maintenance.total > 0) ? 
        Math.round((data.maintenance.en_cours / data.maintenance.total) * 100) : 0;
    const maintenancePlannedPercent = (data.maintenance.total > 0) ? 
        Math.round((data.maintenance.planifiees / data.maintenance.total) * 100) : 0;
    
    // Mettre à jour la barre de progression des maintenances
    updateProgressBar('maintenances-terminees-progress', maintenanceCompletedPercent);
    updateProgressBar('maintenances-encours-progress', maintenanceInProgressPercent);
    updateProgressBar('maintenances-planifiees-progress', maintenancePlannedPercent);
}

/**
 * Met à jour la valeur d'une statistique
 * @param {string} id L'identifiant de l'élément
 * @param {number} value La nouvelle valeur
 */
function updateStatValue(id, value) {
    const element = document.getElementById(id);
    if (element) {
        // Mémoriser l'ancienne valeur
        const oldValue = parseInt(element.textContent);
        
        // Si la valeur a changé, animer la transition
        if (oldValue !== value) {
            animateValueChange(element, oldValue, value);
        }
    }
}

/**
 * Anime le changement de valeur d'un élément
 * @param {HTMLElement} element L'élément DOM
 * @param {number} start La valeur de départ
 * @param {number} end La valeur finale
 */
function animateValueChange(element, start, end) {
    const duration = 800; // 800ms
    const steps = 20;
    const stepValue = (end - start) / steps;
    let currentStep = 0;
    let currentValue = start;
    
    // Animation
    const interval = setInterval(() => {
        currentStep++;
        currentValue += stepValue;
        element.textContent = Math.round(currentValue);
        
        // Ajouter une classe pour mettre en évidence le changement
        element.classList.add('value-changed');
        
        if (currentStep >= steps) {
            clearInterval(interval);
            element.textContent = end; // Assurer la valeur finale exacte
            
            // Retirer la classe après l'animation
            setTimeout(() => {
                element.classList.remove('value-changed');
            }, 300);
        }
    }, duration / steps);
}

/**
 * Met à jour une barre de progression
 * @param {string} id L'identifiant de la barre de progression
 * @param {number} percent Le pourcentage à afficher
 */
function updateProgressBar(id, percent) {
    const progressBar = document.getElementById(id);
    if (progressBar) {
        progressBar.style.transition = 'width 0.8s ease';
        progressBar.style.width = percent + '%';
    }
}

/**
 * Affiche un toast de mise à jour réussie
 */
function showUpdateToast() {
    // Créer le toast container s'il n'existe pas
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Créer le toast
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-success border-0';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Contenu du toast
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>Statistiques mises à jour
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    // Ajouter le toast au container
    toastContainer.appendChild(toast);
    
    // Initialiser et afficher le toast
    const bsToast = new bootstrap.Toast(toast, {
        delay: 3000
    });
    bsToast.show();
    
    // Supprimer le toast après qu'il soit masqué
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

/**
 * Affiche un toast d'erreur
 * @param {string} message Le message d'erreur
 */
function showErrorToast(message) {
    // Créer le toast container s'il n'existe pas
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Créer le toast
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-danger border-0';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Contenu du toast
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-triangle me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    // Ajouter le toast au container
    toastContainer.appendChild(toast);
    
    // Initialiser et afficher le toast
    const bsToast = new bootstrap.Toast(toast, {
        delay: 5000
    });
    bsToast.show();
    
    // Supprimer le toast après qu'il soit masqué
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Styles additionnels pour l'animation des changements de valeur
const style = document.createElement('style');
style.textContent = `
    @keyframes highlight {
        0% { color: #333; }
        50% { color: #0d6efd; }
        100% { color: #333; }
    }
    
    .value-changed {
        animation: highlight 0.8s ease;
        font-weight: bold;
    }
`;
document.head.appendChild(style);