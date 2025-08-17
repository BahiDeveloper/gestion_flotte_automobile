// Fonction pour récupérer les suggestions d'adresses
async function fetchAddressSuggestions(query, apiKey) {
    const url = `https://api.openrouteservice.org/geocode/autocomplete?api_key=${apiKey}&text=${encodeURIComponent(query)}&size=5&boundary.country=CI`;
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error('Erreur lors de la récupération des suggestions.');
    }
    const data = await response.json();
    return data.features.map(feature => feature.properties.label);
}

// Affiche les suggestions sous le champ de saisie
function displaySuggestions(suggestions, container, inputField) {
    container.innerHTML = '';
    if (suggestions.length === 0) {
        container.style.display = 'none';
        return;
    }

    suggestions.forEach(address => {
        const listItem = document.createElement('li');
        listItem.className = 'list-group-item list-group-item-action';
        listItem.textContent = address;
        listItem.addEventListener('click', () => {
            inputField.value = address;
            container.style.display = 'none';
            calculateDistanceAndDurationAutomatically();
        });
        container.appendChild(listItem);
    });
    container.style.display = 'block';
}

// Événement pour afficher les suggestions lors de la saisie
document.getElementById('assignmentRoute1').addEventListener('input', function () {
    handleInputSuggestions(this, document.getElementById('suggestionsStart'));
});
document.getElementById('assignmentRoute2').addEventListener('input', function () {
    handleInputSuggestions(this, document.getElementById('suggestionsEnd'));
});

// Gérer les suggestions d'adresses
async function handleInputSuggestions(inputField, suggestionContainer) {
    const query = inputField.value.trim();
    const apiKey = '5b3ce3597851110001cf624881ff493efc1847cd8f1d7c395e0e513f';

    if (query.length >= 3) { // Lancer la recherche après 3 caractères
        try {
            const suggestions = await fetchAddressSuggestions(query, apiKey);
            displaySuggestions(suggestions, suggestionContainer, inputField);
        } catch (error) {
            console.error('Erreur lors de la récupération des suggestions :', error);
            suggestionContainer.style.display = 'none';
        }
    } else {
        suggestionContainer.style.display = 'none';
    }
}

// Cacher les suggestions si on clique en dehors
document.addEventListener('click', (event) => {
    if (!event.target.closest('.input-group')) {
        document.getElementById('suggestionsStart').style.display = 'none';
        document.getElementById('suggestionsEnd').style.display = 'none';
    }
});


// Fonction pour géocoder une adresse
async function geocodeAddress(address, apiKey) {
    const geocodeUrl = `https://api.openrouteservice.org/geocode/search?api_key=${apiKey}&text=${encodeURIComponent(address)}&boundary.country=CI`;
    const response = await fetch(geocodeUrl);
    if (!response.ok) {
        throw new Error(`Erreur de géocodage pour l'adresse : ${address}`);
    }
    const data = await response.json();
    if (data.features && data.features.length > 0) {
        return data.features[0].geometry.coordinates; // [longitude, latitude]
    } else {
        throw new Error(`Aucune coordonnée trouvée pour l'adresse : ${address}`);
    }
}

// Fonction pour formater la durée en "XhYmin"
function formatDuration(minutes) {
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = Math.round(minutes % 60);

    if (hours > 0) {
        return `${hours}h${remainingMinutes}min`;
    } else {
        return `${remainingMinutes}min`;
    }
}

// Écouteurs d’événements
document.getElementById('assignmentRoute1').addEventListener('input', calculateDistanceAndDurationAutomatically);
document.getElementById('assignmentRoute2').addEventListener('input', calculateDistanceAndDurationAutomatically);

async function calculateDistanceAndDurationAutomatically() {
    const startAddress = document.getElementById('assignmentRoute1').value.trim();
    const endAddress = document.getElementById('assignmentRoute2').value.trim();
    const apiKey = '5b3ce3597851110001cf624881ff493efc1847cd8f1d7c395e0e513f';

    if (startAddress && endAddress) {
        try {
            // Géocodage des adresses
            const [startLng, startLat] = await geocodeAddress(startAddress, apiKey);
            const [endLng, endLat] = await geocodeAddress(endAddress, apiKey);

            // Calcul de la distance et du temps estimé
            const response = await fetch(`https://api.openrouteservice.org/v2/directions/driving-car?api_key=${apiKey}&start=${startLng},${startLat}&end=${endLng},${endLat}`);

            if (!response.ok) {
                throw new Error('Erreur de réponse de l’API');
            }

            const data = await response.json();

            if (data.features && data.features.length > 0) {
                const segment = data.features[0].properties.segments[0];
                const distance = (segment.distance / 1000).toFixed(2); // Distance en km
                const durationInMinutes = segment.duration / 60; // Durée en minutes
                const formattedDuration = formatDuration(durationInMinutes);

                document.getElementById('assignmentDistance').value = distance;
                document.getElementById('assignmentDuration').value = formattedDuration;
            } else {
                document.getElementById('assignmentDistance').value = '';
                document.getElementById('assignmentDuration').value = '';
                console.warn('Aucune donnée de distance ou de durée trouvée.');
            }
        } catch (error) {
            console.error('Erreur lors du calcul de la distance et de la durée:', error);
            document.getElementById('assignmentDistance').value = '';
            document.getElementById('assignmentDuration').value = '';
        }
    } else {
        document.getElementById('assignmentDistance').value = '';
        document.getElementById('assignmentDuration').value = '';
    }
}
