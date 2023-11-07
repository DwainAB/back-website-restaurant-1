<?php

class FoodController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function getAllFoods() {
        $foods = $this->model->getAllFoods();
        if ($foods !== false) {
            echo json_encode($foods);
        } else {
            http_response_code(500); // Erreur de serveur interne
            echo json_encode(["error" => "Échec de la récupération des plats"]);
        }
    }

    public function getFoodById($id) {
        $food = $this->model->getFoodById($id);
        if ($food !== false) {
            echo json_encode($food);
        } else {
            http_response_code(404); // Non trouvé
            echo json_encode(["error" => "Plat non trouvé"]);
        }
    }


    public function addFood($data) {
        // Assurez-vous que les données requises sont présentes dans la demande
        if (isset($data['title']) && isset($data['description']) && isset($data['price']) && isset($data['image'])) {
            $title = $data['title'];
            $description = $data['description'];
            $price = $data['price'];
            $imagePath = 'images/' . basename($data['image']); // Assurez-vous de stocker l'image dans le dossier "images"

            if ($this->model->addFood($title, $description, $price, $imagePath)) {
                // Le produit a été ajouté avec succès
                http_response_code(201); // Code de succès pour création
                echo json_encode(array("message" => "Produit ajouté avec succès."));
            } else {
                // Une erreur s'est produite lors de l'ajout du produit
                http_response_code(500); // Erreur interne du serveur
                echo json_encode(array("message" => "Impossible d'ajouter le produit."));
            }
        } else {
            // Les données requises sont manquantes
            http_response_code(400); // Mauvaise demande
            echo json_encode(array("message" => "Données manquantes. Veuillez fournir title, description, price et image."));
        }
    }
    

    public function updateFood($id, $data) {
        $title = $data['title'];
        $description = $data['description'];
        $price = $data['price'];
        $image = $data['image'];

        if (!empty($title) && !empty($description) && !empty($price) && !empty($image)) {
            $success = $this->model->updateFood($id, $title, $description, $price, $image);

            if ($success) {
                return ['success' => true, 'message' => 'Plat mis à jour avec succès'];
            } else {
                return ['success' => false, 'message' => 'Échec de la mise à jour du plat'];
            }
        } else {
            return ['success' => false, 'message' => 'Tous les champs sont obligatoires'];
        }
    }

    public function deleteFood($id) {
        $success = $this->model->deleteFood($id);

        if ($success) {
            return ['success' => true, 'message' => 'Plat supprimé avec succès'];
        } else {
            return ['success' => false, 'message' => 'Échec de la suppression du plat'];
        }
    }
}

