document.addEventListener('DOMContentLoaded', function () {
    const vehicleSelect = document.getElementById('assignmentVehicle');
    const dateDepartInput = document.getElementById('assignmentDateDepart');
    const dateArriveeInput = document.getElementById('assignmentDateArrivee');
    const form = document.getElementById('assignmentForm');

    const popover = new bootstrap.Popover(vehicleSelect, {
        trigger: 'manual',
        placement: 'auto',
        boundary: 'window',
        container: 'body',
        html: true,
    });

    vehicleSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const vehicleState = selectedOption.getAttribute('data-state');
        const availableDate = selectedOption.getAttribute('data-available-date');

        if (vehicleState !== 'Disponible') {
            let message = `État actuel : <strong>${vehicleState}</strong>.`;
            if (availableDate) {
                const formattedDate = new Date(availableDate).toLocaleString();
                message += `<br>Disponible à partir du <strong>${formattedDate}</strong>.`;
            }
            popover._config.content = message;
            popover.show();

            const rect = vehicleSelect.getBoundingClientRect();
            const popoverElement = document.querySelector('.popover');
            if (popoverElement) {
                popoverElement.style.position = "absolute";
                popoverElement.style.top = `${rect.bottom + window.scrollY + 5}px`;
                popoverElement.style.left = `${rect.right + window.scrollX + 5}px`;
            }
        } else {
            popover.hide();
        }
    });

    document.addEventListener('click', function (event) {
        if (!vehicleSelect.contains(event.target)) {
            popover.hide();
        }
    });

    form.addEventListener('submit', function (event) {
        const dateDepart = new Date(dateDepartInput.value);
        const dateArrivee = new Date(dateArriveeInput.value);
        const now = new Date();
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        const assignedDates = selectedOption.getAttribute('data-dates-assignees');

        if (dateDepart >= dateArrivee) {
            event.preventDefault();
            Swal.fire({
                title: 'Erreur !',
                text: 'La date de départ doit être antérieure à la date d\'arrivée.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (dateDepart < now || dateArrivee < now) {
            event.preventDefault();
            Swal.fire({
                title: 'Erreur !',
                text: 'Les dates doivent être aujourd\'hui ou dans le futur.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (assignedDates) {
            const dateRanges = assignedDates.split(';').map(range => {
                const [start, end] = range.split('|').map(d => new Date(d));
                return { start, end };
            });

            for (const range of dateRanges) {
                if ((dateDepart >= range.start && dateDepart <= range.end) ||
                    (dateArrivee >= range.start && dateArrivee <= range.end) ||
                    (dateDepart <= range.start && dateArrivee >= range.end)) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Erreur !',
                        text: 'Ce véhicule est déjà assigné sur cette période.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            }
        }
    });

    const nowISO = new Date().toISOString().slice(0, 16);
    dateDepartInput.min = nowISO;
    dateArriveeInput.min = nowISO;
});
