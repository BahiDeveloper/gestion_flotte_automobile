document.addEventListener('DOMContentLoaded', function () {
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
    });
    calendar.render();

    // Forcer le rafraîchissement quand on affiche l'onglet
    document.querySelector('button[data-bs-target="#calendar"]').addEventListener('shown.bs.tab', function () {
        setTimeout(function () {
            calendar.render();
        }, 100); // Petit délai pour bien recalculer la hauteur
    });
});