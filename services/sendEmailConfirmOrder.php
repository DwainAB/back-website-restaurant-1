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

function sendMailConfirmOrder($clientEmail, $clientFirstName, $clientLastName, $clientRefOrder, $refRestaurant)
{
    // Crée une instance ; passer `true` active les exceptions
    $mail = new PHPMailer(true);

    // Paramètres du serveur
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();                                            // Envoie en utilisant SMTP
    $mail->Host       = 'smtp.hostinger.com';                   // Définit le serveur SMTP pour l'envoi (vérifiez auprès de Hostinger pour le serveur correct)
    $mail->SMTPAuth   = true;                                   // Active l'authentification SMTP
    $mail->Username   = 'contact@sasyumeats.com';               // Nom d'utilisateur SMTP
    $mail->Password   = 'YumEats2024@';                   // Mot de passe SMTP
    $mail->SMTPSecure = 'ssl';                                  // Active le cryptage SSL implicite
    $mail->Port       = 587;

    // Destinataires
    $mail->setFrom('dwaincontact@gmail.com', 'Dwain');         // Email et nom de l'expéditeur
    $mail->addAddress($clientEmail, $clientFirstName . ' ' . $clientLastName); // Ajoute un destinataire
    $mail->addReplyTo('dwain93290@icloud.com'); // A quel mail doit repondre le client

    // Contenu
    $mail->isHTML(true);                                        // Définit le format de l'email en HTML
    $mail->Subject = "[Nom de l'Application] - Votre commande est prête";                         // Sujet de l'email
    $mail->Body    =
        "Bonjour " . $clientFirstName . ",<br><br>" .

        "Nous sommes ravis de vous informer que votre commande est prête à être récupérée au restaurant ! 🎉" . "<br><br>" .

        "Nous tenons à vous remercier sincèrement d'avoir choisi " . $refRestaurant . " pour votre repas. C'est un plaisir de vous servir !" . " <br><br>" .

        "Voici les détails de votre commande :" . "<br>" .

        "- Référence de commande : " . $clientRefOrder . "<br>" .
        "- Nom du restaurant : " . $refRestaurant . "<br><br>" .

        "Passez quand vous le pouvez pour la récupérer." . "<br><br>" .

        "Encore une fois, merci pour votre confiance et à bientôt !" . "<br><br>" .

        "Cordialement," . "<br>" .
        $refRestaurant;
    try {
        $mail->send();
        return ['success' => true, 'message' => "Mail envoyé"];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur envoi mail: ' . $e->getMessage()];
    }
}


function sendMailConfirmOrderDelivery($clientEmail, $clientFirstName, $clientLastName, $clientRefOrder, $refRestaurant)
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
    $mail->addAddress($clientEmail, $clientFirstName . ' ' . $clientLastName); // Ajoute un destinataire
    $mail->addReplyTo('dwain93290@icloud.com'); // A quel mail doit repondre le client

    // Contenu
    $mail->isHTML(true);                                        // Définit le format de l'email en HTML
    $mail->Subject = "[Nom de l'Application] - Votre commande est prête";                         // Sujet de l'email
    $mail->Body    =
        "Bonjour " . $clientFirstName . ",<br><br>" .

        "aaaaaaaaaaaaaaaaaaa ! 🎉" . "<br><br>" .

        "Nous tenons à vous remercier sincèrement d'avoir choisi " . $refRestaurant . " pour votre repas. C'est un plaisir de vous servir !" . " <br><br>" .

        "Voici les détails de votre commande :" . "<br>" .

        "- Référence de commande : " . $clientRefOrder . "<br>" .
        "- Nom du restaurant : " . $refRestaurant . "<br><br>" .

        "Passez quand vous le pouvez pour la récupérer." . "<br><br>" .

        "Encore une fois, merci pour votre confiance et à bientôt !" . "<br><br>" .

        "Cordialement," . "<br>" .
        $refRestaurant;
    try {
        $mail->send();
        return ['success' => true, 'message' => "Mail envoyé"];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur envoi mail: ' . $e->getMessage()];
    }
}
