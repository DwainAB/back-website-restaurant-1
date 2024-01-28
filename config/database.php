<?php

// Les informations de connexion à la base de données MySQL
$host = 'sql11.freemysqlhosting.net';
$db   = 'sql11680370'; // Nom de la base de données
$user = 'sql11680370'; // Nom d'utilisateur
$pass = 'Q9E7b1CLh7'; // Vous devriez chercher le mot de passe dans votre e-mail

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Créez un nouvel objet PDO pour la connexion à la base de données MySQL
    $pdo = new PDO($dsn, $user, $pass, $options);
    // La ligne suivante est pour le débogage, vous pouvez la commenter ou la supprimer plus tard
} catch (PDOException $e) {
    die("Erreur de connexion à la base de donnée : " . $e->getMessage());
}

// Votre code pour interagir avec la base de données peut aller ici
