<?php
// googlePlacesProxy.php

header('Access-Control-Allow-Origin: *'); // Autorise les requêtes CORS de votre application React
header('Content-Type: application/json'); // Définir le type de contenu comme JSON

// Assurez-vous que le serveur accepte uniquement les requêtes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Remplacez par votre clé API
    $apiKey = 'AIzaSyAeVfqojCJakVaA4gEqW3w40mXYodWDKr4';

    $placeId = isset($_GET['placeId']) ? $_GET['placeId'] : '';
    $language = 'fr'; // Fixer la langue à français

    // Construisez l'URL pour l'API Google Places
    $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id=$placeId&language=$language&fields=name,rating,reviews,user_ratings_total,photos&key=$apiKey";

    // Faites la requête à l'API Google Places
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Vérifiez si la réponse contient des avis
    if (isset($data['result']['reviews'])) {
        $reviews = $data['result']['reviews'];
        $totalReviews = count($reviews);
    } else {
        $reviews = [];
        $totalReviews = 0;
    }

    // Préparez la réponse finale
    $finalData = [
        'result' => [
            'reviews' => $reviews,
            'total_reviews' => $totalReviews
        ]
    ];

    // Convertissez le résultat en JSON et retournez-le
    echo json_encode($finalData);
} else {
    echo json_encode(['error' => 'Méthode de requête non autorisée']);
}
