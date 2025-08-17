// Fonction à ajouter à tous vos documents JS qui contiennent des champs téléphone
function formatPhoneNumber(inputField) {
    // Ajouter ces événements à tous vos champs téléphone
    inputField.addEventListener('input', function (e) {
        // Supprimer tous les caractères non numériques
        let input = this.value.replace(/\D/g, '');

        // Limiter à 10 chiffres
        if (input.length > 10) {
            input = input.substring(0, 10);
        }

        // Formater avec des espaces (XX XX XX XX XX)
        let formattedInput = '';
        for (let i = 0; i < input.length; i++) {
            if (i > 0 && i % 2 === 0 && i < 10) {
                formattedInput += ' ';
            }
            formattedInput += input[i];
        }

        // Mettre à jour la valeur du champ
        this.value = formattedInput;
    });

    // Valider le format lors de la perte de focus
    inputField.addEventListener('blur', function () {
        // Vérifier si le nombre de chiffres est exactement 10
        const digitsOnly = this.value.replace(/\D/g, '');
        if (digitsOnly.length !== 10) {
            this.setCustomValidity('Le numéro de téléphone doit contenir exactement 10 chiffres.');
        } else {
            this.setCustomValidity('');
        }
    });
}

// Appliquer à tous les champs téléphone lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function () {
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="telephone"]');
    phoneInputs.forEach(input => formatPhoneNumber(input));
});