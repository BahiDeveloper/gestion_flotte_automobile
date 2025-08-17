<?php
// auth/services/SmsService.php

class SmsService
{
    private $appKey;
    private $appSecret;
    private $consumerKey;
    private $serviceUrl;
    private $sender;

    public function __construct()
    {
        // Configuration des identifiants OVH SMS
        // Ces informations doivent être obtenues depuis votre espace client OVH
        $this->appKey = 'YOUR_APP_KEY'; // Remplacez par votre clé d'application OVH
        $this->appSecret = 'YOUR_APP_SECRET'; // Remplacez par votre secret d'application OVH
        $this->consumerKey = 'YOUR_CONSUMER_KEY'; // Remplacez par votre clé de consommateur OVH
        $this->serviceUrl = 'https://eu.api.ovh.com/1.0'; // URL de l'API OVH Europe
        $this->sender = 'GestFlotte'; // Nom de l'expéditeur (10 caractères max, alphanumériques uniquement)
    }

    /**
     * Envoie un SMS à un numéro de téléphone spécifié
     *
     * @param string $phoneNumber Le numéro de téléphone destinataire (format international, ex: +22507XXXXXXXX)
     * @param string $message Le contenu du message à envoyer
     * @return boolean True si l'envoi a réussi, false sinon
     */
    public function send($phoneNumber, $message)
    {
        try {
            // Formatage du numéro de téléphone au format international si nécessaire
            if (substr($phoneNumber, 0, 1) !== '+') {
                // Si le numéro commence par 0, on le remplace par le code pays (ex: +225 pour la Côte d'Ivoire)
                if (substr($phoneNumber, 0, 1) === '0') {
                    $phoneNumber = '+225' . substr($phoneNumber, 1);
                } else {
                    $phoneNumber = '+225' . $phoneNumber;
                }
            }

            // Création de la requête OVH API pour envoyer un SMS
            $timestamp = time();
            $url = $this->serviceUrl . '/sms/jobs';

            // Préparation des données de la requête
            $data = json_encode([
                'charset' => 'UTF-8',
                'coding' => '7bit',
                'message' => $message,
                'noStopClause' => true,
                'priority' => 'high',
                'receivers' => [$phoneNumber],
                'sender' => $this->sender,
                'senderForResponse' => false
            ]);

            // Génération de la signature pour authentification OVH
            $signature = $this->computeSignature('POST', $url, $data, $timestamp);

            // Configuration de la requête cURL
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Ovh-Application: ' . $this->appKey,
                    'X-Ovh-Timestamp: ' . $timestamp,
                    'X-Ovh-Signature: ' . $signature,
                    'X-Ovh-Consumer: ' . $this->consumerKey
                ],
            ]);

            // Exécution de la requête
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            // Traitement de la réponse
            if ($err) {
                error_log('Erreur cURL lors de l\'envoi du SMS: ' . $err);
                return false;
            }

            // Vérification du code HTTP
            if ($httpCode >= 200 && $httpCode < 300) {
                error_log('SMS envoyé avec succès au ' . $phoneNumber);
                return true;
            } else {
                error_log('Erreur lors de l\'envoi du SMS: ' . $response);
                return false;
            }

        } catch (Exception $e) {
            error_log('Exception lors de l\'envoi du SMS: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcule la signature pour l'authentification à l'API OVH
     */
    private function computeSignature($method, $url, $body, $timestamp)
    {
        // Extraction du chemin relatif de l'URL complète
        $url_parts = parse_url($url);
        $url_path = $url_parts['path'];

        // Création de la signature selon les spécifications OVH
        $signature_string = $this->appSecret . '+' . $this->consumerKey . '+' . $method . '+' . $url_path . '+' . $body . '+' . $timestamp;
        $signature = '$1$' . sha1($signature_string);

        return $signature;
    }

    /**
     * Méthode alternative pour tester l'envoi de SMS sans OVH API (pour développement)
     */
    public function sendDev($phoneNumber, $message)
    {
        // Enregistrement du SMS dans les logs pour les tests
        error_log('DEV MODE - SMS envoyé à ' . $phoneNumber . ': ' . $message);
        return true;
    }

    /**
     * Vérifie si un numéro de téléphone existe réellement
     * 
     * @param string $phoneNumber Le numéro à vérifier
     * @return boolean True si le numéro est valide
     */
    public function verifyPhoneNumber($phoneNumber)
    {
        // Pour production, utilisez un service comme Twilio, Nexmo, etc.
        // qui offre des API de vérification de numéro
        // 
        // Exemple avec Twilio Lookup (pseudo-code):
        // $result = $twilioClient->lookups->v2->phoneNumbers($phoneNumber)->fetch();
        // return $result->valid;

        // En développement, toujours retourner true
        return true;
    }
}