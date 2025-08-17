function confirmAnnuleeCourse(event, id) {
    event.preventDefault(); // Empêcher la navigation immédiate

    Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Cette action est irréversible !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Oui, annuler la course !",
        cancelButtonText: "Maintenir la course"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("actions/annuler_course_demarree.php?id=" + id)
                .then(response => response.json()) // Convertir la réponse en JSON
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Course annulée !",
                            text: data.message,
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            location.reload(); // Recharger la page pour mettre à jour la liste
                        });
                    } else {
                        Swal.fire({
                            title: "Erreur",
                            text: data.message,
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: "Erreur",
                        text: "Une erreur est survenue lors de l'annulation de la course.",
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                });
        }
    });
}

function confirmSuppressionCourse(event, id) {
    event.preventDefault(); // Empêcher la navigation immédiate

    Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Cette action est irréversible !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Oui, annuler la course !",
        cancelButtonText: "Maintenir la course"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("actions/annuler_course.php?id=" + id)
                .then(response => response.json()) // Convertir la réponse en JSON
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Course annulée !",
                            text: data.message,
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            location.reload(); // Recharger la page pour mettre à jour la liste
                        });
                    } else {
                        Swal.fire({
                            title: "Erreur",
                            text: data.message,
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: "Erreur",
                        text: "Une erreur est survenue lors de l'annulation de la course.",
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                });
        }
    });
}
