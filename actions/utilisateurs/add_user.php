<?php
session_start();

// Inclure le fichier de configuration de la base de données
include_once("../../database/config.php");

// Vérifier que la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(json_encode(['success' => false, 'message' => 'Méthode non autorisée']));
}

// Vérifier l'authentification et les droits d'accès
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}

// Récupérer les données du formulaire
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$role = trim($_POST['role'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

// Validation des données
$errors = [];

if (empty($nom)) {
    $errors[] = "Le nom est requis.";
}

if (empty($prenom)) {
    $errors[] = "Le prénom est requis.";
}

if (empty($email)) {
    $errors[] = "L'email est requis.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email n'est pas valide.";
}

if (empty($role)) {
    $errors[] = "Le rôle est requis.";
}

if (empty($mot_de_passe)) {
    $errors[] = "Le mot de passe est requis.";
} elseif (strlen($mot_de_passe) < 8) {
    $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
}

// S'il y a des erreurs, renvoyer une réponse d'erreur
if (!empty($errors)) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]));
}

// Commencer une transaction
$pdo->beginTransaction();

try {
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $emailExists = $stmt->fetchColumn() > 0;

    if ($emailExists) {
        throw new Exception("Cet email est déjà utilisé.");
    }

    // Hacher le mot de passe
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Préparer la requête d'insertion
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs 
        (nom, prenom, email, telephone, role, mot_de_passe, date_creation, actif) 
        VALUES 
        (?, ?, ?, ?, ?, ?, NOW(), 1)
    ");

    // Exécuter la requête
    $stmt->execute([
        $nom,
        $prenom,
        $email,
        $telephone ?: null,
        $role,
        $mot_de_passe_hash
    ]);

    // Récupérer l'ID de l'utilisateur nouvellement créé
    $nouvel_utilisateur_id = $pdo->lastInsertId();

    // Journaliser l'activité
    $stmt_journal = $pdo->prepare("
        INSERT INTO journal_activites 
        (id_utilisateur, type_activite, description, date_activite, ip_address) 
        VALUES 
        (?, 'creation_utilisateur', ?, NOW(), ?)
    ");
    $stmt_journal->execute([
        $_SESSION['id_utilisateur'],
        "Création de l'utilisateur {$nom} {$prenom}",
        $_SERVER['REMOTE_ADDR']
    ]);

    // Valider la transaction
    $pdo->commit();

    // Répondre avec succès
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur ajouté avec succès',
        'utilisateur_id' => $nouvel_utilisateur_id
    ]);
    exit;

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();

    // Répondre avec l'erreur
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}