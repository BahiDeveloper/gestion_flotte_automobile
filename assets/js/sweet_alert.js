function showSweetAlert(event, link) {
    event.preventDefault();  // Empêche le lien de s'exécuter immédiatement

    // Récupérer les données personnalisées depuis les attributs du lien
    const message = link.getAttribute('data-message');
    const action = link.getAttribute('data-action');

    Swal.fire({
        icon: 'warning',
        title: 'Êtes-vous sûr ?',
        text: message,  // Texte dynamique
        showCancelButton: true,
        confirmButtonText: 'Oui, ' + action,  // Dynamique aussi
        cancelButtonText: 'Non, garder',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Si l'utilisateur confirme, on redirige vers l'URL du lien
            window.location.href = link.href;
        }
    });
}

