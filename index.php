<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Access-Control-Allow-Headers, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

header('Content-Type: application/json');

require_once "config/database.php";  // Assurez-vous que cela configure $pdo avec votre instance PDO

// Utilisateur
require_once 'models/usersModel.php';  // Votre modèle utilisateur
require_once 'controllers/usersController.php';  // Votre contrôleur utilisateur

$userModel = new UserModel($pdo);
$userController = new UserController($userModel);

// Plats (Foods)
require_once 'models/foodModel.php';  // Votre modèle pour les plats
require_once 'controllers/foodController.php';  // Votre contrôleur pour les plats

$foodModel = new FoodModel($pdo);
$foodController = new FoodController($foodModel);

require_once 'routes/router.php';  // Votre système de routage

$subfolderPrefix = '/back-website-restaurant-1';
$requestUri = str_replace($subfolderPrefix, '', strtok($_SERVER["REQUEST_URI"], '?'));

// Vous pouvez ajouter une logique ici pour déterminer si la requête concerne un utilisateur ou un plat, 
// et appeler handleRequest avec le contrôleur approprié.
if (strpos($requestUri, '/api/users') === 0) {
    handleRequest($requestUri, $userController);
} else if (strpos($requestUri, '/api/foods') === 0) {
    handleRequest($requestUri, $foodController);
} else {
    // Gérer les requêtes non reconnues ou renvoyer une erreur 404
    header("HTTP/1.1 404 Not Found");
}
