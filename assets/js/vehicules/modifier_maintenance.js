document.addEventListener('DOMContentLoaded', function () {
    const updateForm = document.getElementById('updateMaintenanceForm');
    const statutSelect = document.getElementById('statut');
    const dateFinEffectiveInput = document.getElementById('date_fin_effective');

    // Mettre à jour l'état du champ date_fin_effective en fonction du statut
    statutSelect.addEventListener('change', function () {
        if (this.value === 'terminee') {
            const today = new Date().toISOString().split('T')[0];
            dateFinEffectiveInput.value = today;
            dateFinEffectiveInput.disabled = false;
            dateFinEffectiveInput.required = true;
        } else {
            dateFinEffectiveInput.value = '';
            dateFinEffectiveInput.disabled = true;
            dateFinEffectiveInput.required = false;
        }
    });

    // Validation des dates
    updateForm.addEventListener('submit', function (event) {
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normaliser la date à minuit

        const dateDebut = new Date(document.getElementById('date_debut').value);
        const dateFinPrevue = new Date(document.getElementById('date_fin_prevue').value);
        const dateFinEffective = dateFinEffectiveInput.value
            ? new Date(dateFinEffectiveInput.value)
            : null;

        // Vérifier que la date de début n'est pas dans le passé (sauf si maintenance déjà commencée)
        const statutActuel = document.getElementById('statut').getAttribute('data-statut-initial');
        if (dateDebut < today && statutActuel === 'planifiee') {
            event.preventDefault();
            Swal.fire({
                title: 'Date invalide',
                text: 'La date de début ne peut pas être dans le passé.',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        if (dateFinPrevue < dateDebut) {
            event.preventDefault();
            Swal.fire({
                title: 'Erreur de date',
                text: 'La date de fin prévue ne peut pas être antérieure à la date de début.',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        if (dateFinEffective && dateFinEffective < dateDebut) {
            event.preventDefault();
            Swal.fire({
                title: 'Erreur de date',
                text: 'La date de fin effective ne peut pas être antérieure à la date de début.',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
    });
});

// Fonction pour afficher une confirmation SweetAlert avant une action
function confirmerAction(action, titre, message, redirectUrl) {
    Swal.fire({
        title: titre,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'terminer' ? '#28a745' :
            (action === 'démarrer' ? '#007bff' : '#dc3545'),
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, ' + action,
        cancelButtonText: 'Annuler',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = redirectUrl;
        }
    });
}