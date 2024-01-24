<?php

$routes = [
    '/api/foods' => ['getAllFoods', 'GET'],
    '/api/foods/{id}' => ['getFoodById', 'GET'],
    '/api/foods/add' => ['addFood', 'POST'],
    '/api/foods/delete/{id}' => ['deleteFood', 'DELETE'],
    '/api/foods/update/{id}' => ['updateFood', 'POST'],
    '/api/foods/addClientAndOrder' => ['addClientAndOrder', 'POST'],
    '/api/foods/orders' => ['getClientsWithOrders', 'GET'],
    '/api/foods/deleteClient/{id}' => ['deleteClient', 'DELETE'],
    '/api/foods/addCategory' => ['addCategory', 'POST'],
    '/api/foods/categories' => ['getAllCategories', 'GET'],
    '/api/foods/categories/delete/{id}' => ['deleteCategory', 'DELETE'],
    '/api/users/addUsers' => ['addUser', 'POST'],
    '/api/users' => ['getAllUsers', 'GET'],
    '/api/users/update/{id}' => ['updateUser', 'POST'],
    '/api/users/getUser/{id}' => ['getUser', 'GET'],
    "/api/users/delete/{id}" => ['deleteUser', 'DELETE'],
    '/api/users/login' => ['login', 'POST'],
];

function handleRequest($requestUri, $controller)
{
    global $routes;

    $requestUri = rtrim($requestUri, '/');
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    foreach ($routes as $route => $actionMethod) {
        list($action, $method) = $actionMethod;
        // Remplacez les placeholders par des regex pour capturer les paramètres.
        $routeWithRegex = preg_replace('/{[a-zA-Z0-9_]+}/', '([a-zA-Z0-9_]+)', $route);
        if (preg_match('#^' . $routeWithRegex . '$#D', $requestUri, $matches)) {
            array_shift($matches); // Enlevez la première correspondance qui est l'URI complète
            if ($requestMethod === $method && method_exists($controller, $action)) {
                if ($requestMethod === 'PUT') {
                    parse_str(file_get_contents("php://input"), $put_vars);
                    call_user_func_array([$controller, $action], array_merge($matches, [$put_vars]));
                } else {
                    call_user_func_array([$controller, $action], $matches);
                }
                return;
            }
        }
    }

    http_response_code(404);
    echo json_encode(["error" => "La route n'existe pas"]);
}

// Exemple d'utilisation :
// $controller = new YourControllerClass();
// handleRequest($_SERVER['REQUEST_URI'], $controller);
