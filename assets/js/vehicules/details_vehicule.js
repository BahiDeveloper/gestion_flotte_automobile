/**
 * vehicle_details.js - Gestion des graphiques et tableaux pour la page détails véhicule
 * 
 * Ce script initialise les tableaux DataTables et les graphiques Chart.js
 * pour afficher les statistiques du véhicule.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialisation des tableaux DataTables
    initializeDataTables();

    // Initialisation des graphiques
    initializeCharts();

    // Gestion des exports
    setupExportButtons();
});

/**
 * Initialise tous les tableaux DataTables de la page
 */
function initializeDataTables() {
    const datatableOptions = {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
        },
        responsive: true
    };

    if (document.getElementById('documentsTable')) {
        $('#documentsTable').DataTable({
            ...datatableOptions,
            order: [[2, 'desc']]
        });
    }

    if (document.getElementById('fuelTable')) {
        $('#fuelTable').DataTable(datatableOptions);
    }

    if (document.getElementById('maintenanceTable')) {
        $('#maintenanceTable').DataTable(datatableOptions);
    }
}

/**
 * Initialise tous les graphiques Chart.js de la page
 */
function initializeCharts() {
    // Vérifier l'existence des données et des éléments DOM

    // 1. Graphique de répartition des coûts
    if (typeof costData !== 'undefined' && document.getElementById('costDistributionChart')) {
        initializeCostDistributionChart();
    }

    // 2. Graphiques de consommation de carburant
    if (typeof fuelData !== 'undefined') {
        if (document.getElementById('consumptionChart')) {
            initializeConsumptionChart();
        }

        if (document.getElementById('fuelCostChart')) {
            initializeFuelCostChart();
        }
    }

    // 3. Graphiques de maintenance
    if (typeof maintenanceData !== 'undefined') {
        if (document.getElementById('maintenanceTypeChart')) {
            initializeMaintenanceTypeChart();
        }

        if (document.getElementById('maintenanceCostChart')) {
            initializeMaintenanceCostChart();
        }
    }
}

/**
 * Initialise le graphique de répartition des coûts
 */
function initializeCostDistributionChart() {
    const costDistributionCtx = document.getElementById('costDistributionChart').getContext('2d');
    new Chart(costDistributionCtx, {
        type: 'pie',
        data: {
            labels: ['Carburant', 'Maintenance'],
            datasets: [{
                data: [
                    costData.carburant,
                    costData.maintenance
                ],
                backgroundColor: ['#FFC107', '#17A2B8'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value.toLocaleString('fr-FR')} FCFA (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialise le graphique d'évolution de la consommation
 */
function initializeConsumptionChart() {
    const consumptionCtx = document.getElementById('consumptionChart').getContext('2d');
    new Chart(consumptionCtx, {
        type: 'line',
        data: {
            labels: fuelData.months,
            datasets: [{
                label: 'Consommation (litres)',
                data: fuelData.liters,
                borderColor: '#007BFF',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Litres'
                    }
                }
            }
        }
    });
}

/**
 * Initialise le graphique d'évolution des coûts de carburant
 */
function initializeFuelCostChart() {
    const fuelCostCtx = document.getElementById('fuelCostChart').getContext('2d');
    new Chart(fuelCostCtx, {
        type: 'bar',
        data: {
            labels: fuelData.months,
            datasets: [{
                label: 'Coût total (FCFA)',
                data: fuelData.costs,
                backgroundColor: '#28a745',
                borderWidth: 1
            }, {
                label: 'Prix moyen/L (FCFA)',
                data: fuelData.averagePrices,
                type: 'line',
                borderColor: '#dc3545',
                backgroundColor: 'transparent',
                pointBackgroundColor: '#dc3545',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Coût total (FCFA)'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Prix moyen/L (FCFA)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

/**
 * Initialise le graphique de répartition des types de maintenance
 */
function initializeMaintenanceTypeChart() {
    const maintenanceTypeCtx = document.getElementById('maintenanceTypeChart').getContext('2d');
    new Chart(maintenanceTypeCtx, {
        type: 'doughnut',
        data: {
            labels: maintenanceData.types,
            datasets: [{
                data: maintenanceData.counts,
                backgroundColor: ['#4E73DF', '#1CC88A', '#36B9CC', '#F6C23E'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

/**
 * Initialise le graphique des coûts de maintenance par type
 */
function initializeMaintenanceCostChart() {
    const maintenanceCostCtx = document.getElementById('maintenanceCostChart').getContext('2d');
    new Chart(maintenanceCostCtx, {
        type: 'bar',
        data: {
            labels: maintenanceData.types,
            datasets: [{
                label: 'Coût total (FCFA)',
                data: maintenanceData.costs,
                backgroundColor: '#4E73DF',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Coût (FCFA)'
                    }
                }
            }
        }
    });
}

/**
 * Configure les boutons d'export
 */
function setupExportButtons() {
    $('#exportStatsCsv').click(function () {
        exportStats('csv');
    });

    $('#exportStatsPdf').click(function () {
        exportStats('pdf');
    });
}

/**
 * Exporte les statistiques dans le format spécifié
 * @param {string} format - Format d'export (csv/pdf)
 */
function exportStats(format) {
    window.location.href = `export_vehicle_stats.php?id=${vehiculeId}&format=${format}`;
}

/**
 * Renvoie la classe CSS appropriée pour un badge de statut
 * @param {string} status - Statut du véhicule
 * @return {string} - Classe CSS pour le badge
 */
function getStatusBadgeClass(status) {
    switch (status) {
        case 'disponible':
            return 'bg-success';
        case 'en_course':
            return 'bg-warning';
        case 'maintenance':
            return 'bg-info';
        case 'hors_service':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

/**
 * Formate le type de document pour l'affichage
 * @param {string} type - Type de document
 * @return {string} - Libellé formaté
 */
function formatDocumentType(type) {
    const types = {
        'carte_transport': 'Carte de transport',
        'carte_grise': 'Carte grise',
        'visite_technique': 'Visite technique',
        'assurance': 'Assurance',
        'carte_stationnement': 'Carte de stationnement'
    };

    return types[type] || type;
}