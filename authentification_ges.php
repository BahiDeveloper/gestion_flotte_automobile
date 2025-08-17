<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification et Gestion des utilisateurs</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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

        .btn-success {
            background-color: #27ae60;
            border: none;
        }

        .btn-warning {
            background-color: #f39c12;
            border: none;
        }

        .btn-danger {
            background-color: #e74c3c;
            border: none;
        }

        .btn-success:hover,
        .btn-warning:hover,
        .btn-danger:hover {
            opacity: 0.9;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            color: #2c3e50;
            font-weight: 600;
        }

        .badge {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .badge-admin {
            background-color: #3498db;
        }

        .badge-manager {
            background-color: #f39c12;
        }

        .badge-user {
            background-color: #27ae60;
        }

        .nav-tabs .nav-link {
            color: #2c3e50;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            color: #3498db;
            border-bottom: 3px solid #3498db;
        }

        .nav-tabs .nav-link:hover {
            color: #3498db;
            border-bottom: 3px solid #3498db;
        }
    </style>
</head>

<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">Gestion de Flotte</a>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container my-5">
        <h1 class="text-center mb-4" style="color: #2c3e50; font-weight: 700;">Authentification et Gestion des
            utilisateurs</h1>

        <!-- Onglets -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button"
                    role="tab" aria-controls="login" aria-selected="true">
                    <i class="fas fa-sign-in-alt me-2"></i>Connexion
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button"
                    role="tab" aria-controls="users" aria-selected="false">
                    <i class="fas fa-users-cog me-2"></i>Gestion des utilisateurs
                </button>
            </li>
        </ul>

        <!-- Contenu des onglets -->
        <div class="tab-content" id="myTabContent">
            <!-- Onglet Connexion -->
            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                <div class="section">
                    <h2><i class="fas fa-sign-in-alt me-2"></i>Connexion</h2>
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-select" id="role" required>
                                <option value="">Sélectionnez un rôle</option>
                                <option value="admin">Administrateur</option>
                                <option value="manager">Gestionnaire</option>
                                <option value="user">Utilisateur</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Onglet Gestion des utilisateurs -->
            <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                <div class="section">
                    <h2><i class="fas fa-users-cog me-2"></i>Gestion des utilisateurs</h2>
                    <div class="mb-4">
                        <h4>Liste des utilisateurs</h4>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Utilisateur 1</h5>
                                <p class="card-text">Rôle : <span class="badge badge-admin">Administrateur</span></p>
                                <button class="btn btn-warning btn-action">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </button>
                                <button class="btn btn-danger btn-action">
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </button>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Utilisateur 2</h5>
                                <p class="card-text">Rôle : <span class="badge badge-manager">Gestionnaire</span></p>
                                <button class="btn btn-warning btn-action">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </button>
                                <button class="btn btn-danger btn-action">
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </button>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Utilisateur 3</h5>
                                <p class="card-text">Rôle : <span class="badge badge-user">Utilisateur</span></p>
                                <button class="btn btn-warning btn-action">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </button>
                                <button class="btn btn-danger btn-action">
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4>Ajouter un nouvel utilisateur</h4>
                        <form id="userForm">
                            <div class="mb-3">
                                <label for="newUsername" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="newUsername" required>
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="newPassword" required>
                            </div>
                            <div class="mb-3">
                                <label for="newRole" class="form-label">Rôle</label>
                                <select class="form-select" id="newRole" required>
                                    <option value="">Sélectionnez un rôle</option>
                                    <option value="admin">Administrateur</option>
                                    <option value="manager">Gestionnaire</option>
                                    <option value="user">Utilisateur</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Ajouter
                            </button>
                        </form>
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
        // Gestion du formulaire de connexion
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            alert('Connexion réussie !');
        });

        // Gestion du formulaire d'ajout d'utilisateur
        document.getElementById('userForm').addEventListener('submit', function (e) {
            e.preventDefault();
            alert('Utilisateur ajouté avec succès !');
        });
    </script>
</body>

</html>