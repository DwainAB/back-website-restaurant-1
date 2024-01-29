<?php
// Vérifiez si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous que le champ email et les noms sont présents
    if (isset($_POST['email']) && isset($_POST['firstName']) && isset($_POST['lastName'])) {
        // Récupérez les données du formulaire
        $email = $_POST['email'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];

        // Préparez le message
        $to = $email; // Destinataire de l'email
        $subject = "Confirmation de commande"; // Sujet de l'email
        $message = "Bonjour " . $firstName . " " . $lastName . ",\n\nVotre commande a été reçue et est en traitement.\n\nCordialement,\nL'équipe du restaurant.";

        // Pour envoyer un email HTML, l'en-tête Content-type doit être défini
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // En-têtes additionnels
        $headers .= 'From: dwaincontact@gmail.com' . "\r\n"; // Remplacez par votre adresse email d'envoi

        // Envoi de l'email
        if (mail($to, $subject, $message, $headers)) {
            echo "Email de confirmation envoyé à : " . $email;
        } else {
            echo "L'envoi de l'email de confirmation a échoué.";
        }
    } else {
        echo "Tous les champs n'ont pas été renseignés.";
    }
} else {
    echo "Méthode de requête non autorisée.";
}
