<?php

// Les informations de connexion à la base de données MySQL
$host = 'localhost';
$db   = 'db-wok-rosny'; // Nom de la base de données
$user = 'root'; // Nom d'utilisateur
$pass = 'root'; // Vous devriez chercher le mot de passe dans votre e-mail

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de donnée : " . $e->getMessage());
}
