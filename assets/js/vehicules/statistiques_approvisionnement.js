/**
 * Statistiques d'approvisionnement - Fonctionnalités avancées et animations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Animation des cartes de statistiques globales
    animateCounters();
    
    // Gestionnaire d'impression
    setupPrintHandler();
    
    // Gestionnaire d'export
    setupExportHandlers();
    
    // Ajout d'interactivité aux graphiques
    enhanceCharts();
    
    // Amélioration de l'interface utilisateur
    setupEnhancedUI();
});

/**
 * Anime les compteurs dans les cartes de statistiques 
 */
function animateCounters() {
    const counterElements = document.querySelectorAll('.card-body h2');
    
    counterElements.forEach(counter => {
        const finalValue = parseFloat(counter.textContent.replace(/[^\d.-]/g, ''));
        
        // Déterminer si c'est une valeur monétaire
        const isCurrency = counter.textContent.includes('FCFA');
        
        // Déterminer si c'est une valeur décimale
        const isDecimal = counter.textContent.includes(',');
        
        // Configurer l'animation
        const duration = 1000; // 1 seconde
        const steps = 20;
        const stepValue = finalValue / steps;
        let currentStep = 0;
        
        counter.textContent = '0';
        
        // Animation
        const interval = setInterval(() => {
            currentStep++;
            const value = stepValue * currentStep;
            
            // Formater la valeur selon son type
            if (isCurrency) {
                counter.textContent = new Intl.NumberFormat('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(value) + ' FCFA';
            } else if (isDecimal) {
                counter.textContent = new Intl.NumberFormat('fr-FR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(value) + ' L';
            } else {
                counter.textContent = new Intl.NumberFormat('fr-FR').format(value);
            }
            
            if (currentStep >= steps) {
                clearInterval(interval);
                // S'assurer que la valeur finale est exacte
                if (isCurrency) {
                    counter.textContent = new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(finalValue) + ' FCFA';
                } else if (isDecimal) {
                    counter.textContent = new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(finalValue) + ' L';
                } else {
                    counter.textContent = new Intl.NumberFormat('fr-FR').format(finalValue);
                }
            }
        }, duration / steps);
    });
}

/**
 * Configurer le gestionnaire d'impression
 */
function setupPrintHandler() {
    // Ajouter un bouton d'impression s'il n'existe pas déjà
    if (!document.getElementById('printBtn')) {
        const actionBar = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
        
        if (actionBar) {
            const printBtn = document.createElement('button');
            printBtn.id = 'printBtn';
            printBtn.className = 'btn btn-outline-dark ms-2';
            printBtn.innerHTML = '<i class="fas fa-print me-2"></i>Imprimer';
            
            // Insérer avant le bouton de retour
            const returnBtn = actionBar.querySelector('.btn-secondary');
            actionBar.insertBefore(printBtn, returnBtn);
            
            // Ajouter le gestionnaire d'événement
            printBtn.addEventListener('click', function() {
                window.print();
            });
        }
    }
    
    // Ajouter des styles d'impression
    addPrintStyles();
}

/**
 * Ajouter des styles spécifiques pour l'impression
 */
function addPrintStyles() {
    const style = document.createElement('style');
    style.type = 'text/css';
    style.media = 'print';
    
    style.textContent = `
        @media print {
            /* Cacher les éléments inutiles en impression */
            header, footer, .nav, .btn, #filterForm, input, select, button, .form-control {
                display: none !important;
            }
            
            /* Ajuster les marges et couleurs */
            body {
                background-color: white;
                color: black;
                margin: 0;
                padding: 20px;
            }
            
            .card {
                border: 1px solid #ddd;
                margin-bottom: 20px;
                box-shadow: none !important;
            }
            
            .card-header {
                background-color: #f0f0f0 !important;
                color: #333 !important;
            }
            
            /* Forcer l'affichage des graphiques */
            canvas {
                max-height: 300px;
                width: 100% !important;
            }
            
            /* Enlever les couleurs de fond des cartes de statistiques */
            .bg-primary, .bg-success, .bg-danger, .bg-info {
                background-color: #f8f9fa !important;
                color: #333 !important;
            }
            
            /* S'assurer que les tableaux s'affichent correctement */
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            
            /* Insérer un titre de page en haut */
            body::before {
                content: "Statistiques d'approvisionnement - Rapport généré le " + new Date().toLocaleDateString('fr-FR');
                display: block;
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 20px;
            }
        }
    `;
    
    document.head.appendChild(style);
}

/**
 * Configurer les boutons d'export (CSV, Excel)
 */
function setupExportHandlers() {
    // Ajouter le groupe de boutons d'export s'ils n'existent pas déjà
    if (!document.getElementById('exportBtns')) {
        const actionBar = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
        
        if (actionBar) {
            const exportGroup = document.createElement('div');
            exportGroup.id = 'exportBtns';
            exportGroup.className = 'btn-group ms-2';
            exportGroup.setAttribute('role', 'group');
            
            exportGroup.innerHTML = `
                <button id="exportCsv" class="btn btn-outline-success" title="Exporter en CSV">
                    <i class="fas fa-file-csv me-2"></i>CSV
                </button>
                <button id="exportExcel" class="btn btn-outline-success" title="Exporter en Excel">
                    <i class="fas fa-file-excel me-2"></i>Excel
                </button>
            `;
            
            // Insérer avant le bouton d'impression
            const printBtn = document.getElementById('printBtn');
            if (printBtn) {
                actionBar.insertBefore(exportGroup, printBtn);
            } else {
                // Si le bouton d'impression n'existe pas, insérer avant le bouton de retour
                const returnBtn = actionBar.querySelector('.btn-secondary');
                actionBar.insertBefore(exportGroup, returnBtn);
            }
            
            // Ajouter les gestionnaires d'événements
            document.getElementById('exportCsv').addEventListener('click', exportToCSV);
            document.getElementById('exportExcel').addEventListener('click', exportToExcel);
        }
    }
}

/**
 * Exporter les données en CSV
 */
function exportToCSV() {
    // Récupérer la table des véhicules
    const table = document.querySelector('.table');
    if (!table) return;
    
    // Construire les en-têtes
    let csv = [];
    const headers = [];
    const headerCells = table.querySelectorAll('thead th');
    
    headerCells.forEach(th => {
        // Nettoyer le texte (retirer les icônes)
        const headerText = th.textContent.replace(/[\n\r]+|[\s]{2,}/g, ' ').trim();
        headers.push('"' + headerText + '"');
    });
    
    csv.push(headers.join(','));
    
    // Construire les lignes de données
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('td');
        
        cells.forEach(cell => {
            // Nettoyer le texte (retirer les balises HTML)
            const cellText = cell.textContent.replace(/[\n\r]+|[\s]{2,}/g, ' ').trim();
            rowData.push('"' + cellText + '"');
        });
        
        csv.push(rowData.join(','));
    });
    
    // Créer le blob et le télécharger
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', 'statistiques_approvisionnement.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Exporter les données en Excel (simulé par CSV avec extension .xlsx)
 */
function exportToExcel() {
    // Pour cette version simplifiée, nous utilisons le même mécanisme que CSV
    // mais avec une extension différente
    
    // Récupérer la table des véhicules
    const table = document.querySelector('.table');
    if (!table) return;
    
    // Construire les en-têtes
    let csv = [];
    const headers = [];
    const headerCells = table.querySelectorAll('thead th');
    
    headerCells.forEach(th => {
        // Nettoyer le texte (retirer les icônes)
        const headerText = th.textContent.replace(/[\n\r]+|[\s]{2,}/g, ' ').trim();
        headers.push('"' + headerText + '"');
    });
    
    csv.push(headers.join(','));
    
    // Construire les lignes de données
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('td');
        
        cells.forEach(cell => {
            // Nettoyer le texte (retirer les balises HTML)
            const cellText = cell.textContent.replace(/[\n\r]+|[\s]{2,}/g, ' ').trim();
            rowData.push('"' + cellText + '"');
        });
        
        csv.push(rowData.join(','));
    });
    
    // Créer le blob et le télécharger
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'application/vnd.ms-excel' });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', 'statistiques_approvisionnement.xlsx');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Améliorer l'interactivité des graphiques
 */
