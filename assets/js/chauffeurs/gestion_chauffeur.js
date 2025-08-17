/**
 * Script de gestion des chauffeurs
 * Gère les tableaux de données, les validations de formulaire et les confirmations
 */

document.addEventListener("DOMContentLoaded", function () {
    // Initialisation des DataTables
    initializeDataTables();

    // Configuration des contrôles de date
    setupDateValidation();

    // Configuration de la gestion des catégories de permis
    setupPermisCategories();

    // Validation du formulaire avant soumission
    setupFormValidation();
});

/**
 * Initialise les tableaux DataTables et les boutons d'export
 */
function initializeDataTables() {
    // Table des chauffeurs
    const chauffeurTable = $('#chauffeursTable').DataTable({
        language: {
            url: 'assets/js/datatables-fr.json'
        },
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [1, 2, 3, 4]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger',
                exportOptions: {
                    columns: [1, 2, 3, 4]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimer',
                className: 'btn btn-sm btn-info',
                exportOptions: {
                    columns: [1, 2, 3, 4]
                }
            }
        ]
    });

    // Table de l'historique
    const historiqueTable = $('#historiqueTable').DataTable({
        language: {
            url: 'assets/js/datatables-fr.json'
        },
        responsive: true,
        order: [[2, 'desc']], // Trier par date de départ descendante
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimer',
                className: 'btn btn-sm btn-info'
            }
        ]
    });

    // Gestion des boutons d'export
    $('#btnExportChauffeurs').on('click', function () {
        chauffeurTable.button('.buttons-excel').trigger();
    });

    $('#btnPrintChauffeurs').on('click', function () {
        chauffeurTable.button('.buttons-print').trigger();
    });

    $('#btnExportHistorique').on('click', function () {
        historiqueTable.button('.buttons-excel').trigger();
    });

    $('#btnPrintHistorique').on('click', function () {
        historiqueTable.button('.buttons-print').trigger();
    });
}

/**
 * Configure la validation des dates
 */
function setupDateValidation() {
    const dateNaissanceInput = document.getElementById('date_naissance');
    const dateEmbauchInput = document.getElementById('date_embauche');
    const dateDelivranceInput = document.getElementById('date_delivrance_permis');
    const dateExpirationInput = document.getElementById('date_expiration_permis');

    // Aujourd'hui au format YYYY-MM-DD
    const today = new Date().toISOString().split('T')[0];

    // Ajouter contrainte d'âge minimum
    if (dateNaissanceInput) {
        dateNaissanceInput.addEventListener('change', function () {
            const today = new Date();
            const birthDate = new Date(this.value);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age < 18) {
                this.setCustomValidity('Le chauffeur doit avoir au moins 18 ans');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Limiter les dates dans le passé
    if (dateEmbauchInput) dateEmbauchInput.setAttribute('max', today);
    if (dateDelivranceInput) dateDelivranceInput.setAttribute('max', today);
    if (dateExpirationInput) dateExpirationInput.setAttribute('min', today);
}

/**
 * Configure la gestion des catégories de permis
 */
function setupPermisCategories() {
    const categoriePermisSelect = document.getElementById('categories_permis');
    const dateExpirationField = document.getElementById('date_expiration_field');
    const dateExpirationInput = document.getElementById('date_expiration_permis');

    if (categoriePermisSelect) {
        // Vérifier l'état initial au chargement de la page
        updateExpirationField();

        // Ajouter l'écouteur d'événement pour les changements
        categoriePermisSelect.addEventListener('change', updateExpirationField);

        function updateExpirationField() {
            // Convertir la liste de sélection en tableau
            const selectedCategories = Array.from(categoriePermisSelect.selectedOptions).map(option => option.value);

            // Vérifier si seule la catégorie A est sélectionnée
            const onlyA = selectedCategories.length === 1 && selectedCategories[0] === 'A';

            // Vérifier si la catégorie A fait partie des sélections
            const hasA = selectedCategories.includes('A');

            if (onlyA) {
                // Si uniquement A est sélectionné, masquer le champ d'expiration
                dateExpirationField.style.display = 'none';
                dateExpirationInput.removeAttribute('required');
                dateExpirationInput.value = '';
            } else {
                // Sinon, afficher le champ d'expiration
                dateExpirationField.style.display = 'block';
                dateExpirationInput.setAttribute('required', 'required');

                // Ajouter une note si A est sélectionné avec d'autres catégories
                const formTextElement = document.querySelector('#date_expiration_field .form-text');
                if (formTextElement) {
                    if (hasA) {
                        formTextElement.textContent = "Date d'expiration pour les catégories autres que A. La catégorie A reste permanente.";
                    } else {
                        formTextElement.textContent = "Date d'expiration du permis";
                    }
                }
            }
        }
    }
}

/**
 * Configure la validation du formulaire avant soumission
 */
function setupFormValidation() {
    const addChauffeurForm = document.getElementById('addChauffeurForm');
    if (addChauffeurForm) {
        addChauffeurForm.addEventListener('submit', function (event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Afficher message avec SweetAlert
                Swal.fire({
                    title: 'Erreur de validation',
                    text: 'Veuillez corriger les erreurs dans le formulaire',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }

            this.classList.add('was-validated');
        });
    }
}

/**
 * Fonction de confirmation de suppression avec SweetAlert
 * @param {number} id - ID du chauffeur à supprimer
 * @param {string} nom - Nom complet du chauffeur
 */
function confirmDeleteChauffeur(id, nom) {
    Swal.fire({
        title: 'Êtes-vous sûr?',
        html: `Voulez-vous vraiment supprimer le chauffeur <strong>${nom}</strong>?<br>Cette action est irréversible.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `actions/chauffeurs/supprimer_chauffeur.php?id=${id}`;
        }
    });
}