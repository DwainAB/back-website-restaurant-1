<?php
class UserModel
{
    private $table = "users";

    private $db;

    public function __construct($pdo)
    {
        if (!$pdo instanceof PDO) {
            throw new InvalidArgumentException('Expected a PDO instance.');
        }
        $this->db = $pdo;
    }

    // Ajouter un utilisateur
    public function addUser($ref_restaurant, $place_id, $firstname, $lastname, $email, $password, $tel, $address, $role)
    {
        // Hasher le mot de passe avant de l'insérer dans la base de données
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $tel = ($tel === '') ? null : $tel;
        $address = ($address === '') ? null : $address;
        $role = ($role === '') ? 'User' : $role;

        $stmt = $this->db->prepare("INSERT INTO $this->table (ref_restaurant, place_id, firstname, lastname, email, password, tel, address, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$ref_restaurant, $place_id, $firstname, $lastname, $email, $hashedPassword, $tel, $address, $role]);
    }


    // Récupérer tous les utilisateurs
    public function getAll($ref_restaurant)
    {
        $ref_restaurant = urldecode($ref_restaurant);
        // Préparez votre requête SQL pour récupérer les utilisateurs par ref_restaurant
        $stmt = $this->db->prepare("SELECT * FROM $this->table WHERE ref_restaurant = ?");
        $stmt->execute([$ref_restaurant]);

        // Récupérez tous les utilisateurs filtrés
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un utilisateur par son ID
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour un utilisateur
    public function update($id, $firstname = null, $lastname = null, $email = null, $tel = null, $address = null, $role = null, $password = null)
    {
        // Récupérez les données actuelles de l'utilisateur
        $currentUser = $this->getById($id);

        // Utilisez les valeurs existantes comme valeurs par défaut
        $firstname = $firstname ?? $currentUser['firstname'];
        $lastname = $lastname ?? $currentUser['lastname'];
        $email = $email ?? $currentUser['email'];
        $tel = $tel ?? $currentUser['tel'];
        $address = $address ?? $currentUser['address'];
        $role = $role ?? $currentUser['role'];

        // Vérifiez si un nouveau mot de passe a été fourni
        if ($password) {
            // Hachez le nouveau mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Préparez et exécutez la requête
            $stmt = $this->db->prepare("UPDATE $this->table SET firstname = ?, lastname = ?, email = ?, tel = ?, address = ?, role = ?, password = ? WHERE id = ?");
            return $stmt->execute([$firstname, $lastname, $email, $tel, $address, $role, $hashedPassword, $id]);
        } else {
            // Si aucun nouveau mot de passe n'est fourni, mettez à jour sans le mot de passe
            $stmt = $this->db->prepare("UPDATE $this->table SET firstname = ?, lastname = ?, email = ?, tel = ?, address = ?, role = ? WHERE id = ?");
            return $stmt->execute([$firstname, $lastname, $email, $tel, $address, $role, $id]);
        }
    }


    // Supprimer un utilisateur
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM $this->table WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function emailExists($email)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM $this->table WHERE email = ?");
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    public function login($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM $this->table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function getUserFromSessionToken($token)
    {
        $stmt = $this->db->prepare("SELECT * FROM sessions WHERE token = ?");
        $stmt->execute([$token]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($session) {
            return $this->getById($session['user_id']);
        }
        return null;
    }

    public function getUserByEmail($email)
    {
        // Récupérer l'utilisateur par email depuis la base de données
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUserPassword($email, $hashedPassword)
    {
        // Mettre à jour le mot de passe de l'utilisateur dans la base de données
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE email = ?');
        return $stmt->execute([$hashedPassword, $email]);
    }
}
