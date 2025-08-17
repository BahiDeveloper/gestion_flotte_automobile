<?php
// Inclure le fichier de configuration de la base de données
include_once("database".DIRECTORY_SEPARATOR."config.php");


// Récupérer les assignations en cours depuis la base de données
$sql = "SELECT 
            d.id as assignation_id, 
            v.marque, 
            v.modele, 
            v.logo_marque_vehicule, 
            v.etat, 
            d.objet_demande, 
            d.trajet, 
            d.date_depart_prevue, 
            d.date_arrivee_prevue,
            d.id_chauffeur,  -- Ajout de l'ID du chauffeur
            d.id_vehicule     -- Ajout de l'ID du véhicule
        FROM deplacements d
        JOIN vehicules v ON d.id_vehicule = v.id
        WHERE d.id_chauffeur IS NULL"; // Ne pas filtrer par état du véhicule

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $assignations_demande = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // var_dump($assignations_demande); 

} catch (PDOException $e) {
    die("Erreur lors de la récupération des assignations : " . $e->getMessage());
}


// Récupérer les assignations en cours avec le nom du chauffeur
$sql = "SELECT 
            d.id as assignation_id, 
            v.marque, 
            v.modele, 
            v.logo_marque_vehicule, 
            v.etat, 
            d.objet_demande, 
            d.etat_course,
            d.etat,
            d.trajet, 
            d.date_depart_prevue, 
            d.date_arrivee_prevue, 
            c.nom as chauffeur_nom,  -- Ajout du nom du chauffeur
            c.prenom as chauffeur_prenom  -- Ajout du nom du chauffeur
        FROM deplacements d
        JOIN vehicules v ON d.id_vehicule = v.id
        JOIN chauffeurs c ON d.id_chauffeur = c.id  -- Jointure avec la table chauffeurs
        WHERE d.id_chauffeur  IS NOT NULL 
        AND  d.etat_course = 'en_cours'
         "; // Seules les assignations confirmées sont affichées

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $assignations_accepte = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // var_dump($assignations_accepte);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des assignations : " . $e->getMessage());
}

// <!-- Historique des assignations -->
$sql = "SELECT 
            d.id as assignation_id, 
            v.marque, 
            v.modele, 
            v.logo_marque_vehicule, 
            v.etat, 
            d.objet_demande, 
            d.etat,
            d.etat_course,
            d.trajet, 
            d.date_depart, 
            d.date_depart_prevue, 
            d.date_arrivee_prevue, 
            d.date_arrivee, 
            c.nom as chauffeur_nom,  -- Ajout du nom du chauffeur
            c.prenom as chauffeur_prenom  -- Ajout du nom du chauffeur
        FROM deplacements d
        JOIN vehicules v ON d.id_vehicule = v.id
        JOIN chauffeurs c ON d.id_chauffeur = c.id  -- Jointure avec la table chauffeurs
        WHERE d.etat_course = 'terminee'";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $historique_assignations_terminee = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // var_dump($assignations_accepte);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des assignations : " . $e->getMessage());
}

// <!-- Historique des assignations -->
$sql = "SELECT 
            d.id as assignation_id, 
            v.marque, 
            v.modele, 
            v.logo_marque_vehicule, 
            v.etat, 
            d.objet_demande, 
            d.etat,
            d.etat_course,
            d.trajet, 
            d.date_depart, 
            d.date_depart_prevue, 
            d.date_arrivee_prevue, 
            d.date_arrivee, 
            c.nom as chauffeur_nom,  -- Ajout du nom du chauffeur
            c.prenom as chauffeur_prenom  -- Ajout du nom du chauffeur
        FROM deplacements d
        JOIN vehicules v ON d.id_vehicule = v.id
        JOIN chauffeurs c ON d.id_chauffeur = c.id  -- Jointure avec la table chauffeurs
        WHERE d.etat_course = 'annulee' ";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $historique_assignations_annulee = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // var_dump($assignations_accepte);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des assignations : " . $e->getMessage());
}





?>