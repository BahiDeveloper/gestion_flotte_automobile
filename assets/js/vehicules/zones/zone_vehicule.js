$(document).ready(function () {
    let debounceTimer;
    const $zoneVehiculeInput = $("#zone_vehicule");
    const $idZoneInput = $("#id_zone");

    // Initialisation de l'autocomplétion avec récupération de l'id_zone
    $zoneVehiculeInput.autocomplete({
        source: function (request, response) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                $.ajax({
                    url: "request/vehicules/zones/get_zones.php",
                    method: "GET",
                    data: { term: request.term },
                    dataType: "json",
                    success: function (data) {
                        response(data);
                    },
                    error: function () {
                        Swal.fire({
                            icon: "error",
                            title: "Erreur",
                            text: "Erreur lors de la récupération des zones.",
                        });
                    }
                });
            }, 300);
        },
        select: function (event, ui) {
            $zoneVehiculeInput.val(ui.item.label);
            $idZoneInput.val(ui.item.value);
            return false;
        },
        minLength: 0  // Permettre l'affichage même sans saisie
    });

    // Afficher la liste au focus
    $zoneVehiculeInput.on('focus', function () {
        $(this).autocomplete("search", "");
    });

    // Vérifier la zone quand l'utilisateur quitte le champ
    $zoneVehiculeInput.on('blur', function () {
        const zoneSaisie = $(this).val().trim();
        const idZone = $idZoneInput.val();

        if (zoneSaisie && !idZone) {
            checkZone(zoneSaisie, function (success) {
                // Pas besoin d'action supplémentaire ici car checkZone gère déjà tout
            });
        }
    });

    // Vérifier et créer une zone si elle n'existe pas
    function checkZone(zoneSaisie, callback) {
        $.ajax({
            url: "request/vehicules/zones/check_zone.php",
            method: "GET",
            data: { zone: zoneSaisie },
            dataType: "json",
            success: function (response) {
                if (!response.exists) {
                    Swal.fire({
                        title: `Créer "${zoneSaisie}" ?`,
                        text: "Cette zone n'existe pas. Voulez-vous la créer ?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonText: "Oui, créer",
                        cancelButtonText: "Annuler"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "actions/vehicules/zones/create_zone.php",
                                method: "POST",
                                data: { zone: zoneSaisie },
                                dataType: "json",
                                success: function (res) {
                                    if (res.success) {
                                        Swal.fire({
                                            icon: "success",
                                            title: "Succès",
                                            text: "Zone créée avec succès !",
                                        });
                                        // Récupérer immédiatement `id_zone` après la création
                                        $.ajax({
                                            url: "request/vehicules/zones/get_zones.php",
                                            method: "GET",
                                            data: { term: zoneSaisie },
                                            dataType: "json",
                                            success: function (data) {
                                                const newZone = data.find(z => z.label === zoneSaisie);
                                                if (newZone) {
                                                    $idZoneInput.val(newZone.value);
                                                    callback(true);
                                                }
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Erreur",
                                            text: res.message,
                                        });
                                        callback(false);
                                    }
                                },
                                error: function () {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Erreur",
                                        text: "Erreur lors de la création de la zone.",
                                    });
                                    callback(false);
                                }
                            });
                        } else {
                            callback(false);
                        }
                    });
                } else {
                    $idZoneInput.val(response.id_zone);
                    callback(true);
                }
            },
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Erreur",
                    text: "Erreur lors de la vérification de la zone.",
                });
                callback(false);
            }
        });
    }

    // Vérification avant soumission du formulaire
    $('#addVehicleForm').on('submit', function (e) {
        e.preventDefault();

        const zoneSaisie = $zoneVehiculeInput.val().trim();
        const idZone = $idZoneInput.val();

        if (!idZone && zoneSaisie) {
            checkZone(zoneSaisie, function (success) {
                if (success) {
                    $('#addVehicleForm').off('submit').submit();
                }
            });
        } else if (idZone) {
            $('#addVehicleForm').off('submit').submit();
        } else {
            Swal.fire({
                icon: "error",
                title: "Erreur",
                text: "Veuillez sélectionner ou créer une zone valide.",
            });
        }
    });
});