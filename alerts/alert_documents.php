<?php
// Vérification des notifications à afficher
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> ' . $_SESSION['success'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>';
    unset($_SESSION['error']);
}

// Vérifier s'il y a des documents qui expirent bientôt pour afficher une alerte globale
// Compter les documents dans chaque catégorie d'alerte
$urgent_count = 0;
$warning_count = 0;
$info_count = 0;

foreach ($documents as $doc) {
    if ($doc['statut_alerte'] == 'urgente') {
        $urgent_count++;
    } elseif ($doc['statut_alerte'] == 'proche') {
        $warning_count++;
    } elseif ($doc['statut_alerte'] == 'attention') {
        $info_count++;
    }
}

// Afficher une alerte globale si des documents expirent bientôt
if ($urgent_count > 0) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle fa-pulse"></i> <strong>Attention !</strong> 
            ' . $urgent_count . ' document' . ($urgent_count > 1 ? 's' : '') . ' expire' . ($urgent_count > 1 ? 'nt' : '') . ' dans moins d\'une semaine.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>';
} elseif ($warning_count > 0) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Avertissement</strong> 
            ' . $warning_count . ' document' . ($warning_count > 1 ? 's' : '') . ' expire' . ($warning_count > 1 ? 'nt' : '') . ' dans moins d\'un mois.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>';
} elseif ($info_count > 0 && !isset($_COOKIE['info_alert_dismissed'])) {
    echo '<div class="alert alert-info alert-dismissible fade show" role="alert" id="info-alert">
            <i class="fas fa-info-circle"></i> <strong>Information</strong> 
            ' . $info_count . ' document' . ($info_count > 1 ? 's' : '') . ' expire' . ($info_count > 1 ? 'nt' : '') . ' dans moins de deux mois.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer" onclick="dismissInfoAlert()"></button>
          </div>
          <script>
          function dismissInfoAlert() {
              // Définir un cookie pour éviter d\'afficher cette alerte trop souvent
              let date = new Date();
              date.setTime(date.getTime() + (24 * 60 * 60 * 1000)); // 24 heures
              document.cookie = "info_alert_dismissed=1; expires=" + date.toUTCString() + "; path=/";
          }
          </script>';
}
?>