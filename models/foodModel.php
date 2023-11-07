<?php

class FoodModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getAllFoods() {
        $query = "SELECT * FROM food";
        $stmt = $this->db->query($query);
    
        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public function getFoodById($id) {
        $query = "SELECT * FROM food WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }
    

    public function addFood($title, $description, $price, $imagePath) {
        $query = "INSERT INTO products (title, description, price, image) VALUES (:title, :description, :price, :image)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $imagePath);

        if ($stmt->execute()) {
            return true; // Le produit a été ajouté avec succès
        } else {
            return false; // Une erreur s'est produite lors de l'ajout du produit
        }
    }

    public function updateFood($id, $title, $description, $price, $image) {
        $query = "UPDATE food SET title = :title, description = :description, price = :price, image = :image WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteFood($id) {
        $query = "DELETE FROM food WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
