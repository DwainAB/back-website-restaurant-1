<?php

class FoodController
{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function test()
    {
        print('test');
    }

    public function getAllFoods($ref_restaurant)
    {
        $foods = $this->model->getAllFoods($ref_restaurant);
        if ($foods !== false) {
            echo json_encode($foods);
        } else {
            http_response_code(500); // Erreur de serveur interne
            echo json_encode(["error" => "Échec de la récupération des plats"]);
        }
    }


    public function uploadImageFromReactNative($imageURI)
    {
        $dossierDestination = "images/"; // Dossier de destination pour sauvegarder les images

        // Vérifier si $imageURI est une chaîne JSON
        if (is_string($imageURI)) {
            $imageData = json_decode($imageURI); // Convertir la chaîne JSON en objet
            if ($imageData) {
                $imageURIObject = $imageData->imageURI;
                $fileName = $imageURIObject->fileName;
                $mimeType = $imageURIObject->type;
                // Décodage de l'image
                $base64Image = $imageURIObject->base64;
                $decodedImage = base64_decode($base64Image);

                $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                // Enregistrement de l'image
                $fichierTemporaire = tempnam(sys_get_temp_dir(), 'image');
                file_put_contents($fichierTemporaire, $decodedImage);

                $destinationFinale = $dossierDestination . $fileName;
                rename($fichierTemporaire, $destinationFinale);

                // Retourner le chemin de l'image enregistrée
                return $destinationFinale;
            }
        }

        // En cas d'échec, retourner false ou null
        return null;
    }


    public function addFood()
    {
        // Assurez-vous que les données requises sont présentes dans la demande
        if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['category']) && isset($_POST['price']) && isset($_POST['ref_restaurant'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $price = $_POST['price'];
            $refRestaurant = $_POST['ref_restaurant']; // Ajout de la référence du restaurant

            // Gérer l'upload de fichier
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageFile = $_FILES['image'];
                $imagePath = 'images/' . basename($imageFile['name']);
                move_uploaded_file($imageFile['tmp_name'], $imagePath);
            } elseif (isset($_POST['imageURI'])) {
                $imageData = $_POST['imageURI'];
                $imagePath = $this->uploadImageFromReactNative($imageData);
                if (!$imagePath) {
                    // Échec de l'enregistrement de l'image
                    http_response_code(500); // Erreur interne du serveur
                    echo json_encode(array("message" => "Impossible d'enregistrer l'image."));
                    return;
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Image manquante."));
                return;
            }

            if ($this->model->addFood($title, $description, $category, $price, $imagePath, $refRestaurant)) {
                // Le produit a été ajouté avec succès
                http_response_code(201); // Code de succès pour création
                echo json_encode(array("message" => "Produit ajouté avec succès."));
            } else {
                // Une erreur s'est produite lors de l'ajout du produit
                http_response_code(500); // Erreur interne du serveur
                echo json_encode(array("message" => "Impossible d'ajouter le produit."));
            }
        } else {
            // Mauvaise demande
            http_response_code(400);
            $missingFields = [];
            if (!isset($_POST['title'])) {
                $missingFields[] = 'title';
            }
            if (!isset($_POST['description'])) {
                $missingFields[] = 'description';
            }
            if (!isset($_POST['category'])) {
                $missingFields[] = 'category';
            }
            if (!isset($_POST['price'])) {
                $missingFields[] = 'price';
            }
            if (!isset($_POST['ref_restaurant'])) {
                $missingFields[] = 'ref_restaurant'; // Ajout de la référence manquante
            }
            echo json_encode(array("message" => "Données manquantes. Veuillez fournir les champs suivants : " . implode(', ', $missingFields)));
        }
    }


