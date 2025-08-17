<?php
// Inclure le fichier de configuration de la base de données
include_once("database" . DIRECTORY_SEPARATOR . "config.php");

try {
    // Récupérer les chauffeurs disponibles
    $sql = "SELECT * FROM chauffeurs";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $chauffeurs_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Erreur lors de la récupération des chauffeurs : " . addslashes($e->getMessage()) . "',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'page_assignations.php';
        });
    </script>";
    exit();
}

try {
    // Récupérer les chauffeurs disponibles
    $sql = "SELECT * FROM chauffeurs WHERE disponibilite = 'Disponible'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $chauffeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Erreur lors de la récupération des chauffeurs : " . addslashes($e->getMessage()) . "',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'page_assignations.php';
        });
    </script>";
    exit();
}

?>
