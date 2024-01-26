<?php
// googlePlacesProxy.php

header('Access-Control-Allow-Origin: *'); // Autorise les requêtes CORS de votre application React
header('Content-Type: application/json'); // Défi

// Assurez-vous que le serveur accepte uniquement les requêtes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Remplacez par votre clé API
    $apiKey = 'AIzaSyBWCVfz4hi__lsl5xctq5O1D90VCezfzP4';

    // Obtenez l'ID de Place depuis la requête GET
    $placeId = isset($_GET['placeId']) ? $_GET['placeId'] : '';
    $language = 'fr';

    // Construisez l'URL pour l'API Google Places
    $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id=$placeId&language=$language&fields=name,rating,review,user_ratings_total,photos&key=$apiKey";

    // Faites la requête à l'API Google Places
    $response = file_get_contents($url);

    // Convertissez la réponse en un objet PHP
    $data = json_decode($response, true);

    // Vérifiez si la réponse contient des avis
    if (isset($data['result']['reviews'])) {
        // Filtrer les avis pour ne garder que ceux avec une note supérieure à 2
        $filteredReviews = array_filter($data['result']['reviews'], function ($review) {
            return $review['rating'] > 2;
        });

        // Trier les avis filtrés du plus récent au plus ancien
        usort($filteredReviews, function ($review1, $review2) {
            return $review2['time'] - $review1['time'];
        });

        // Remplacer les avis non filtrés par les avis filtrés
        $data['result']['reviews'] = array_values($filteredReviews); // array_values pour réindexer le tableau
    }

    // Convertissez le résultat filtré en JSON et retournez-le
    echo json_encode($data);
} else {
    // Gérez le cas où la méthode de la requête n'est pas GET
    echo json_encode(['error' => 'Méthode de requête non autorisée']);
}
