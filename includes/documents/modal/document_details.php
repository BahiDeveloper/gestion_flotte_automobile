<!-- Modal pour afficher les détails du document -->
<div class="modal fade" id="documentDetailsModal" tabindex="-1" aria-labelledby="documentDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentDetailsModalLabel">Détails du Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
            <div class="modal-footer">
                <!-- Les boutons d'action seront ajoutés dynamiquement -->
            </div>
        </div>
    </div>
</div>

<script>
function viewDocumentDetails(documentId) {
    console.log("Fonction appelée avec l'ID:", documentId);
    
    // Afficher un indicateur de chargement
    $('#documentDetailsModal .modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-3">Chargement des détails du document...</p></div>');

    // Afficher la modal
    $('#documentDetailsModal').modal('show');

    // Charger les détails du document via AJAX
    $.ajax({
        url: 'actions/documents/get_document_details.php',
        type: 'GET',
        data: { id: documentId },
        dataType: 'json',
        success: function (response) {
            console.log("Réponse AJAX reçue:", response);
            if (response.success) {
                // Mettre à jour le titre de la modal
                $('#documentDetailsModalLabel').text('Document : ' + response.document.type_document);

                // Remplir le corps de la modal avec le HTML retourné
                $('#documentDetailsModal .modal-body').html(response.html);

                // Ajouter les boutons d'action dans le footer de la modal
                var footerButtons = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button> ';
                footerButtons += '<a href="actions/documents/download_document.php?id=' + documentId + '" class="btn btn-primary"><i class="fas fa-download"></i> Télécharger</a>';

                // Boutons supplémentaires selon les droits
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'administrateur' || $_SESSION['role'] === 'gestionnaire')): ?>
                    <?php if ($doc['frequence_renouvellement'] != 0): // Afficher le bouton uniquement si non permanent ?>
                footerButtons += ' <a href="actions/documents/renew_document.php?id=' + documentId + '" class="btn btn-warning"><i class="fas fa-sync-alt"></i> Renouveler</a>';
                    <?php endif; ?>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrateur'): ?>
                footerButtons += ' <a href="javascript:confirmDelete(' + documentId + ')" class="btn btn-danger"><i class="fas fa-trash"></i> Supprimer</a>';
                <?php endif; ?>
                <?php endif; ?>

                $('#documentDetailsModal .modal-footer').html(footerButtons);
            } else {
                // Afficher un message d'erreur
                $('#documentDetailsModal .modal-body').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' + response.message + '</div>');
            }
        },
        error: function (xhr, status, error) {
            // Afficher le contenu brut de la réponse pour le débogage
            console.error("Erreur AJAX:", error);
            console.log("Réponse brute:", xhr.responseText);
            
            $('#documentDetailsModal .modal-body').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Erreur lors du chargement des détails.</div><div class="mt-3 p-3 bg-light"><small><pre>' + xhr.responseText.substring(0, 500) + '...</pre></small></div>');
        }
    });
}
    // Fonction pour confirmer la suppression d'un document
    function confirmDelete(documentId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.')) {
            window.location.href = 'actions/documents/delete_document.php?id=' + documentId;
        }
    }
</script>