// FullCalendar.js

// Ajouter la modal au début du chargement du DOM
document.addEventListener('DOMContentLoaded', function () {
    const body = document.querySelector('body');
    body.insertAdjacentHTML('afterbegin', modalHTML); // Insérer la modal avant le reste

    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        locale: 'fr',
        buttonText: {
            today: "Aujourd'hui",
            month: 'Mois',
            week: 'Semaine',
            day: 'Jour',
        },
        events: events, // Utiliser les données récupérées
        eventDisplay: 'block',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        },
        navLinks: true,
        editable: false,
        selectable: false,

        // Nouveau gestionnaire d'événement pour le clic sur un événement
        eventClick: function (info) {
            info.jsEvent.preventDefault();

            const eventTitleEl = document.getElementById('eventTitle');
            const eventStartEl = document.getElementById('eventStart');
            const eventDetailsEl = document.getElementById('eventDetails');

            if (eventTitleEl && eventStartEl && eventDetailsEl) {
                eventTitleEl.innerText = info.event.title || 'Sans titre';
                eventStartEl.innerText = info.event.start ? info.event.start.toLocaleString() : 'Date inconnue';

                const details = info.event.extendedProps.details;
                let detailsHtml = '';
                if (details) {
                    for (const [key, value] of Object.entries(details)) {
                        detailsHtml += `<p><strong>${key} :</strong> ${value}</p>`;
                    }
                } else {
                    detailsHtml = '<p>Aucun détail supplémentaire.</p>';
                }
                eventDetailsEl.innerHTML = detailsHtml;

                // Initialiser correctement la modal
                const eventModalEl = document.getElementById('eventModal');
                const eventModal = new bootstrap.Modal(eventModalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                eventModal.show();
            } else {
                console.error('Un ou plusieurs éléments de la modal sont introuvables.');
            }
        }
    });
    calendar.render();

    // Forcer le rafrâchissement quand on affiche l'onglet
    document.querySelector('button[data-bs-target="#calendar"]').addEventListener('shown.bs.tab', function () {
        setTimeout(function () {
            calendar.render();
        }, 100);
    });
});

// Structure de la modal
const modalHTML = `
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Date de début :</strong> <span id="eventStart"></span></p>
        <div id="eventDetails"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>`;

// Style CSS pour forcer l'affichage
const style = document.createElement('style');
style.textContent = `
#eventTitle, #eventStart, #eventDetails {
    display: block !important;
    color: black !important;
    font-size: 16px !important;
}`;
document.head.appendChild(style);
