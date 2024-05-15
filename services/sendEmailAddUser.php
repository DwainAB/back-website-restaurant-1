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

function sendMailAddUser($email, $firstName, $lastName, $password, $ref_restaurant)
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
    $mail->addAddress($email, $firstName . ' ' . $lastName); // Ajoute un destinataire
    $mail->addReplyTo('dwain93290@icloud.com'); // Réponse à l'email de l'expéditeur

    // Contenu
    $mail->isHTML(true);                                        // Définit le format de l'email en HTML
    $mail->Subject = "[Nom de l'Application] - Vos Informations de Connexion";                         // Sujet de l'email
    $mail->Body    =
        "Bonjour" . $firstName . ",<br><br>" .

        "Nous sommes ravis de vous accueillir sur [Nom de l'Application] ! Veuillez touver ci-dessous vos informations de connexion : " . " ,<br><br>" .

        "Nom de votre restaurant : " . $ref_restaurant . " <br>" .
        "Adresse Email : " . $email . "<br>" .
        "Mot de passe : " . $password . "<br><br>" .

        "Vous pouvez utiliser ces informations pour vous connecter à votre compte et explorer les fonctionnalités de l'application." . "<br>" .
        'Pour changer votre mot de passe rendez vous dans les paramètres, puis dans la rubrique "Changer le mot de passe."' . "<br><br>" .

        "N'hésitez pas à nous contacter si vous avez des questions ou des préoccupations." . "<br><br>" .

        "Cordialement," . "<br><br>" .

        "[Nom de l'application]";

    try {
        $mail->send();
        return ['success' => true, 'message' => "Mail envoyé"];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur envoi mail: ' . $e->getMessage()];
    }
}
