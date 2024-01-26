<?php

// Spécifier le chemin absolu vers votre fichier de base de données SQLite
// Assurez-vous que le chemin d'accès et les permissions sont corrects
$path = __DIR__ . '/data.sqlite';

try {
    // Créez un nouvel objet PDO pour la connexion à la base de données SQLite
    $pdo = new PDO('sqlite:' . $path);
    // Configurez les attributs PDO comme nécessaire
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // La ligne suivante est pour le débogage, vous pouvez la commenter ou la supprimer plus tard
    echo 'Connexion réussie à la base de données SQLite.';
} catch (PDOException $e) {
    die("Erreur de connexion à la base de donnée : " . $e->getMessage());
}

// Votre code pour interagir avec la base de données peut aller ici
