/**
 * Script de gestion des documents administratifs
 * 
 * Fonctionnalités:
 * - Gestion du formulaire d'ajout de document
 * - Gestion de la suppression de documents
 * - Calcul automatique des dates d'expiration
 * - Gestion des filtres et exports
 */

document.addEventListener('DOMContentLoaded', function () {
    // Gestion du formulaire d'ajout de document
    const concerneSelect = document.getElementById('concerne');
    const vehiculeSelect = document.getElementById('select_vehicule');
    const chauffeurSelect = document.getElementById('select_chauffeur');
    const utilisateurSelect = document.getElementById('select_utilisateur');

    if (concerneSelect) {
        concerneSelect.addEventListener('change', function () {
            // Cacher tous les sélecteurs
            vehiculeSelect.classList.add('d-none');
            chauffeurSelect.classList.add('d-none');
            utilisateurSelect.classList.add('d-none');

            // Désactiver tous les champs de sélection
            document.getElementById('id_vehicule').removeAttribute('required');
            document.getElementById('id_chauffeur').removeAttribute('required');
            document.getElementById('id_utilisateur').removeAttribute('required');

            // Afficher le sélecteur approprié
            if (this.value === 'vehicule') {
                vehiculeSelect.classList.remove('d-none');
                document.getElementById('id_vehicule').setAttribute('required', 'required');
            } else if (this.value === 'chauffeur') {
                chauffeurSelect.classList.remove('d-none');
                document.getElementById('id_chauffeur').setAttribute('required', 'required');
            } else if (this.value === 'utilisateur') {
                utilisateurSelect.classList.remove('d-none');
                document.getElementById('id_utilisateur').setAttribute('required', 'required');
            }
        });
    }

    // Calcul automatique de la date d'expiration basée sur la fréquence
    const dateEmissionInput = document.getElementById('date_emission');
    const dateExpirationInput = document.getElementById('date_expiration');
    const frequenceInput = document.getElementById('frequence_renouvellement');

    if (dateEmissionInput && dateExpirationInput && frequenceInput) {
        // Mise à jour de la date d'expiration lors du changement de la fréquence
        frequenceInput.addEventListener('change', updateExpirationDate);
        dateEmissionInput.addEventListener('change', updateExpirationDate);

        function updateExpirationDate() {
            const dateEmission = dateEmissionInput.value;
            const frequence = parseInt(frequenceInput.value);

            if (dateEmission && !isNaN(frequence) && frequence > 0) {
                const emissionDate = new Date(dateEmission);
                const expirationDate = new Date(emissionDate);
                expirationDate.setDate(emissionDate.getDate() + frequence);

                // Format YYYY-MM-DD pour input date
                const year = expirationDate.getFullYear();
                const month = String(expirationDate.getMonth() + 1).padStart(2, '0');
                const day = String(expirationDate.getDate()).padStart(2, '0');
                dateExpirationInput.value = `${year}-${month}-${day}`;
            }
        }
    }

    // Préréglages de durée pour les types de documents courants
    const typeDocumentSelect = document.getElementById('type_document');
    if (typeDocumentSelect && frequenceInput) {
        typeDocumentSelect.addEventListener('change', function () {
            // Définir des durées par défaut selon le type de document
            switch (this.value) {
                case 'assurance':
                    // Assurance généralement annuelle
                    frequenceInput.value = 365;
                    break;
                case 'visite_technique':
                    // Visite technique généralement semestrielle
                    frequenceInput.value = 180;
                    break;
                case 'carte_stationnement':
                    // Carte de stationnement généralemnet annuelle
                    frequenceInput.value = 365;
                    break;
                case 'carte_transport':
                    // Carte de transport généralement biennale
                    frequenceInput.value = 730;
                    break;
                case 'carte_grise':
                    // Carte grise généralement permanente
                    frequenceInput.value = 0;
                    break;
                default:
                    // Pas de durée par défaut
                    break;
            }

            // Mettre à jour la date d'expiration si une date d'émission est déjà renseignée
            if (dateEmissionInput && dateEmissionInput.value) {
                updateExpirationDate();
            }
        });
    }

    // Fonction pour confirmer la suppression d'un document
    window.confirmDeleteDocument = function (id, type) {
        Swal.fire({
            title: 'Confirmation de suppression',
            html: `Êtes-vous sûr de vouloir supprimer le document <strong>${type}</strong> ?<br><br>
                  <span class="text-danger fw-bold">Cette action est irréversible !</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Supprimer',
            cancelButtonText: 'Annuler',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Rediriger vers la page de suppression
                window.location.href = `actions/documents/supprimer_document.php?id=${id}`;
            }
        });
    };

    // Validation du formulaire d'ajout de document
    const addDocumentForm = document.getElementById('addDocumentForm');
    if (addDocumentForm) {
        addDocumentForm.addEventListener('submit', function (event) {
            let isValid = true;

            // Vérification de la date d'émission
            if (dateEmissionInput && dateEmissionInput.value) {
                const emissionDate = new Date(dateEmissionInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Réinitialiser l'heure pour comparer seulement les dates

                if (emissionDate > today) {
                    isValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Date invalide',
                        text: 'La date d\'émission ne peut pas être dans le futur.'
                    });
                }
            }

            // Vérification des dates d'émission et d'expiration
            if (dateEmissionInput && dateEmissionInput.value && dateExpirationInput && dateExpirationInput.value) {
                const emissionDate = new Date(dateEmissionInput.value);
                const expirationDate = new Date(dateExpirationInput.value);

                if (expirationDate <= emissionDate) {
                    isValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Date invalide',
                        text: 'La date d\'expiration doit être postérieure à la date d\'émission.'
                    });
                }
            }

            // Vérification de l'élément concerné
            const concerne = concerneSelect.value;
            if (concerne === 'vehicule' && (!document.getElementById('id_vehicule').value)) {
                isValid = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Sélection manquante',
                    text: 'Veuillez sélectionner un véhicule.'
                });
            } else if (concerne === 'chauffeur' && (!document.getElementById('id_chauffeur').value)) {
                isValid = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Sélection manquante',
                    text: 'Veuillez sélectionner un chauffeur.'
                });
            } else if (concerne === 'utilisateur' && (!document.getElementById('id_utilisateur').value)) {
                isValid = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Sélection manquante',
                    text: 'Veuillez sélectionner un utilisateur.'
                });
            }

            // Vérification du fichier
            const fichierInput = document.getElementById('fichier');
            if (fichierInput && fichierInput.files.length > 0) {
                const fichier = fichierInput.files[0];
                const maxSize = 5 * 1024 * 1024; // 5 MB
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

                if (fichier.size > maxSize) {
                    isValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Fichier trop volumineux',
                        text: 'La taille du fichier ne doit pas dépasser 5 MB.'
                    });
                } else if (!allowedTypes.includes(fichier.type)) {
                    isValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Type de fichier non supporté',
                        text: 'Le fichier doit être au format PDF, JPG ou PNG.'
                    });
                }
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});