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

// Crée une instance ; passer `true` active les exceptions
if (!isset($_POST['email'], $_POST['firstName'], $_POST['lastName'])) {
    // Retourne une erreur si les paramètres nécessaires ne sont pas présents
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}
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
    $mail->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']); // Ajoute un destinataire
    $mail->addReplyTo('dwaincontact@gmail.com', 'Information'); // Réponse à l'email de l'expéditeur

    // Contenu
    $mail->isHTML(true);                                        // Définit le format de l'email en HTML
    $mail->Subject = "Commande en préparation !";                         // Sujet de l'email
    $mail->Body    = "Bonjour " . $_POST['firstName'] . " ,<br><br>" . "Nous avons bien reçu ta commande et nous t'en remercions ! " . " ,<br><br>" . "Elle sera disponible d'ici 30min"; // Message de l'email

    try {
        $mail->send();
        echo json_encode(['message' => "Mail envoyé"]);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Erreur envoi mail: ' . $e->getMessage()]);
    }

