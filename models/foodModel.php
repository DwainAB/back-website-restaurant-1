<?php

class FoodModel
{
    private $db;
    private $table = "products";


    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAllFoods($ref_restaurant)
    {
        $ref_restaurant = urldecode($ref_restaurant);
        $stmt = $this->db->prepare("SELECT * FROM $this->table WHERE ref_restaurant = ?");
        $stmt->execute([$ref_restaurant]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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


    public function addFood($title, $description, $category, $price, $imagePath, $refRestaurant)
    {
        $query = "INSERT INTO products (title, description, category, price, image, ref_restaurant) VALUES (:title, :description, :category, :price, :image, :refRestaurant)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':refRestaurant', $refRestaurant); // Liaison de la référence du restaurant

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


    public function getImageNameById($id)
    {
        $query = "SELECT image FROM products WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retourne le nom de l'image ou NULL si aucune image n'est trouvée
        return $result['image'] ?? null;
    }



    public function addClient($firstname, $lastname, $email, $phone, $address, $method, $payment, $ref_order, $ref_restaurant)
    {
        $query = "INSERT INTO clients (firstname, lastname, email, phone, address, method, payment, ref_order, ref_restaurant) VALUES (:firstname, :lastname, :email, :phone, :address, :method, :payment, :ref_order, :ref_restaurant)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':method', $method);
        $stmt->bindParam(':payment', $payment);
        $stmt->bindParam(':ref_order', $ref_order);
        $stmt->bindParam(':ref_restaurant', $ref_restaurant);

        if ($stmt->execute()) {
            return $this->db->lastInsertId(); // Retourne l'ID du client ajouté
        } else {
            return false;
        }
    }

    public function addDataClient($firstname, $lastname, $email, $phone, $address, $method, $payment, $ref_order, $ref_restaurant, $currentDate)
    {
        $query = "INSERT INTO dataclients (firstname, lastname, email, phone, address, method, payment, ref_order, ref_restaurant, date) VALUES (:firstname, :lastname, :email, :phone, :address, :method, :payment, :ref_order, :ref_restaurant, :currentDate)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':method', $method);
        $stmt->bindParam(':payment', $payment);
        $stmt->bindParam(':ref_order', $ref_order);
        $stmt->bindParam(':ref_restaurant', $ref_restaurant);
        $stmt->bindParam(':currentDate', $currentDate);

        if ($stmt->execute()) {
            return $this->db->lastInsertId(); // Retourne l'ID du client ajouté
        } else {
            return false;
        }
    }


    public function addOrder($clientId, $productId, $quantity, $ref_restaurant) // Ajout de $ref_restaurant
    {
        $query = "INSERT INTO orders (client_id, product_id, quantity, date, ref_restaurant) VALUES (:client_id, :product_id, :quantity, NOW(), :ref_restaurant)"; // Ajout de :ref_restaurant
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':client_id', $clientId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':ref_restaurant', $ref_restaurant); // Liaison de :ref_restaurant

        return $stmt->execute();
    }

    public function addDataOrder($clientDataId, $productId, $quantity, $ref_restaurant, $currentDate) // Ajout de $ref_restaurant
    {
        $query = "INSERT INTO dataorders (client_id, product_id, quantity, ref_restaurant, date) VALUES (:client_id, :product_id, :quantity, :ref_restaurant, :currentDate )"; // Ajout de :ref_restaurant
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':client_id', $clientDataId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':ref_restaurant', $ref_restaurant); // Liaison de :ref_restaurant
        $stmt->bindParam(':currentDate', $currentDate); // Liaison de :ref_restaurant

        return $stmt->execute();
    }





    public function getClientsWithOrders($refRestaurant)
    {
        // Augmenter la taille maximale pour GROUP_CONCAT pour cette session
        $this->db->exec("SET SESSION group_concat_max_len = 1000000;");
        $ref_restaurant = urldecode($refRestaurant);

        // Préparer la requête pour récupérer les clients et leurs commandes sous forme de JSON
        $stmt = $this->db->prepare("
        SELECT 
            c.id AS client_id,
            c.ref_order AS client_ref_order,
            c.firstname AS client_firstname,
            c.lastname AS client_lastname,
            c.email AS client_email,
            c.phone AS client_phone,
            c.address AS client_address,
            c.method AS client_method,
            c.payment AS client_payment,
            c.ref_restaurant AS client_ref_restaurant, 
                
            GROUP_CONCAT(
                CONCAT(
                    '{',
                        '\"client_id\":', c.id, ',',
                        '\"ref_restaurant\":\"', c.ref_restaurant, '\",',
                        '\"order_id\":', o.id, ',',
                        '\"order_quantity\":', o.quantity, ',',
                        '\"order_date\":\"', DATE_FORMAT(o.date, '%Y-%m-%dT%TZ'), '\",',
                        '\"product_id\":', p.id, ',',
                        '\"product_title\":\"', REPLACE(p.title, '\"', '\\\"'), '\",',
                        '\"product_description\":\"', REPLACE(p.description, '\"', '\\\"'), '\",',
                        '\"product_price\":', p.price,                        
                    '}'
                )
                SEPARATOR ','
            ) AS orders
        FROM clients c
        LEFT JOIN orders o ON c.id = o.client_id
        LEFT JOIN products p ON o.product_id = p.id
        WHERE c.ref_restaurant = :refRestaurant
        GROUP BY c.id;
    ");
        $stmt->bindParam(":refRestaurant", $ref_restaurant);
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir la chaîne JSON agrégée en un tableau d'objets pour chaque client
        foreach ($clients as $key => $client) {
            if ($client['orders'] !== null) {
                $clients[$key]['orders'] = json_decode('[' . $client['orders'] . ']', true);
            } else {
                $clients[$key]['orders'] = []; // Aucune commande n'est définie sur un tableau vide
            }
        }

        return $clients;
    }



    public function getClientsWithOrdersData($refRestaurant)
    {
        // Augmenter la taille maximale pour GROUP_CONCAT pour cette session
        $this->db->exec("SET SESSION group_concat_max_len = 1000000;");
        $ref_restaurant = urldecode($refRestaurant);

        // Préparer la requête pour récupérer les clients et leurs commandes sous forme de JSON
        $stmt = $this->db->prepare("
        SELECT 
            dc.id AS client_id,
            dc.ref_order AS client_ref_order,
            dc.firstname AS client_firstname,
            dc.lastname AS client_lastname,
            dc.email AS client_email,
            dc.phone AS client_phone,
            dc.address AS client_address,
            dc.method AS client_method,
            dc.payment AS client_payment,
            dc.ref_restaurant AS client_ref_restaurant,
            dc.date AS client_date, 
                        
            GROUP_CONCAT(
                CONCAT(
                    '{',
                        '\"client_id\":', dc.id, ',',
                        '\"ref_restaurant\":\"', dc.ref_restaurant, '\",',
                        '\"order_id\":', do.id, ',',
                        '\"order_quantity\":', do.quantity, ',',
                        '\"order_date\":\"', DATE_FORMAT(do.date, '%Y-%m-%dT%TZ'), '\",',
                        '\"product_id\":', p.id, ',',
                        '\"product_title\":\"', REPLACE(p.title, '\"', '\\\"'), '\",',
                        '\"product_description\":\"', REPLACE(p.description, '\"', '\\\"'), '\",',
                        '\"product_price\":', p.price,                        
                    '}'
                )
                SEPARATOR ','
            ) AS orders
        FROM dataclients dc
        LEFT JOIN dataorders do ON dc.id = do.client_id
        LEFT JOIN products p ON do.product_id = p.id
        WHERE dc.ref_restaurant = :refRestaurant
        GROUP BY dc.id; 
    ");
        $stmt->bindParam(":refRestaurant", $ref_restaurant);
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir la chaîne JSON agrégée en un tableau d'objets pour chaque client
        foreach ($clients as $key => $client) {
            if ($client['orders'] !== null) {
                $clients[$key]['orders'] = json_decode('[' . $client['orders'] . ']', true);
            } else {
                $clients[$key]['orders'] = []; // Aucune commande n'est définie sur un tableau vide
            }
        }

        return $clients;
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

    public function deleteClientAndOrdersData($clientId)
    {
        try {
            $this->db->beginTransaction();

            // Suppression des commandes du client
            $stmt = $this->db->prepare("DELETE FROM dataorders WHERE client_id = :clientId");
            $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                // Log l'erreur si la requête échoue
                error_log("Erreur lors de la suppression des commandes: " . implode(", ", $stmt->errorInfo()));
            }

            // Suppression du client
            $stmt = $this->db->prepare("DELETE FROM dataclients WHERE id = :clientId");
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


    public function addCategory($name, $refRestaurant)
    {
        $query = "INSERT INTO categorys (name, ref_restaurant) VALUES (:name, :refRestaurant)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':refRestaurant', $refRestaurant);

        if ($stmt->execute()) {
            return $this->db->lastInsertId(); // Retourne l'ID de la catégorie ajoutée
        } else {
            return false;
        }
    }


    public function getAllCategories($refRestaurant)
    {
        $ref_restaurant = urldecode($refRestaurant);
        $query = "SELECT * FROM categorys WHERE ref_restaurant = :ref_restaurant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':ref_restaurant', $ref_restaurant, PDO::PARAM_STR);
        $stmt->execute();

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
