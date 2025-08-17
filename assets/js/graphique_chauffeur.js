// Exemple de graphique pour l'analyse des chauffeurs
const ctx = document.getElementById('chauffeurChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Actifs', 'Suspendus', 'Licenciés'],
        datasets: [{
            label: 'Nombre de Chauffeurs',
            data: [10, 3, 1],  // Remplacez par des données réelles
            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
            borderColor: ['#28a745', '#ffc107', '#dc3545'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});