    public function updateFood($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $keys = ['title', 'description', 'category', 'price'];
            $data = [];

            foreach ($keys as $key) {
                if (isset($_POST[$key])) {
                    $data[$key] = htmlspecialchars($_POST[$key], ENT_QUOTES, 'UTF-8');
                }
            }
            $updateImage = false; // Flag pour indiquer si l'image doit être mise à jour
            if (isset($_POST['imageURI'])) {
                // Un nouveau fichier a été sélectionné, procédez à l'upload
                $imageData = $_POST['imageURI'];
                $imagePath = $this->uploadImageFromReactNative($imageData);
                if (!$imagePath) {
                    // Échec de l'enregistrement de l'image
                    http_response_code(500); // Erreur interne du serveur
                    echo json_encode(array("message" => "Impossible d'enregistrer l'image."));
                    return;
                }

                $data['image'] = $imagePath;
                $updateImage = true; // Définissez le drapeau pour indiquer que l'image doit être mise à jour
            }

            if (!$updateImage && !isset($_FILES['image'])) {
                // Aucun nouveau fichier n'a été sélectionné, ne mettez pas à jour l'image
                unset($data['image']);
            }

            $success = $this->model->updateFood($id, $data);

            if ($success) {
                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Plat mis à jour avec succès."]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Une erreur est survenue lors de la mise à jour du plat."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Mauvaise méthode de requête. Les mises à jour doivent être faites avec POST."]);
        }
    }


    public function deleteFood($id)
    {
        // Récupérer le nom du fichier image associé au produit
        $imageName = $this->model->getImageNameById($id);

        // Supprimer le produit de la base de données
        $success = $this->model->deleteFood($id);

        if ($success) {
            // Supprimer l'image du dossier images si elle existe
            if ($imageName && file_exists($imageName)) {
                unlink($imageName);
            } else {
                echo "Le dossier ou le nom n'existe pas ";
            }

            // Envoyer une réponse JSON avec le code de réussite
            http_response_code(204);
            echo json_encode(['message' => 'Suppression réussie']);
        } else {
            // Envoyer une réponse JSON avec le code d'erreur
            http_response_code(400);
            echo json_encode(['message' => 'Échec de la suppression du plat']);
        }
    }




    public function addClientAndOrder()
    {
        // Récupérez le JSON brut de la demande
        $json = file_get_contents('php://input');

        // Décoder le JSON en tableau associatif
        $data = json_decode($json, true);

        // Assurez-vous que toutes les données requises sont présentes
        $requiredFields = ['firstname', 'lastname', 'email', 'phone', 'address', 'method', 'payment', 'cartItems'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            // Certaines données requises sont manquantes
            http_response_code(400);
            echo json_encode(array("message" => "Données manquantes pour la commande.", "missingFields" => $missingFields));
            return;
        }

        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $email = $data['email'];
        $phone = $data['phone'];
        $address = $data['address'];
        $method = $data['method'];
        $payment = $data['payment'];
        $cartItems = $data['cartItems'];
        $ref_restaurant = $data['ref_restaurant']; // Ajout de 'ref_restaurant'

        // Générer ref_order
        $ref_order = '#' . strtoupper(substr($lastname, 0, 1)) . mt_rand(1000, 9999);

        //récupère la date d'aujourd'hui
        $currentDate = date('Y-m-d');

        // Ajouter le client
        $clientId = $this->model->addClient($firstname, $lastname, $email, $phone, $address, $method, $payment, $ref_order, $ref_restaurant);
        $clientDataId = $this->model->addDataClient($firstname, $lastname, $email, $phone, $address, $method, $payment, $ref_order, $ref_restaurant, $currentDate);

        if ($clientId !== false && $clientDataId !== false) {
            // Ajouter chaque produit de la commande
            foreach ($cartItems as $item) {
                $productId = $item['id'];
                $quantity = $item['quantity'];
                $this->model->addOrder($clientId, $productId, $quantity, $ref_restaurant);
                $this->model->addDataOrder($clientDataId, $productId, $quantity, $ref_restaurant, $currentDate);
            }

            http_response_code(201); // Code de succès pour création
            echo json_encode(array("message" => "Commande ajoutée avec succès."));
        } else {
            // Erreur lors de l'ajout du client
            http_response_code(500);
            echo json_encode(array("message" => "Erreur lors de l'ajout du client."));
        }
    }



    public function getClientsWithOrders($ref_restaurant)
    {
        $clientsWithOrders = $this->model->getClientsWithOrders($ref_restaurant);
        if ($clientsWithOrders !== false) {
            echo json_encode($clientsWithOrders);
        } else {
            http_response_code(500); // Erreur de serveur interne
            echo json_encode(["error" => "Échec de la récupération des clients avec les commandes"]);
        }
    }

    public function getClientsWithOrdersData($ref_restaurant)
    {
        $clientsWithOrders = $this->model->getClientsWithOrdersdata($ref_restaurant);
        if ($clientsWithOrders !== false) {
            echo json_encode($clientsWithOrders);
        } else {
            http_response_code(500); // Erreur de serveur interne
            echo json_encode(["error" => "Échec de la récupération des clients avec les commandes"]);
        }
    }