function enhanceCharts() {
    // Optimiser les graphiques pour différentes tailles d'écran
    optimizeChartsForResponsive();
    
    // Ajouter des contrôles supplémentaires aux graphiques
    const chartContainers = document.querySelectorAll('.card-body canvas');
    
    chartContainers.forEach(canvas => {
        const chartId = canvas.id;
        const chartContainer = canvas.parentNode;
        
        // Créer les contrôles
        const controlsDiv = document.createElement('div');
        controlsDiv.className = 'chart-controls';
        controlsDiv.innerHTML = `
            <button class="btn btn-sm chart-download" data-chart="${chartId}">
                <i class="fas fa-download me-1"></i>Télécharger
            </button>
        `;
        
        // Ajouter après le canvas
        chartContainer.appendChild(controlsDiv);
        
        // Ajouter les gestionnaires d'événements
        controlsDiv.querySelector('.chart-download').addEventListener('click', function() {
            downloadChart(this.getAttribute('data-chart'));
        });
    });
}

/**
 * Optimise les graphiques pour différentes tailles d'écran
 */
function optimizeChartsForResponsive() {
    // Adapter les options des graphiques en fonction de la taille d'écran
    const mediaQuery = window.matchMedia('(max-width: 768px)');
    
    function handleScreenChange(e) {
        const isMobile = e.matches;
        
        // Récupérer les instances de graphiques
        const charts = Object.values(Chart.instances);
        
        charts.forEach(chart => {
            // Ajuster les options en fonction de la taille d'écran
            if (chart.config.type === 'line') {
                // Pour les graphiques linéaires (évolution)
                chart.options.plugins.legend.position = isMobile ? 'bottom' : 'top';
                chart.options.scales.y.ticks.maxTicksLimit = isMobile ? 5 : 8;
                chart.options.scales.x.ticks.maxRotation = isMobile ? 60 : 45;
            } 
            else if (chart.config.type === 'bar') {
                // Pour les graphiques à barres (coût par véhicule)
                chart.options.scales.x.ticks.maxRotation = isMobile ? 90 : 45;
                chart.options.scales.x.ticks.minRotation = isMobile ? 60 : 45;
                chart.options.scales.y.ticks.maxTicksLimit = isMobile ? 5 : 8;
            }
            else if (chart.config.type === 'doughnut') {
                // Pour les graphiques camembert
                chart.options.plugins.legend.position = 'bottom';
                chart.options.plugins.legend.labels
                chart.options.plugins.legend.labels.boxWidth = isMobile ? 12 : 20;
                chart.options.plugins.legend.labels.padding = isMobile ? 10 : 15;
            }
            
            // Appliquer les changements
            chart.update();
        });
    }
    
    // Appliquer immédiatement
    handleScreenChange(mediaQuery);
    
    // Écouter les changements de taille d'écran
    mediaQuery.addEventListener('change', handleScreenChange);
}

