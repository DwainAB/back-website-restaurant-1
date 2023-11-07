<?php

$routes = [
    '/back-wok-rosny/index.php/api/foods' => 'getAllFoods',
    '/back-wok-rosny/index.php/api/foods/{id}' => 'getFoodById',
    '/back-wok-rosny/index.php/api/foods/add' => 'addFood',
    '/back-wok-rosny/index.php/api/foods/delete/{id}' => 'deleteFood',
    '/back-wok-rosny/index.php/api/foods/update/{id}' => 'updateFood',
];

function handleRequest($requestUri, $controller) {
    global $routes;

    foreach ($routes as $route => $action) {
        if (strpos($route, '{id}') !== false) {
            $baseRoute = str_replace('{id}', '', $route);

            if (strpos($requestUri, $baseRoute) === 0) {
                $id = str_replace($baseRoute, '', $requestUri);

                if (method_exists($controller, $action)) {
                    $controller->$action($id);
                    return;
                }
            }
        } elseif ($requestUri == $route) {
            if (method_exists($controller, $action)) {
                $controller->$action();
                return;
            }
        }
    }

    http_response_code(404);
    echo json_encode(["error" => "La route n'existe pas"]);
}