    public function deleteClient($clientId)
    {
        try {
            // Récupérer les données du corps de la requête
            $requestData = json_decode(file_get_contents('php://input'), true);

            // Vérifier si les données sont valides
            if (
                isset($requestData['clientRefOrder']) &&
                isset($requestData['clientLastName']) &&
                isset($requestData['clientFirstName']) &&
                isset($requestData['clientEmail']) &&
                isset($requestData['clientMethod']) &&
                isset($requestData['refRestaurant'])
            ) {
                // Récupérer les informations supplémentaires du corps de la requête
                $clientRefOrder = $requestData['clientRefOrder'];
                $clientLastName = $requestData['clientLastName'];
                $clientFirstName = $requestData['clientFirstName'];
                $clientEmail = $requestData['clientEmail'];
                $clientMethod = $requestData['clientMethod'];
                $refRestaurant = $requestData['refRestaurant'];

                // Supprimer le client et ses commandes
                $this->model->deleteClientAndOrders($clientId);

                // Utiliser les informations supplémentaires comme nécessaire
                require_once './services/sendEmailConfirmOrder.php';

                if ($clientMethod === "A emporter") {
                    sendMailConfirmOrder($clientEmail, $clientFirstName, $clientLastName, $clientRefOrder, $refRestaurant);
                } elseif ($clientMethod === "Livraison") {
                    sendMailConfirmOrderDelivery($clientEmail, $clientFirstName, $clientLastName, $clientRefOrder, $refRestaurant);
                } else {
                    echo json_encode(['message' => 'Pas de méthode de commande trouver']);
                }

                // Répondre avec succès
                http_response_code(200);
                echo json_encode(['message' => 'Le client et ses commandes ont été supprimés avec succès.']);
            } else {
                // Les données fournies ne sont pas complètes
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes dans la requête.']);
            }
        } catch (Exception $e) {
            // En cas d'erreur, répondre avec un code d'erreur 500
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la suppression du client: ' . $e->getMessage()]);
        }
    }



    public function deleteClientdata($clientId)
    {
        try {
            $this->model->deleteClientAndOrdersData($clientId);
            http_response_code(200);
            echo json_encode(['message' => 'Le client et ses commandes ont été supprimés avec succès.']);
        } catch (Exception $e) {
            http_response_code(500); // Erreur de serveur interne
            echo json_encode(['error' => 'Erreur lors de la suppression du client: ' . $e->getMessage()]);
        }
    }

    public function addCategory()
    {
        // Assurez-vous que le nom de la catégorie est présent dans la demande
        if (isset($_POST['name'])) {
            $name = $_POST['name'];
            $refRestaurant = isset($_POST['ref_restaurant']) ? $_POST['ref_restaurant'] : ''; // Récupérer la valeur de ref_restaurant ou utiliser une valeur par défaut

            // Appeler la méthode du modèle pour ajouter la catégorie
            $categoryId = $this->model->addCategory($name, $refRestaurant);

            if ($categoryId !== false) {
                // La catégorie a été ajoutée avec succès
                http_response_code(201); // Code de succès pour création
                echo json_encode(["success" => "Catégorie ajoutée avec succès.", "categoryId" => $categoryId]);
            } else {
                // Une erreur s'est produite lors de l'ajout de la catégorie
                http_response_code(500); // Erreur interne du serveur
                echo json_encode(["message" => "Impossible d'ajouter la catégorie."]);
            }
        } else {
            // Le nom de la catégorie est manquant
            http_response_code(400); // Mauvaise demande
            echo json_encode(["message" => "Donnée manquante. Veuillez fournir le nom de la catégorie."]);
        }
    }


    public function getAllCategories($ref_restaurant)
    {
        $categories = $this->model->getAllCategories($ref_restaurant);
        if ($categories !== false) {
            echo json_encode($categories);
        } else {
            http_response_code(500); // Erreur de serveur interne
            echo json_encode(["error" => "Échec de la récupération des catégories"]);
        }
    }

    public function deleteCategory($id)
    {
        $success = $this->model->deleteCategory($id);

        if ($success) {
            http_response_code(204); // Aucun contenu, mais succès
        } else {
            http_response_code(500); // Erreur de serveur interne
            echo json_encode(['message' => 'Échec de la suppression de la catégorie']);
        }
    }
}
