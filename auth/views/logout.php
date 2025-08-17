<?php
session_start();

// Inclure le contrôleur d'authentification
require_once '../controllers/auth_controller.php';

// Créer une instance du contrôleur d'authentification
$authController = new AuthController();

// Appeler la méthode de déconnexion
$authController->logout();