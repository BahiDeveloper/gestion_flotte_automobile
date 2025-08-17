document.addEventListener('DOMContentLoaded', function () {
    // Prix des carburants en FCFA/L (février 2025)
    const PRIX_CARBURANTS = {
        'essence': 875, // Super sans plomb
        'diesel': 715,  // Gasoil
        'hybride': 875  // Assimilé au Super
    };

    // Éléments du formulaire
    const typeCarburantSelect = document.getElementById('type_carburant');
    const quantiteInput = document.getElementById('quantite_litres');
    const prixUnitaireInput = document.getElementById('prix_unitaire');
    const prixTotalInput = document.getElementById('prix_total');
    const coutInput = document.getElementById('cout_total');
    const calculModeSelect = document.getElementById('calcul_mode');

    // Définir le prix unitaire initial et rendre le champ readonly
    if (typeCarburantSelect && prixUnitaireInput) {
        // Obtenir le type de carburant du véhicule
        const typeCarburant = typeCarburantSelect.value;
        const prixCarburant = PRIX_CARBURANTS[typeCarburant] || '';

        // Définir le prix unitaire et le rendre non modifiable
        prixUnitaireInput.value = prixCarburant;
        prixUnitaireInput.readOnly = true;

        // Désactiver le select du type de carburant
        typeCarburantSelect.disabled = true;
    }

    // Fonction pour formater les nombres avec séparateurs de milliers
    function formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    }

    // Fonction pour calculer le prix total ou la quantité selon le mode
    function calculerTotal() {
        if (!quantiteInput || !prixUnitaireInput) return;

        const mode = calculModeSelect ? calculModeSelect.value : 'quantite-prix';
        const prixUnitaire = parseFloat(prixUnitaireInput.value) || 0;

        if (mode === 'cout-quantite') {
            // Mode: Saisir coût -> calculer quantité
            let cout = parseFloat(coutInput.value) || 0;

            // Arrondir le coût à l'entier
            cout = Math.round(cout);
            coutInput.value = cout;

            if (prixUnitaire > 0) {
                const quantite = cout / prixUnitaire;
                quantiteInput.value = quantite.toFixed(2);

                // Afficher le prix total formaté (même valeur que coût total)
                prixTotalInput.value = formatNumber(cout) + ' FCFA';
            }
        } else {
            // Mode par défaut: Saisir quantité -> calculer prix
            const quantite = parseFloat(quantiteInput.value) || 0;

            // Calculer le prix total et l'arrondir à l'entier
            const prixTotal = Math.round(quantite * prixUnitaire);

            // Afficher le prix total formaté
            prixTotalInput.value = formatNumber(prixTotal) + ' FCFA';

            if (coutInput) {
                coutInput.value = prixTotal;
            }
        }
    }

    // Ajouter les écouteurs d'événements selon le mode
    if (calculModeSelect) {
        calculModeSelect.addEventListener('change', function () {
            const quantiteContainer = document.getElementById('quantite-container');
            const coutContainer = document.getElementById('cout-container');

            if (this.value === 'cout-quantite') {
                quantiteContainer.classList.add('calculated-field');
                coutContainer.classList.remove('calculated-field');
                quantiteInput.readOnly = true;
                coutInput.readOnly = false;
            } else {
                quantiteContainer.classList.remove('calculated-field');
                coutContainer.classList.add('calculated-field');
                quantiteInput.readOnly = false;
                coutInput.readOnly = true;
            }

            calculerTotal();
        });

        // Déclencher l'événement change pour initialiser l'interface
        const event = new Event('change');
        calculModeSelect.dispatchEvent(event);
    }

    // Ajouter les écouteurs pour la mise à jour dynamique
    if (quantiteInput) {
        quantiteInput.addEventListener('input', calculerTotal);
    }

    if (coutInput) {
        coutInput.addEventListener('input', function () {
            // S'assurer que la valeur est un entier
            if (this.value) {
                this.value = Math.round(parseFloat(this.value));
            }
            calculerTotal();
        });
    }

    // Vérification du kilométrage
    const kilometrageInput = document.getElementById('kilometrage');
    if (kilometrageInput) {
        const minKilometrage = parseInt(kilometrageInput.getAttribute('min')) || 0;

        kilometrageInput.addEventListener('input', function () {
            const valeur = parseInt(this.value) || 0;
            if (valeur < minKilometrage) {
                this.setCustomValidity(`Le kilométrage doit être supérieur ou égal à ${minKilometrage}`);
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Gestion des détails dans le modal
    const viewButtons = document.querySelectorAll('.view-details');
    viewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const detailsContent = document.getElementById('detailsContent');

            // Afficher un indicateur de chargement
            detailsContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Chargement des détails...</p></div>';

            // Ouvrir le modal pendant le chargement
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();

            // Effectuer la requête AJAX vers get_approvisionnement_details.php
            fetch(`request/vehicules/ajax/get_approvisionnement_details.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const appro = data.data;
                        // Remplir le modal avec les données
                        let html = `
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th class="bg-light">Date</th>
                                    <td>${appro.date_formattee}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Véhicule</th>
                                    <td>${appro.vehicule_infos}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Chauffeur</th>
                                    <td>${appro.nom_complet_chauffeur}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Quantité</th>
                                    <td>${appro.quantite_litres_formattee} L</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Prix unitaire</th>
                                    <td>${appro.prix_unitaire_formatte} FCFA/L</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Prix total</th>
                                    <td>${appro.prix_total_formatte} FCFA</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Kilométrage</th>
                                    <td>${appro.kilometrage_formatte} km</td>
                                </tr>`;

                        // Ajouter les informations de consommation si disponibles
                        if (appro.distance_parcourue) {
                            html += `
                                <tr>
                                    <th class="bg-light">Distance parcourue</th>
                                    <td>${appro.distance_parcourue_formattee} km</td>
                                </tr>`;
                        }

                        if (appro.consommation) {
                            html += `
                                <tr>
                                    <th class="bg-light">Consommation</th>
                                    <td>${appro.consommation_formattee} L/100km</td>
                                </tr>`;
                        }

                        if (appro.cout_km) {
                            html += `
                                <tr>
                                    <th class="bg-light">Coût au km</th>
                                    <td>${appro.cout_km_formatte} FCFA/km</td>
                                </tr>`;
                        }

                        html += `
                                <tr>
                                    <th class="bg-light">Station-service</th>
                                    <td>${appro.station_service || 'Non spécifiée'}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Type de carburant</th>
                                    <td>${appro.type_carburant}</td>
                                </tr>
                            </table>
                        </div>`;

                        detailsContent.innerHTML = html;
                    } else {
                        // Afficher un message d'erreur
                        detailsContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.error || 'Erreur lors du chargement des détails'}
                        </div>`;
                    }
                })
                .catch(error => {
                    // Gérer les erreurs
                    detailsContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erreur de communication avec le serveur: ${error.message}
                    </div>`;
                });
        });
    });

    // Initialisation DataTable pour l'historique
    const historyTable = document.getElementById('historyTable');
    if (historyTable && typeof DataTable !== 'undefined') {
        new DataTable('#historyTable', {
            language: {
                url: 'assets/js/dataTables.french.json'
            },
            order: [[0, 'desc']],
            pageLength: 5,
            lengthMenu: [5, 10, 25]
        });
    }
});