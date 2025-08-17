document.addEventListener('DOMContentLoaded', function () {
    // Initialisation des DataTables
    if (document.getElementById('maintenancesEnCours')) {
        $('#maintenancesEnCours').DataTable({
            language: {
                url: 'assets/js/datatables-fr.json'
            },
            responsive: true,
            order: [[3, 'desc']] // Trier par date de début par défaut
        });
    }

    if (document.getElementById('maintenancesTermineesTable')) {
        $('#maintenancesTermineesTable').DataTable({
            language: {
                url: 'assets/js/datatables-fr.json'
            },
            responsive: true,
            order: [[3, 'desc']] // Trier par date de début par défaut
        });
    }

    if (document.getElementById('maintenancesAnnuleesTable')) {
        $('#maintenancesAnnuleesTable').DataTable({
            language: {
                url: 'assets/js/datatables-fr.json'
            },
            responsive: true,
            order: [[3, 'desc']] // Trier par date prévue par défaut
        });
    }

// Validation du formulaire d'ajout
const addForm = document.getElementById('addMaintenanceForm');
if (addForm) {
    addForm.addEventListener('submit', function (event) {
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normaliser la date à minuit
        
        const dateDebut = new Date(document.getElementById('date_debut').value);
        const dateFinPrevue = new Date(document.getElementById('date_fin_prevue').value);
        
        // Vérifier que la date de début n'est pas dans le passé
        if (dateDebut < today) {
            event.preventDefault();
            Swal.fire({
                title: 'Date invalide',
                text: 'La date de début ne peut pas être dans le passé.',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // Vérifier que la date de fin n'est pas antérieure à la date de début
        if (dateFinPrevue < dateDebut) {
            event.preventDefault();
            Swal.fire({
                title: 'Date invalide',
                text: 'La date de fin prévue ne peut pas être antérieure à la date de début.',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
    });
}

    // Initialisation des dates par défaut
    const dateDebutInput = document.getElementById('date_debut');
    const dateFinPrevueInput = document.getElementById('date_fin_prevue');

    if (dateDebutInput && !dateDebutInput.value) {
        const today = new Date();
        dateDebutInput.value = today.toISOString().split('T')[0];
    }

    if (dateFinPrevueInput && !dateFinPrevueInput.value) {
        const defaultEndDate = new Date();
        defaultEndDate.setDate(defaultEndDate.getDate() + 7); // Par défaut une semaine plus tard
        dateFinPrevueInput.value = defaultEndDate.toISOString().split('T')[0];
    }

    // Gestion du modal de saisie du coût final
    const coutFinalModal = document.getElementById('coutFinalModal');
    if (coutFinalModal) {
        const coutFinalForm = document.getElementById('coutFinalForm');

        // Préremplir les champs du formulaire lorsque le modal est ouvert
        coutFinalModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const maintenanceId = button.getAttribute('data-maintenance-id');
            const vehiculeId = button.getAttribute('data-vehicule-id');
            const prestataire = button.getAttribute('data-prestataire') || '';
            const cout = button.getAttribute('data-cout') || '';

            document.getElementById('maintenance_id').value = maintenanceId;
            document.getElementById('prestataire_final').value = prestataire;
            document.getElementById('cout_final').value = cout;

            // Définir l'action du formulaire
            coutFinalForm.action = `actions/vehicules/maintenance_vehicule.php?id=${vehiculeId}`;
        });

        // Validation du formulaire
        coutFinalForm.addEventListener('submit', function (event) {
            const coutFinal = document.getElementById('cout_final').value;
            if (!coutFinal || isNaN(parseFloat(coutFinal)) || parseFloat(coutFinal) < 0) {
                event.preventDefault();
                Swal.fire({
                    title: 'Erreur',
                    text: 'Veuillez saisir un coût valide pour terminer la maintenance.',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    }
});

// Fonction pour afficher la confirmation modale
function confirmerAction(message, actionUrl) {
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    document.getElementById('confirmationMessage').textContent = message;
    document.getElementById('confirmActionBtn').href = actionUrl;
    modal.show();
}

// Fonction pour ouvrir le modal de saisie du coût final
function terminerMaintenanceAvecCout(maintenanceId, vehiculeId) {
    const modal = new bootstrap.Modal(document.getElementById('coutFinalModal'));
    const button = document.createElement('button');
    button.setAttribute('data-maintenance-id', maintenanceId);
    button.setAttribute('data-vehicule-id', vehiculeId);

    // Déclencher l'ouverture du modal avec les données
    modal._element.addEventListener('show.bs.modal', (event) => {
        event.relatedTarget = button;

        // S'assurer que le maintenanceId est bien défini
        document.getElementById('maintenance_id').value = maintenanceId;

        // Définir l'action du formulaire avec l'ID du véhicule
        const coutFinalForm = document.getElementById('coutFinalForm');
        coutFinalForm.action = `actions/vehicules/maintenance_vehicule.php?id=${vehiculeId}`;

    }, { once: true });

    modal.show();
}