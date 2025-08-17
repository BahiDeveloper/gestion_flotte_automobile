/**
 * Script pour améliorer l'expérience sur les petits écrans
 * A inclure après les scripts existants
 */
document.addEventListener('DOMContentLoaded', function() {
    // Ajout d'un indicateur de scroll pour tableaux sur mobile
    enhanceTableResponsiveness();
    
    // Repositionnement des boutons de téléchargement de graphiques
    fixChartDownloadButtons();
    
    // Optimiser l'affichage des graphiques sur petits écrans
    optimizeChartsForMobile();
    
    // Ajouter des boutons d'action rapide en bas d'écran (pour mobiles)
    addMobileActionBar();
  });
  
  /**
   * Améliore la navigation dans les tableaux sur petits écrans
   */
  function enhanceTableResponsiveness() {
    if (window.innerWidth <= 768) {
      const tables = document.querySelectorAll('.table-responsive');
      
      tables.forEach(tableWrapper => {
        // Vérifier si l'élément existe déjà pour éviter les doublons
        if (!tableWrapper.querySelector('.mobile-scroll-indicator')) {
          const scrollIndicator = document.createElement('div');
          scrollIndicator.className = 'mobile-scroll-indicator';
          scrollIndicator.style.cssText = 'text-align: center; font-size: 0.8rem; color: #6c757d; padding: 5px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;';
          scrollIndicator.innerHTML = '<i class="fas fa-arrows-alt-h me-1"></i>Faites défiler horizontalement';
          
          // Ajouter au début du wrapper
          if (tableWrapper.firstChild) {
            tableWrapper.insertBefore(scrollIndicator, tableWrapper.firstChild);
          } else {
            tableWrapper.appendChild(scrollIndicator);
          }
          
          // Masquer l'indicateur après un délai
          setTimeout(() => {
            scrollIndicator.style.opacity = '0.5';
          }, 3000);
        }
      });
    }
  }
  
  /**
   * Corrige le positionnement des boutons de téléchargement sur les graphiques circulaires
   */
  function fixChartDownloadButtons() {
    // Cibler spécifiquement les contrôles des graphiques circulaires
    const donutCharts = [
      document.getElementById('chartTypes'), 
      document.getElementById('chartStatuts'),
      document.getElementById('chartCarburant')
    ].filter(Boolean);
    
    donutCharts.forEach(chart => {
      if (chart) {
        const chartContainer = chart.closest('.card-body');
        if (chartContainer) {
          // S'assurer que le conteneur a un positionnement relatif
          chartContainer.style.position = 'relative';
          
          // Trouver le contrôle existant ou en créer un nouveau
          let controlsDiv = chartContainer.querySelector('.chart-controls');
          
          if (!controlsDiv) {
            // Si le contrôle n'existe pas encore, on le crée
            controlsDiv = document.createElement('div');
            controlsDiv.className = 'chart-controls';
            controlsDiv.style.cssText = 'position: absolute; bottom: 0; left: 0; right: 0; display: flex; justify-content: center; padding-bottom: 10px; z-index: 10;';
            
            const downloadBtn = document.createElement('button');
            downloadBtn.className = 'btn btn-sm chart-download';
            downloadBtn.setAttribute('data-chart', chart.id);
            downloadBtn.innerHTML = '<i class="fas fa-download me-1"></i>Télécharger';
            downloadBtn.addEventListener('click', function() {
              downloadChart(chart.id);
            });
            
            controlsDiv.appendChild(downloadBtn);
            chartContainer.appendChild(controlsDiv);
          } else {
            // Si le contrôle existe, on ajuste sa position
            controlsDiv.style.cssText = 'position: absolute; bottom: 0; left: 0; right: 0; display: flex; justify-content: center; padding-bottom: 10px; z-index: 10;';
          }
        }
      }
    });
  }
  
  /**
   * Optimise les options des graphiques pour petits écrans
   */
  function optimizeChartsForMobile() {
    if (window.innerWidth <= 768) {
      // Si Chart.js est chargé et que des instances existent
      if (window.Chart && Chart.instances) {
        // Récupérer toutes les instances de graphiques
        const charts = Object.values(Chart.instances);
        
        charts.forEach(chart => {
          // Ajuster selon le type de graphique
          if (chart.config.type === 'line') {
            // Pour les graphiques linéaires
            if (chart.options.plugins && chart.options.plugins.legend) {
              chart.options.plugins.legend.position = 'bottom';
            }
            if (chart.options.scales && chart.options.scales.x && chart.options.scales.x.ticks) {
              chart.options.scales.x.ticks.maxRotation = 90;
              chart.options.scales.x.ticks.autoSkip = true;
              chart.options.scales.x.ticks.maxTicksLimit = 6;
            }
          } 
          else if (chart.config.type === 'bar') {
            // Pour les graphiques à barres
            if (chart.options.plugins && chart.options.plugins.legend) {
              chart.options.plugins.legend.position = 'bottom';
            }
            if (chart.options.scales && chart.options.scales.x && chart.options.scales.x.ticks) {
              chart.options.scales.x.ticks.maxRotation = 90;
              chart.options.scales.x.ticks.minRotation = 75;
            }
          }
          else if (chart.config.type === 'doughnut') {
            // Pour les graphiques circulaires
            if (chart.options.plugins && chart.options.plugins.legend) {
              chart.options.plugins.legend.position = 'bottom';
              if (chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.boxWidth = 12;
              }
            }
          }
          
          // Appliquer les changements
          chart.update();
        });
      }
    }
  }
  
  /**
   * Télécharge le graphique en tant qu'image
   */
  function downloadChart(chartId) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    // Créer un lien de téléchargement temporaire
    const downloadLink = document.createElement('a');
    
    // Configurer le nom de fichier selon le type de graphique
    let fileName = 'graphique.png';
    
    if (chartId === 'chartEvolution') {
      fileName = 'evolution_maintenances.png';
    } else if (chartId === 'chartTypes') {
      fileName = 'repartition_types_maintenance.png';
    } else if (chartId === 'chartStatuts') {
      fileName = 'repartition_statuts_maintenance.png';
    } else if (chartId === 'chartVehicules') {
      fileName = 'cout_maintenances_vehicules.png';
    } else if (chartId === 'chartCarburant') {
      fileName = 'repartition_carburant.png';
    }
    
    // Configurer et déclencher le téléchargement
    downloadLink.download = fileName;
    downloadLink.href = canvas.toDataURL('image/png');
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
  }
  
  /**
   * Ajoute une barre d'action fixe en bas d'écran sur mobile
   */
  function addMobileActionBar() {
    if (window.innerWidth <= 768) {
      // Vérifier si la barre n'existe pas déjà
      if (!document.getElementById('mobile-action-bar')) {
        const actionBar = document.createElement('div');
        actionBar.id = 'mobile-action-bar';
        actionBar.style.cssText = 'position: fixed; bottom: 0; left: 0; right: 0; background-color: #fff; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-around; padding: 10px; z-index: 1000;';
        
        // Boutons d'action communs
        actionBar.innerHTML = `
          <button class="btn btn-sm btn-primary" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
            <i class="fas fa-arrow-up"></i>
          </button>
          <button class="btn btn-sm btn-success" onclick="window.print()">
            <i class="fas fa-print"></i>
          </button>
          <a href="gestion_vehicules.php" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
          </a>
        `;
        
        // Ajouter au body
        document.body.appendChild(actionBar);
        
        // Ajouter un espace en bas du contenu pour éviter que la barre ne masque du contenu
        const spacer = document.createElement('div');
        spacer.style.height = '60px';
        document.querySelector('.container-fluid').appendChild(spacer);
      }
    }
  }