<?php

// Importe les classes PHPMailer dans l'espace de noms global
// Celles-ci doivent être en haut de votre script, pas à l'intérieur d'une fonction
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fichiers requis
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Crée une instance ; passer `true` active les exceptions
if (isset($_POST["send"])) {

    $mail = new PHPMailer(true);

    // Paramètres du serveur
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
    $mail->Subject = $_POST["Commande reçu !"];                         // Sujet de l'email
    $mail->Body    = "Bonjour " . $_POST['firstname'] . " " . $_POST['lastname'] . ",<br><br>" . "Votre commande à bien été envoyé !"; // Message de l'email

    try {
        $mail->send();
        echo json_encode(['message' => "Mail envoyé"]);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Erreur envoi mail: ' . $e->getMessage()]);
    }
}
