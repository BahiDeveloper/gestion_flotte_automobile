document.addEventListener('DOMContentLoaded', function () {
    const vehicleCards = document.querySelectorAll('.vehicle-card');
    const searchInput = document.getElementById('searchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterType = document.getElementById('filterType');
    const pagination = document.getElementById('pagination');
    const itemsPerPageSelect = document.getElementById('itemsPerPage'); // Nouveau sélecteur
    let currentPage = 1;
    let itemsPerPage = parseInt(itemsPerPageSelect.value); // Valeur initiale

    // Mettre à jour itemsPerPage lorsque l'utilisateur change la sélection
    itemsPerPageSelect.addEventListener('change', function () {
        itemsPerPage = parseInt(this.value); // Mettre à jour itemsPerPage
        currentPage = 1; // Revenir à la première page
        filterVehicles(); // Re-filtrer et mettre à jour la pagination
    });

    // Fonction pour filtrer les véhicules
    function filterVehicles() {
        const searchText = searchInput.value.toLowerCase().trim();
        const statusFilter = filterStatus.value.toLowerCase().trim();
        const typeFilter = filterType.value.toLowerCase().trim();

        vehicleCards.forEach(card => {
            const model = card.querySelector('.card-text:nth-child(1)').textContent.toLowerCase().trim();
            const brand = card.querySelector('.card-text:nth-child(2)').textContent.toLowerCase().trim();
            const registration = card.querySelector('.card-text:nth-child(3)').textContent.toLowerCase().trim();
            const status = card.querySelector('.badge').textContent.toLowerCase().trim();
            const type = card.querySelector('.card-title').textContent.toLowerCase().trim();

            const matchesSearch = model.includes(searchText) || brand.includes(searchText) || registration.includes(searchText);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;
            const matchesType = typeFilter === 'all' || type === typeFilter;

            if (matchesSearch && matchesStatus && matchesType) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        updatePagination();
    }

    // Fonction pour mettre à jour la pagination
    function updatePagination() {
        const visibleCards = Array.from(vehicleCards).filter(card => card.style.display !== 'none');
        const totalPages = Math.ceil(visibleCards.length / itemsPerPage);

        pagination.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', () => {
                currentPage = i;
                showPage(currentPage);
            });
            pagination.appendChild(li);
        }

        showPage(currentPage);
    }

    // Fonction pour afficher les cartes de la page sélectionnée
    function showPage(page) {
        const visibleCards = Array.from(vehicleCards).filter(card => card.style.display !== 'none');
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        visibleCards.forEach((card, index) => {
            if (index >= start && index < end) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Écouteurs d'événements
    searchInput.addEventListener('input', filterVehicles);
    filterStatus.addEventListener('change', filterVehicles);
    filterType.addEventListener('change', filterVehicles);

    // Initialisation
    filterVehicles();
});

// // Gestion du formulaire de maintenance
// document.getElementById('maintenanceForm').addEventListener('submit', function (e) {
//     e.preventDefault();
//     alert('Maintenance planifiée avec succès !');
// });

// // Gestion du formulaire d'assignation
// document.getElementById('assignmentForm').addEventListener('submit', function (e) {
//     e.preventDefault();
//     alert('Véhicule assigné avec succès !');
// });


