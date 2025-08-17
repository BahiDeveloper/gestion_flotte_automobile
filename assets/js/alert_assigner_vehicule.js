document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('assignmentForm');

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Succès !',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'gestion_vehicules.php'; // Rediriger après succès
                    });
                } else {
                    Swal.fire({
                        title: 'Erreur !',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Erreur !',
                    text: 'Une erreur s\'est produite lors de la soumission du formulaire.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
    });
});