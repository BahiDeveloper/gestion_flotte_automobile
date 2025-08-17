// Fonction pour afficher une confirmation SweetAlert avant une action
function confirmerAction(action, titre, message, redirectUrl) {
    Swal.fire({
        title: titre,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'terminer' ? '#28a745' :
            (action === 'demarrer' ? '#007bff' : '#dc3545'),
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, ' + action,
        cancelButtonText: 'Annuler',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirection vers l'URL d'action
            window.location.href = redirectUrl;
        }
    });
}

// Assurez-vous que SweetAlert est chargé
document.addEventListener('DOMContentLoaded', function () {
    // Vérifier si SweetAlert est disponible
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert n\'est pas chargé. Veuillez ajouter la bibliothèque à votre page.');
    }
});