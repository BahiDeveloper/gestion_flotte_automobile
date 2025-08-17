<?php
// auth/services/MailService.php

require_once '../../vendor/autoload.php'; // Si vous utilisez Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $mail;
    private $siteUrl;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = 'ssl0.ovh.net'; // Serveur SMTP OVH
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'gestion_flotte@aes.ci'; // Votre email OVH
        $this->mail->Password = 'DIak12345'; // Votre mot de passe
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port = 465; // Port SSL d'OVH
        $this->mail->CharSet = 'UTF-8';

        // URL de base du site
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $this->siteUrl = $protocol . $host;
    }

    public function sendPasswordResetEmail($email, $token, $nom, $prenom)
    {
        try {
            // Configuration de l'email
            $this->mail->setFrom('gestion_flotte@aes.ci', 'Gestion de Flotte');
            $this->mail->addAddress($email, $nom . ' ' . $prenom);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Réinitialisation de votre mot de passe - Gestion de Flotte';

            // Construction du lien de réinitialisation
            $resetLink = $this->siteUrl . '/auth/views/reset_password.php?token=' . $token;

            // Corps du message
            $this->mail->Body = $this->getPasswordResetTemplate($nom, $prenom, $resetLink);
            $this->mail->AltBody = "Bonjour $prenom $nom,\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\nPour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : $resetLink\n\nSi vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.\n\nCordialement,\nL'équipe Gestion de Flotte";

            // Envoi de l'email
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
            return false;
        }
    }

    private function getPasswordResetTemplate($nom, $prenom, $resetLink)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2d63c8; color: white; padding: 15px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .button { display: inline-block; background-color: #2d63c8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Réinitialisation de votre mot de passe</h2>
                </div>
                <div class="content">
                    <p>Bonjour <strong>' . $prenom . ' ' . $nom . '</strong>,</p>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe sur notre plateforme de Gestion de Flotte.</p>
                    <p>Pour créer un nouveau mot de passe, veuillez cliquer sur le bouton ci-dessous :</p>
                    <p style="text-align: center;">
                        <a href="' . $resetLink . '" class="button">Réinitialiser mon mot de passe</a>
                    </p>
                    <p>Si le bouton ne fonctionne pas, vous pouvez copier et coller le lien suivant dans votre navigateur :</p>
                    <p>' . $resetLink . '</p>
                    <p>Ce lien expirera dans 1 heure pour des raisons de sécurité.</p>
                    <p>Si vous n\'êtes pas à l\'origine de cette demande, veuillez ignorer cet email.</p>
                    <p>Cordialement,<br>L\'équipe Gestion de Flotte</p>
                </div>
                <div class="footer">
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>';
    }
}