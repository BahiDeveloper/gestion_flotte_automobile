<?php
// Inclure le fichier de configuration de la base de données
include_once("../../database/config.php");

// Vérifier que l'email est passé en paramètre GET
if (!isset($_GET['email'])) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'message' => 'Email non fourni']));
}

// Récupérer et nettoyer l'email
$email = trim($_GET['email']);

// Valider l'email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'message' => 'Email invalide']));
}

try {
    // Préparer la requête pour vérifier l'existence de l'email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $emailExists = $stmt->fetchColumn() > 0;

    // Répondre avec le résultat
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'exists' => $emailExists
    ]);
    exit;

} catch (PDOException $e) {
    // Gérer les erreurs de base de données
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode([
        'success' => false,
        'message' => 'Erreur de vérification de l\'email : ' . $e->getMessage()
    ]));
}