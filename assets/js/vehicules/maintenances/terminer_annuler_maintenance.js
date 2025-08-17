// Gérer les clics sur les boutons de terminer maintenance
$(document).ready(function() {
    // Gestion du bouton "Terminer" dans la liste de toutes les maintenances
    $('.btn-terminer-maintenance').click(function() {
        var maintenanceId = $(this).data('id');
        
        // Récupérer l'ID du véhicule associé à cette maintenance via une requête AJAX
        $.ajax({
            url: 'request/vehicules/maintenances/get_maintenance_info.php',
            type: 'GET',
            data: {maintenance_id: maintenanceId},
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Remplir le modal avec les informations
                    $('#maintenance_id_modal').val(maintenanceId);
                    $('#vehicule_id_modal').val(response.vehicule_id);
                    
                    // Afficher le modal
                    $('#terminerMaintenanceModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de récupérer les informations de la maintenance'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors de la récupération des informations'
                });
            }
        });
    });
    
    // Gestion du bouton "Annuler" dans la liste de toutes les maintenances
    $('.btn-annuler-maintenance').click(function() {
        var maintenanceId = $(this).data('id');
        
        Swal.fire({
            title: 'Confirmer l\'annulation',
            text: 'Êtes-vous sûr de vouloir annuler cette maintenance ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, annuler',
            cancelButtonText: 'Non, revenir'
        }).then((result) => {
            if (result.isConfirmed) {
                // Rediriger vers l'action d'annulation
                window.location.href = 'actions/vehicules/maintenance_vehicule.php?action=annuler&maintenance_id=' + maintenanceId + '&id=' + $(this).data('vehicule');
            }
        });
    });
    
    // Gestion de la soumission du formulaire de fin de maintenance
    $('#terminerMaintenanceFormModal').submit(function() {
        // Validation des champs obligatoires
        if(!$('#cout_final_modal').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Veuillez indiquer le coût final de la maintenance'
            });
            return false;
        }
        return true;
    });
});