<?php
try {
    // Requête pour récupérer les valeurs ENUM de type_vehicule
    $sql = "SHOW COLUMNS FROM vehicules LIKE 'type_vehicule'";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extraction des valeurs ENUM
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $enum_values = str_getcsv(str_replace("'", "", $matches[1]));

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

try {
    // Requête pour récupérer les valeurs ENUM de type_carburant
    $sql = "SHOW COLUMNS FROM vehicules LIKE 'type_carburant'";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extraction des valeurs ENUM
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $enum_values_type_carburant = str_getcsv(str_replace("'", "", $matches[1]));

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

try {
    // Requête pour récupérer les véhicules avec leur zone
    $sql = "SELECT v.*, z.nom_zone 
FROM vehicules v 
LEFT JOIN zone_vehicules z ON v.id_zone = z.id
ORDER BY v.date_acquisition DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

?>