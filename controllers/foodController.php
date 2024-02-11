<?php

class FoodController
{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getAllFoods()
    {
        $foods = $this->model->getAllFoods();
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
        $imageData = json_decode(file_get_contents("php://input"));
        echo json_encode($imageData->imageURI);

        if($imageData->imageURI){
            $fileName = $imageData->imageURI->fileName;
            $base64 = $imageData->imageURI->base64;

            $data = base64_decode($base64);
            $success = file_put_contents($fileName, $data);
            
            if(success){
                move_uploaded_file($file_tmp,"images/".$file_name);
                echo json_encode("Success");
            }else{
                echo json_encode("Failed");
            }
        }

        echo json_encode('Failes');

    }

    public function addFood()
    {
        // Assurez-vous que les données requises sont présentes dans la demande
        if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['category']) && isset($_POST['price'])) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $price = $_POST['price'];

            // Gérer l'upload de fichier
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageFile = $_FILES['image'];
                $imagePath = 'images/' . basename($imageFile['name']);
                move_uploaded_file($imageFile['tmp_name'], $imagePath);
            } elseif (isset($_POST['imageURI'])) {
                var_dump($_POST['imageURI']);
                echo json_encode(array("message" => 'reussi' ));
                $imagePath = $this->uploadImageFromReactNative($_POST['imageURI']);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Image manquante."));
                return;
            }

            if ($this->model->addFood($title, $description, $category, $price, $imagePath)) {
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
            echo json_encode(array("message" => "Données manquantes. Veuillez fournir les champs suivants : " . implode(', ', $missingFields)));
            
        }
    }



/*public function addFood()
{
    // Assurez-vous que les données requises sont présentes dans la demande
    if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['category']) && isset($_POST['price'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $price = $_POST['price'];

        // Gérer l'upload de fichier
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageFile = $_FILES['image'];
            $imagePath = 'images/' . basename($imageFile['name']);
            move_uploaded_file($imageFile['tmp_name'], $imagePath);
        } elseif (isset($_POST['imageURI'])) {
            // Télécharger l'image depuis l'URI
            $imageURI = $_POST['imageURI'];
            $imageContent = file_get_contents($imageURI);
            $imagePath = 'images/' . basename($imageURI);
            file_put_contents($imagePath, $imageContent);
        } else {
            http_response_code(400); // Mauvaise demande
            echo json_encode(array("message" => "Image manquante."));
            return;
        }

        if ($this->model->addFood($title, $description, $category, $price, $imagePath)) {
            // Le produit a été ajouté avec succès
            http_response_code(201); // Code de succès pour création
            echo json_encode(array("message" => "Produit ajouté avec succès."));
        } else {
            // Une erreur s'est produite lors de l'ajout du produit
            http_response_code(500); // Erreur interne du serveur
            echo json_encode(array("message" => "Impossible d'ajouter le produit."));
        }
    } else {
        http_response_code(400); // Mauvaise demande
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
        echo json_encode(array("message" => "Données manquantes. Veuillez fournir les champs suivants : " . implode(', ', $missingFields)));
    }
}
*/



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

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Un nouveau fichier a été sélectionné, procédez à l'upload
                $imageFileName = time() . '-' . basename($_FILES['image']['name']);
                $imagePath = 'images/' . $imageFileName;
                move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);

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
                echo json_encode(["success" => false, "error" => "Une erreur est survenue lors de la mise à jour du plat."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Mauvaise méthode de requête. Les mises à jour doivent être faites avec POST."]);
        }
    }






    public function deleteFood($id)
    {
        $success = $this->model->deleteFood($id);

        if ($success) {
            http_response_code(204);
        } else {
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
        $requiredFields = ['firstname', 'lastname', 'email', 'phone', 'address', 'cartItems'];
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
        $cartItems = $data['cartItems'];

        // Ajouter le client
        $clientId = $this->model->addClient($firstname, $lastname, $email, $phone, $address, $method);

        if ($clientId !== false) {
            // Ajouter chaque produit de la commande
            foreach ($cartItems as $item) {
                $productId = $item['id'];
                $quantity = $item['quantity'];
                $this->model->addOrder($clientId, $productId, $quantity);
            }

            http_response_code(201); // Code de succès pour création
            echo json_encode(array("message" => "Commande ajoutée avec succès."));
        } else {
            // Erreur lors de l'ajout du client
            http_response_code(500);
            echo json_encode(array("message" => "Erreur lors de l'ajout du client."));
        }
    }

    public function getClientsWithOrders()
    {
        $clientsWithOrders = $this->model->getClientsWithOrders();
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
            $this->model->deleteClientAndOrders($clientId);
            http_response_code(200);
            echo json_encode(['message' => 'Le client et ses commandes ont été supprimés avec succès.']);
        } catch (Exception $e) {
            http_response_code(500); // Erreur de serveur interne
            echo json_encode(['error' => 'Erreur lors de la suppression du client: ' . $e->getMessage()]);
        }
    }


    public function addCategory()
    {
        // Récupérez le corps de la requête brute
        $json = file_get_contents('php://input');

        // Décoder le JSON en tableau associatif
        $data = json_decode($json, true);

        // Assurez-vous que le nom de la catégorie est présent dans la demande
        if (isset($data['name'])) {
            $name = $data['name'];

            // Appeler la méthode du modèle pour ajouter la catégorie
            $categoryId = $this->model->addCategory($name);

            if ($categoryId !== false) {
                // La catégorie a été ajoutée avec succès
                http_response_code(201); // Code de succès pour création
                echo json_encode(["message" => "Catégorie ajoutée avec succès.", "categoryId" => $categoryId]);
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


    public function getAllCategories()
    {
        $categories = $this->model->getAllCategories();
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
