/**
 * Script de gestion des messages d'alerte
 * Ce script vérifie les paramètres d'URL et affiche les messages d'alerte correspondants
 */

document.addEventListener('DOMContentLoaded', function () {

    // Récupérer les paramètres d'URL
    const urlParams = new URLSearchParams(window.location.search);

    // Vérifier s'il y a un message de succès
    if (urlParams.has('success')) {
        const successType = urlParams.get('success');
        let title = 'Succès !';
        let message = 'Opération réussie.';

        switch (successType) {
            case 'add':
                message = 'Le chauffeur a été ajouté avec succès.';
                break;
            case 'edit':
                message = 'Les informations du chauffeur ont été mises à jour avec succès.';
                break;
            case 'delete':
                message = 'Le chauffeur a été supprimé avec succès.';
                break;
            case 'doc_add':
                message = 'Le document a été ajouté avec succès.';
                break;
            case 'doc_edit':
                message = 'Le document a été mis à jour avec succès.';
                break;
            case 'doc_delete':
                message = 'Le document a été supprimé avec succès.';
                break;
        }

        Swal.fire({
            title: title,
            text: message,
            icon: 'success',
            confirmButtonText: 'OK'
        });
    }

    // Vérifier s'il y a un message d'erreur
    if (urlParams.has('error')) {
        const errorType = urlParams.get('error');
        let title = 'Erreur !';
        let message = 'Une erreur est survenue.';

        switch (errorType) {
            case 'add':
                message = 'Une erreur est survenue lors de l\'ajout du chauffeur.';
                break;
            case 'edit':
                message = 'Une erreur est survenue lors de la modification du chauffeur.';
                break;
            case 'delete':
                message = 'Une erreur est survenue lors de la suppression du chauffeur.';
                break;
            case 'invalid_id':
                message = 'Identifiant invalide. Veuillez réessayer.';
                break;
            case 'not_found':
                message = 'L\'élément demandé n\'a pas été trouvé.';
                break;
            case 'doc_add':
                message = 'Une erreur est survenue lors de l\'ajout du document.';
                break;
            case 'doc_edit':
                message = 'Une erreur est survenue lors de la modification du document.';
                break;
            case 'doc_delete':
                message = 'Une erreur est survenue lors de la suppression du document.';
                break;
        }

        Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
});