/**
 * Configurer l'interface utilisateur pour une meilleure expérience
 */
function setupEnhancedUI() {
    // Ajouter des classes pour améliorer les en-têtes du tableau
    const tableHeaders = document.querySelectorAll('.table th');
    tableHeaders.forEach(header => {
        // Ajouter une petite animation au survol des en-têtes
        header.addEventListener('mouseover', function() {
            this.style.backgroundColor = '#495057';
        });
        
        header.addEventListener('mouseout', function() {
            this.style.backgroundColor = '#343a40';
        });
    });
    
    // Optimiser le tableau pour les appareils mobiles
    const tableContainer = document.querySelector('.table-responsive');
    if (tableContainer) {
        // Ajouter un indicateur de défilement horizontal sur mobile
        const mediaQuery = window.matchMedia('(max-width: 992px)');
        
        if (mediaQuery.matches) {
            const scrollIndicator = document.createElement('div');
            scrollIndicator.className = 'text-center text-muted mb-2';
            scrollIndicator.innerHTML = '<small><i class="fas fa-arrows-alt-h me-1"></i>Faites défiler horizontalement pour voir toutes les données</small>';
            
            tableContainer.parentNode.insertBefore(scrollIndicator, tableContainer);
        }
    }
    
    // Masquer les champs vides dans le tableau
    document.querySelectorAll('.table td').forEach(cell => {
        if (cell.textContent.trim() === '---') {
            cell.classList.add('text-muted');
            cell.style.fontStyle = 'italic';
        }
    });
}

/**
 * Télécharger un graphique en tant qu'image PNG
 */
function downloadChart(chartId) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    // Créer un lien de téléchargement
    const link = document.createElement('a');
    
    // Configurer le nom de fichier
    let fileName = 'graphique.png';
    
    // Personnaliser le nom de fichier selon le type de graphique
    if (chartId === 'chartEvolution') {
        fileName = 'evolution_approvisionnements.png';
    } else if (chartId === 'chartCarburant') {
        fileName = 'repartition_types_carburant.png';
    } else if (chartId === 'chartVehicules') {
        fileName = 'cout_par_vehicule.png';
    }
    
    // Configurer et déclencher le téléchargement
    link.download = fileName;
    link.href = canvas.toDataURL('image/png');
    link.click();
}