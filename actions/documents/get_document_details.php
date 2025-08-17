<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration et la connexion à la base de données
require_once '../../database/config.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier que l'ID est fourni
$id_document = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_document) {
    echo json_encode(['success' => false, 'message' => 'ID du document non valide']);
    exit;
}

try {
    // Récupérer les détails du document avec les informations jointes
    $query = "SELECT d.*,
              v.marque, v.modele, v.immatriculation,
              CONCAT(c.nom, ' ', c.prenoms) as chauffeur_nom,
              CONCAT(u.nom, ' ', u.prenom) as utilisateur_nom
              FROM documents_administratifs d
              LEFT JOIN vehicules v ON d.id_vehicule = v.id_vehicule
              LEFT JOIN chauffeurs c ON d.id_chauffeur = c.id_chauffeur
              LEFT JOIN utilisateurs u ON d.id_utilisateur = u.id_utilisateur
              WHERE d.id_document = :id_document";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_document', $id_document, PDO::PARAM_INT);
    $stmt->execute();

    if ($document = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Déterminer si le fichier est une image
        $file_extension = strtolower(pathinfo($document['fichier_url'], PATHINFO_EXTENSION));
        $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif']);

        // Déterminer le texte de fréquence
        $frequence_text = 'Permanent';
        if ($document['frequence_renouvellement']) {
            switch ($document['frequence_renouvellement']) {
                case 1:
                    $frequence_text = 'Mensuel';
                    break;
                case 3:
                    $frequence_text = 'Trimestriel';
                    break;
                case 6:
                    $frequence_text = 'Semestriel';
                    break;
                case 12:
                    $frequence_text = 'Annuel';
                    break;
                default:
                    $frequence_text = $document['frequence_renouvellement'] . ' mois';
            }
        }

        // Formater les données pour le front-end
        $result = [
            'success' => true,
            'document' => [
                'id_document' => $document['id_document'],
                'type_document' => ucfirst(str_replace('_', ' ', $document['type_document'])),
                'numero_document' => $document['numero_document'],
                'date_emission' => $document['date_emission'],

                // Modification pour gérer les documents permanents
                'date_expiration' => $document['frequence_renouvellement'] == 0 ? null : $document['date_expiration'],
                'frequence_renouvellement' => $frequence_text,

                'fournisseur' => $document['fournisseur'],
                'prix' => $document['prix'],
                'vehicule_info' => $document['id_vehicule'] ?
                    "{$document['marque']} {$document['modele']} ({$document['immatriculation']})" : null,
                'chauffeur_info' => $document['chauffeur_nom'],
                'utilisateur_info' => $document['utilisateur_nom'],
                'fichier_url' => $document['fichier_url'],
                'is_image' => $is_image,
                'statut' => $document['statut'],
                'note' => $document['note'],
                'created_at' => $document['created_at'],
                'updated_at' => $document['updated_at']
            ]
        ];

        // Ajouter l'entrée dans le journal d'activités
        if (isset($_SESSION['id_utilisateur'])) {
            $action_description = "Consultation du document #{$document['id_document']} - " .
                ucfirst(str_replace('_', ' ', $document['type_document']));

            $log_query = "INSERT INTO journal_activites (id_utilisateur, type_activite, description, ip_address) 
                         VALUES (:id_utilisateur, 'view_document', :description, :ip)";
            $log_stmt = $pdo->prepare($log_query);
            $log_stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur'], PDO::PARAM_INT);
            $log_stmt->bindParam(':description', $action_description, PDO::PARAM_STR);
            $log_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
            $log_stmt->execute();
        }

        $expiration_display = '';
        if ($result['document']['frequence_renouvellement'] === 'Permanent') {
            $expiration_display = '<span class="badge bg-secondary">Permanent</span>';
        } else {
            $expiration_display = date('d/m/Y', strtotime($result['document']['date_expiration']));
        }

        // Construction du HTML pour les détails du document
        $result['html'] = "
            <div class='container'>
                <div class='row mb-4'>
                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-header bg-primary text-white'>
                                <h5 class='mb-0'><i class='fas fa-info-circle me-2'></i>Informations générales</h5>
                            </div>
                            <div class='card-body'>
                                <dl class='row'>
                                    <dt class='col-sm-4'>Type:</dt>
                                    <dd class='col-sm-8'>" . $result['document']['type_document'] . "</dd>
                                    
                                    <dt class='col-sm-4'>N° document:</dt>
                                    <dd class='col-sm-8'>" . ($result['document']['numero_document'] ?: 'Non spécifié') . "</dd>
                                    
                                    <dt class='col-sm-4'>Émission:</dt>
                                    <dd class='col-sm-8'>" . date('d/m/Y', strtotime($result['document']['date_emission'])) . "</dd>
                                    
                                    <dt class='col-sm-4'>Expiration:</dt>
                                    <dd class='col-sm-8'>" . $expiration_display . "</dd>
                                    
                                    <dt class='col-sm-4'>Fréquence:</dt>
                                    <dd class='col-sm-8'>" . $result['document']['frequence_renouvellement'] . "</dd>
                                    
                                    <dt class='col-sm-4'>Fournisseur:</dt>
                                    <dd class='col-sm-8'>" . htmlspecialchars($result['document']['fournisseur']) . "</dd>
                                    
                                    <dt class='col-sm-4'>Prix:</dt>
                                    <dd class='col-sm-8'>" . number_format($result['document']['prix']?? 0, 0, ',', ' ') . " FCFA</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-header bg-info text-white'>
                                <h5 class='mb-0'><i class='fas fa-link me-2'></i>Rattachement</h5>
                            </div>
                            <div class='card-body'>
                                <dl class='row'>
                                    <dt class='col-sm-4'>Véhicule:</dt>
                                    <dd class='col-sm-8'>" . ($result['document']['vehicule_info'] ?: 'Aucun') . "</dd>
                                    
                                    <dt class='col-sm-4'>Chauffeur:</dt>
                                    <dd class='col-sm-8'>" . ($result['document']['chauffeur_info'] ?: 'Aucun') . "</dd>
                                    
                                    <dt class='col-sm-4'>Utilisateur:</dt>
                                    <dd class='col-sm-8'>" . $result['document']['utilisateur_info'] . "</dd>
                                    
                                    <dt class='col-sm-4'>Ajouté le:</dt>
                                    <dd class='col-sm-8'>" . date('d/m/Y H:i', strtotime($result['document']['created_at'])) . "</dd>
                                    
                                    <dt class='col-sm-4'>Dernière MAJ:</dt>
                                    <dd class='col-sm-8'>" . date('d/m/Y H:i', strtotime($result['document']['updated_at'])) . "</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-12'>
                        <div class='card'>
                            <div class='card-header bg-secondary text-white'>
                                <h5 class='mb-0'><i class='fas fa-sticky-note me-2'></i>Notes</h5>
                            </div>
                            <div class='card-body'>
                                <p class='mb-0'>" . ($result['document']['note'] ?: 'Aucune note') . "</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='row mt-4'>
                    <div class='col-12'>
                        <div class='card'>
                            <div class='card-header bg-dark text-white'>
                                <h5 class='mb-0'><i class='fas fa-file me-2'></i>Aperçu du document</h5>
                            </div>
                            <div class='card-body text-center bg-light p-4'>";

        if ($result['document']['is_image']) {
            $result['html'] .= "<img src='uploads/documents/" . $result['document']['fichier_url'] . "' 
                                class='img-fluid' style='max-height: 500px;' 
                                alt='Aperçu du document'>";
        } else {
            $result['html'] .= "<div class='py-5'>
                                <i class='fas fa-file-pdf fa-5x text-danger'></i>
                                <p class='mt-3'>Document PDF - Cliquez sur Télécharger pour voir le contenu</p>
                            </div>";
        }

        $result['html'] .= "    </div>
                        </div>
                    </div>
                </div>
            </div>";

        // Renvoyer la réponse JSON
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Document non trouvé']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur inattendue: ' . $e->getMessage()]);
}
?>