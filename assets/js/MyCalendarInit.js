document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    console.log("Initialisation du calendrier"); // Debug

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        height: 'auto', // Ajout important
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            console.log("Chargement des événements", fetchInfo); // Debug

            const statuts = getSelectedStatuts();
            const chauffeur = document.getElementById('driverFilter')?.value || '';

            fetch(`api/charger-evenements-calendrier.php?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}&statuts=${statuts}&chauffeur=${chauffeur}`)
                .then(response => {
                    console.log("Réponse brute:", response); // Debug
                    return response.json();
                })
                .then(data => {
                    console.log("Données reçues:", data); // Debug
                    if (data.error) {
                        console.error("Erreur API:", data.message);
                        failureCallback(new Error(data.message));
                    } else {
                        successCallback(data);
                    }
                })
                .catch(error => {
                    console.error('Erreur de chargement des événements:', error);
                    failureCallback(error);
                });
        }
    });

    calendar.render();
    console.log("Calendrier rendu"); // Debug

    function getSelectedStatuts() {
        return Array.from(document.querySelectorAll('.filter-statut:checked'))
            .map(checkbox => checkbox.value)
            .join(',');
    }
});