<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de l'utilisation des véhicules</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar-custom {
            background-color: #2c3e50;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-custom .navbar-brand {
            color: #fff;
            font-weight: bold;
        }

        .nav-tabs .nav-link {
            color: #2c3e50;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
        }

        .nav-tabs .nav-link:hover {
            color: #3498db;
        }

        .section {
            background-color: #fff;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .section h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: #34495e;
        }

        .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-export {
            background-color: #27ae60;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-export:hover {
            background-color: #219653;
        }

        .chart-container {
            margin-top: 2rem;
        }
    </style>
</head>

<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index-1.php">Gestion de Flotte</a>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container my-5">
        <h1 class="text-center mb-4" style="color: #2c3e50; font-weight: 700;">Suivi de l'utilisation des véhicules</h1>

        <!-- Onglets de navigation -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="data-entry-tab" data-bs-toggle="tab" data-bs-target="#data-entry"
                    type="button" role="tab" aria-controls="data-entry" aria-selected="true">
                    <i class="fas fa-pencil-alt me-2"></i>Saisie des données
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button"
                    role="tab" aria-controls="reports" aria-selected="false">
                    <i class="fas fa-chart-bar me-2"></i>Rapports et statistiques
                </button>
            </li>
        </ul>

        <!-- Contenu des onglets -->
        <div class="tab-content" id="myTabContent">
            <!-- Saisie des données -->
            <div class="tab-pane fade show active" id="data-entry" role="tabpanel" aria-labelledby="data-entry-tab">
                <div class="section">
                    <h2><i class="fas fa-pencil-alt me-2"></i>Saisie des données</h2>
                    <form id="dataEntryForm">
                        <div class="mb-3">
                            <label for="vehicle" class="form-label">Véhicule</label>
                            <select class="form-select" id="vehicle" required>
                                <option value="">Sélectionnez un véhicule</option>
                                <option value="1">Voiture 1</option>
                                <option value="2">Voiture 2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="startMileage" class="form-label">Kilométrage de début</label>
                            <input type="number" class="form-control" id="startMileage" required>
                        </div>
                        <div class="mb-3">
                            <label for="endMileage" class="form-label">Kilométrage de fin</label>
                            <input type="number" class="form-control" id="endMileage" required>
                        </div>
                        <div class="mb-3">
                            <label for="fuelQuantity" class="form-label">Quantité de carburant (L)</label>
                            <input type="number" class="form-control" id="fuelQuantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="fuelPrice" class="form-label">Prix du carburant (€)</label>
                            <input type="number" class="form-control" id="fuelPrice" required>
                        </div>
                        <div class="mb-3">
                            <label for="fuelDate" class="form-label">Date de l'achat</label>
                            <input type="date" class="form-control" id="fuelDate" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>

            <!-- Rapports et statistiques -->
            <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">
                <div class="section">
                    <h2><i class="fas fa-chart-bar me-2"></i>Rapports et statistiques</h2>
                    <div class="mb-4">
                        <button class="btn btn-export me-2">
                            <i class="fas fa-file-pdf me-2"></i>Exporter en PDF
                        </button>
                        <button class="btn btn-export">
                            <i class="fas fa-file-excel me-2"></i>Exporter en Excel
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="mileageChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="fuelConsumptionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS et dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Gestion du formulaire de saisie des données
        document.getElementById('dataEntryForm').addEventListener('submit', function (e) {
            e.preventDefault();
            alert('Données enregistrées avec succès !');
        });

        // Graphique pour le kilométrage
        const mileageCtx = document.getElementById('mileageChart').getContext('2d');
        const mileageChart = new Chart(mileageCtx, {
            type: 'bar',
            data: {
                labels: ['Voiture 1', 'Voiture 2'],
                datasets: [{
                    label: 'Kilométrage total (km)',
                    data: [1200, 800],
                    backgroundColor: ['#3498db', '#2ecc71'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Kilométrage total par véhicule'
                    }
                }
            }
        });

        // Graphique pour la consommation de carburant
        const fuelCtx = document.getElementById('fuelConsumptionChart').getContext('2d');
        const fuelChart = new Chart(fuelCtx, {
            type: 'line',
            data: {
                labels: ['Voiture 1', 'Voiture 2'],
                datasets: [{
                    label: 'Consommation (L/100 km)',
                    data: [8.5, 7.2],
                    borderColor: '#e74c3c',
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Consommation de carburant par véhicule'
                    }
                }
            }
        });
    </script>
</body>

</html>