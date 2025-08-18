<?php

$host = 'localhost'; // Adresse du serveur de base de données
$dbname = 'gestion_flotte'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur de la base de données
$password = ''; // Gomp@225 Mot de passe de la base de données

date_default_timezone_set('Africa/Abidjan');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>