<?php

// Permet d'éviter les problèmes CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Access-Control-Allow-Headers, X-Requested-With");

// Permet de gérer les requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

header('Content-Type: application/json');

require_once "config/database.php";
require_once 'controllers/foodController.php';  // Utilisez le contrôleur des plats
require_once 'models/foodModel.php';  // Utilisez le modèle des plats

// Crée une instance du modèle FoodModel et du contrôleur FoodController avec le modèle
$model = new FoodModel($pdo);
$controller = new FoodController($model);

require_once 'routes/router.php';

$requestUri = strtok($_SERVER["REQUEST_URI"], '?');
handleRequest($requestUri, $controller);

?>
