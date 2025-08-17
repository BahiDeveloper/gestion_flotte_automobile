<!-- Modale de confirmation pour créer une nouvelle zone -->
<div class="modal fade" id="createZoneModal" tabindex="-1" aria-labelledby="createZoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createZoneModalLabel">Créer une nouvelle zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous créer la zone <strong><span id="newZoneName"></span></strong> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="confirmCreateZone">Créer</button>
            </div>
        </div>
    </div>
</div>