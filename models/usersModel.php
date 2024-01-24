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
    public function addUser($firstname, $lastname, $email, $password, $tel, $address, $role)
    {
        // Hasher le mot de passe avant de l'insérer dans la base de données
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO $this->table (firstname, lastname, email, password, tel, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$firstname, $lastname, $email, $hashedPassword, $tel, $address, $role]);
    }


    // Récupérer tous les utilisateurs
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM $this->table");
        $stmt->execute();
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
    public function update($id, $firstname = null, $lastname = null, $email = null, $tel = null, $address = null, $role = null)
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

        // Préparez et exécutez la requête
        $stmt = $this->db->prepare("UPDATE $this->table SET firstname = ?, lastname = ?, email = ?, tel = ?, address = ?, role = ? WHERE id = ?");
        return $stmt->execute([$firstname, $lastname, $email, $tel, $address, $role, $id]);
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
}
