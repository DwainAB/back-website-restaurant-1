<?php

class FoodModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAllFoods()
    {
        $query = "SELECT * FROM products";
        $stmt = $this->db->query($query);

        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public function getFoodById($id)
    {
        $query = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }


    public function addFood($title, $description, $category, $price, $imagePath)
    {
        $query = "INSERT INTO products (title, description, category, price, image) VALUES (:title, :description, :category, :price, :image)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $imagePath);

        if ($stmt->execute()) {
            return true; // Le produit a été ajouté avec succès
        } else {
            return false; // Une erreur s'est produite lors de l'ajout du produit
        }
    }


    public function updateFood($id, $data)
    {
        $fields = [];

        if (isset($data['title'])) {
            $fields[] = "title = :title";
        }
        if (isset($data['description'])) {
            $fields[] = "description = :description";
        }
        if (isset($data['category'])) {
            $fields[] = "category = :category";
        }
        if (isset($data['price'])) {
            $fields[] = "price = :price";
        }
        if (isset($data['image'])) {
            $fields[] = "image = :image";
        }

        // Ne procédez à la mise à jour que si au moins un champ est défini
        if (count($fields) > 0) {
            $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            // Liaison dynamique des paramètres
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            foreach ($fields as $field) {
                if (strpos($field, 'title =') !== false) {
                    $stmt->bindParam(':title', $data['title']);
                } elseif (strpos($field, 'description =') !== false) {
                    $stmt->bindParam(':description', $data['description']);
                } elseif (strpos($field, 'category =') !== false) {
                    $stmt->bindParam(':category', $data['category']);
                } elseif (strpos($field, 'price =') !== false) {
                    $stmt->bindParam(':price', $data['price']);
                } elseif (strpos($field, 'image =') !== false) {
                    $stmt->bindParam(':image', $data['image']);
                }
            }

            return $stmt->execute();
        }

        return false;
    }


    public function deleteFood($id)
    {
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function addClient($firstname, $lastname, $email, $phone, $address, $method)
    {
        $query = "INSERT INTO clients (firstname, lastname, email, phone, address, method) VALUES (:firstname, :lastname, :email, :phone, :address, :method)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':method', $method);

        if ($stmt->execute()) {
            return $this->db->lastInsertId(); // Retourne l'ID du client ajouté
        } else {
            return false;
        }
    }

    public function addOrder($clientId, $productId, $quantity)
    {
        $query = "INSERT INTO orders (client_id, product_id, quantity, date) VALUES (:client_id, :product_id, :quantity, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':client_id', $clientId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':quantity', $quantity);

        return $stmt->execute();
    }




    public function getClientsWithOrders()
    {
        // Augmenter la taille maximale pour GROUP_CONCAT pour cette session
        $this->db->exec("SET SESSION group_concat_max_len = 1000000;");

        // Votre requête existante, sans JSON_OBJECT
        $stmt = $this->db->prepare("
            SELECT 
            c.id AS client_id,
            c.firstname AS client_firstname,
            c.lastname AS client_lastname,
            c.email AS client_email,
            c.phone AS client_phone,
            c.address AS client_address,
            o.id AS order_id,
            o.quantity AS order_quantity,
            o.date AS order_date,
            p.id AS product_id,
            p.title AS product_title,
            p.price AS product_price
        FROM clients c
        LEFT JOIN orders o ON c.id = o.client_id
        LEFT JOIN products p ON o.product_id = p.id
        GROUP BY c.id;
        ");
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($clients as $client) {
            $clientData = [
                'client_id' => $client['client_id'],
                'client_firstname' => $client['client_firstname'],
                'client_lastname' => $client['client_lastname'],
                'client_email' => $client['client_email'],
                'client_phone' => $client['client_phone'],
                'client_address' => $client['client_address'],
                'orders' => []
            ];
            if ($client['order_id'] !== null) {
                $clientData['orders'][] = [
                    'order_id' => $client['order_id'],
                    'order_quantity' => $client['order_quantity'],
                    'order_date' => $client['order_date'],
                    'product_id' => $client['product_id'],
                    'product_title' => $client['product_title'],
                    'product_price' => $client['product_price']
                ];
            }
            $results[] = $clientData;
        }

        return $results;
    }




    public function deleteClientAndOrders($clientId)
    {
        try {
            $this->db->beginTransaction();

            // Suppression des commandes du client
            $stmt = $this->db->prepare("DELETE FROM orders WHERE client_id = :clientId");
            $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                // Log l'erreur si la requête échoue
                error_log("Erreur lors de la suppression des commandes: " . implode(", ", $stmt->errorInfo()));
            }

            // Suppression du client
            $stmt = $this->db->prepare("DELETE FROM clients WHERE id = :clientId");
            $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                // Log l'erreur si la requête échoue
                error_log("Erreur lors de la suppression du client: " . implode(", ", $stmt->errorInfo()));
            }

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            // Log l'exception
            error_log("Exception lors de la suppression: " . $e->getMessage());
            throw $e;
        }
    }


    public function addCategory($name)
    {
        $query = "INSERT INTO categorys (name) VALUES (:name)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);

        if ($stmt->execute()) {
            return $this->db->lastInsertId(); // Retourne l'ID de la catégorie ajoutée
        } else {
            return false;
        }
    }

    public function getAllCategories()
    {
        $query = "SELECT * FROM categorys";
        $stmt = $this->db->query($query);

        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public function deleteCategory($id)
    {
        $query = "DELETE FROM categorys WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
