<?php
// Inclure le fichier de configuration de la base de données
include_once("../../database/config.php");

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté (présence de l'ID utilisateur)
if (!isset($_SESSION['id_utilisateur'])) {
    $_SESSION['alert_message'] = "Vous devez vous connecter pour effectuer cette action.";
    $_SESSION['alert_type'] = "danger";
    header('Location: ../../auth/views/login.php');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier les permissions (seuls admin et gestionnaire autorisés)
    $role = $_SESSION['role'];
    if ($role !== 'administrateur' && $role !== 'gestionnaire') {
        $_SESSION['alert_message'] = "Vous n'avez pas les permissions nécessaires pour effectuer cette action.";
        $_SESSION['alert_type'] = "danger";
        header('Location: ../../gestion_vehicules.php#approvisionnements');
        exit;
    }
    
    // Récupérer les données du formulaire
    $id_approvisionnement = isset($_POST['id_approvisionnement']) ? intval($_POST['id_approvisionnement']) : 0;
    $id_vehicule = isset($_POST['id_vehicule']) ? intval($_POST['id_vehicule']) : null;
    $id_chauffeur = isset($_POST['id_chauffeur']) && !empty($_POST['id_chauffeur']) ? intval($_POST['id_chauffeur']) : null;
    $date_approvisionnement = isset($_POST['date_approvisionnement']) ? $_POST['date_approvisionnement'] : null;
    $quantite_litres = isset($_POST['quantite_litres']) ? floatval($_POST['quantite_litres']) : 0;
    $prix_unitaire = isset($_POST['prix_unitaire']) ? intval($_POST['prix_unitaire']) : 0;
    $prix_total = isset($_POST['prix_total']) ? intval($_POST['prix_total']) : 0;
    $kilometrage = isset($_POST['kilometrage']) ? intval($_POST['kilometrage']) : 0;
    $station_service = isset($_POST['station_service']) ? trim($_POST['station_service']) : '';
    
    // Correction du type de carburant selon la correspondance
    $type_carburant = isset($_POST['type_carburant']) ? $_POST['type_carburant'] : 'diesel';
    switch ($type_carburant) {
        case 'Super':
            $type_carburant = 'essence';
            break;
        case 'Gasoil':
            $type_carburant = 'diesel';
            break;
        case 'Essence':
            $type_carburant = 'hybride';
            break;
    }
    
    // Validation des données
    if (!$id_approvisionnement || !$id_vehicule || !$date_approvisionnement) {
        $_SESSION['alert_message'] = "Toutes les informations nécessaires n'ont pas été fournies.";
        $_SESSION['alert_type'] = "danger";
        header('Location: ../../gestion_vehicules.php#approvisionnements');
        exit;
    }
    
    try {
        // Mettre à jour l'approvisionnement dans la base de données
        $stmt = $pdo->prepare("
            UPDATE approvisionnements_carburant 
            SET id_vehicule = ?, 
                id_chauffeur = ?, 
                date_approvisionnement = ?, 
                quantite_litres = ?, 
                prix_unitaire = ?, 
                prix_total = ?, 
                kilometrage = ?, 
                station_service = ?, 
                type_carburant = ?
            WHERE id_approvisionnement = ?
        ");
        
        $result = $stmt->execute([
            $id_vehicule,
            $id_chauffeur,
            $date_approvisionnement,
            $quantite_litres,
            $prix_unitaire,
            $prix_total,
            $kilometrage,
            $station_service,
            $type_carburant,
            $id_approvisionnement
        ]);
        
        // Vérifier si une ligne a été effectivement mise à jour
        if ($result && $stmt->rowCount() > 0) {
            // Mettre à jour le kilométrage du véhicule si nécessaire
            $stmt_vehicule = $pdo->prepare("
                SELECT kilometrage_actuel FROM vehicules WHERE id_vehicule = ?
            ");
            $stmt_vehicule->execute([$id_vehicule]);
            $vehicule = $stmt_vehicule->fetch(PDO::FETCH_ASSOC);
            
            // Si le kilométrage de l'approvisionnement est supérieur à celui du véhicule, mettre à jour
            if ($vehicule && $kilometrage > $vehicule['kilometrage_actuel']) {
                $stmt_update_km = $pdo->prepare("
                    UPDATE vehicules SET kilometrage_actuel = ? WHERE id_vehicule = ?
                ");
                $stmt_update_km->execute([$kilometrage, $id_vehicule]);
            }
            
            // Rediriger avec un message de succès
            $_SESSION['alert_message'] = "L'approvisionnement a été mis à jour avec succès.";
            $_SESSION['alert_type'] = "success";
            header('Location: ../../gestion_vehicules.php#approvisionnements?success_approvisionnement_edit=1&debug=true');            exit;
        } else {
            // Aucune mise à jour effectuée
            $_SESSION['alert_message'] = "Aucune modification n'a été apportée à l'approvisionnement.";
            $_SESSION['alert_type'] = "warning";
            header('Location: ../../gestion_vehicules.php#approvisionnements');
            exit;
        }
        
    } catch (PDOException $e) {
        // En cas d'erreur, rediriger avec un message d'erreur
        $_SESSION['alert_message'] = "Erreur lors de la mise à jour de l'approvisionnement : " . $e->getMessage();
        $_SESSION['alert_type'] = "danger";
        header('Location: ../../gestion_vehicules.php#approvisionnements');
        exit;
    }
    
} else {
    // Si le formulaire n'a pas été soumis, rediriger
    header('Location: ../../gestion_vehicules.php#approvisionnements');
    exit;
}
?>