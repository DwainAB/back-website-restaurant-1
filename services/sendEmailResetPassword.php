<?php

// Autoriser les requêtes de n'importe quelle origine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


// Importe les classes PHPMailer dans l'espace de noms global
// Celles-ci doivent être en haut de votre script, pas à l'intérieur d'une fonction
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fichiers requis
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sendMailAddUser($email, $password)
{
    // Crée une instance ; passer `true` active les exceptions
    $mail = new PHPMailer(true);

    // Paramètres du serveur
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();                                            // Envoie en utilisant SMTP
    $mail->Host       = 'smtp.gmail.com';                       // Définit le serveur SMTP pour l'envoi
    $mail->SMTPAuth   = true;                                   // Active l'authentification SMTP
    $mail->Username   = 'dwaincontact@gmail.com';               // Nom d'utilisateur SMTP
    $mail->Password   = 'mzrfykgngaybzdqr';                    // Mot de passe SMTP
    $mail->SMTPSecure = 'ssl';                                  // Active le cryptage SSL implicite
    $mail->Port       = 465;

    // Destinataires
    $mail->setFrom('dwaincontact@gmail.com', 'Dwain');         // Email et nom de l'expéditeur
    $mail->addAddress($email); // Ajoute un destinataire
    $mail->addReplyTo('dwain93290@icloud.com'); // Réponse à l'email de l'expéditeur

    // Contenu
    $mail->isHTML(true);                                        // Définit le format de l'email en HTML
    $mail->Subject = "YumEats - Demande de Réinitialisation de Mot de Passe";                         // Sujet de l'email
    $mail->Body    =
        "Bonjour" . ",<br><br>" .

        "Nous avons reçu une demande de réinitialisation de votre mot de passe pour votre compte sur notre application mobile." . "<br><br>" .

        "Votre nouveau mot de passe temporaire est : " . $password . " <br><br>" .

        "Pour changer ce mot de passe temporaire et en définir un nouveau, veuillez suivre les étapes ci-dessous :" . "<br><br>" .

        "1- Ouvrez notre application et connectez-vous à votre compte avec le mot de passe temporaire ci-dessus." . "<br>" .
        "2- Accédez aux paramètres de votre compte." . "<br>" .
        "3- Sélectionnez la rubrique 'Changer le mot de passe'." . "<br>" .
        "4- Suivez les instructions à l'écran pour renseigner et confirmer votre nouveau mot de passe." . "<br><br>" .

        "Si vous n'êtes pas à l'origine de cette demande, nous vous recommandons de vérifier la sécurité de votre compte et de contacter notre support immédiatement." . "<br><br>" .

        "Nous vous remercions pour votre confiance." . "<br><br>" .

        "Cordialement," . "<br><br>" .

        "YumEats" . "<br>" .
        "Support clients" . "<br>" .
        "support@sasyumeats.com" . "<br>" .
        "07 61 24 42 84" . "<br>";

    try {
        $mail->send();
        return ['success' => true, 'message' => "Mail envoyé"];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur envoi mail: ' . $e->getMessage()];
    }
}
