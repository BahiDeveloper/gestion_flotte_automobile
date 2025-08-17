<?php
// config/config.php
// define('PROJECT_ROOT', 'http://localhost:8080');

// define('PROJECT_ROOT', '/DYM/gestion_flotte_automobile-up_complet');
// define('PROJECT_ROOT', '');
define('PROJECT_ROOT', '/DYM MANUFACTURE/gestion_flotte_automobile');

// Fonction utilitaire à ajouter dans un fichier d'utilitaires ou là où vous en avez besoin
function formatIvorianPhone($phone) {
    // Supprimer tous les caractères non numériques
    $cleaned = preg_replace('/\D/', '', $phone);

    // Formater le numéro (XX XX XX XX XX)
    if (strlen($cleaned) === 10) {
        return chunk_split($cleaned, 2, ' ');
    }

    // Retourner le numéro tel quel s'il n'a pas 10 chiffres
    return $phone;
}
