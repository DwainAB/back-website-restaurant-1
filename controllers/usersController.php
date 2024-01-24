<?php

class UserController
{
    private $userModel;
    private $db;


    // Modifiez le constructeur pour accepter une instance de UserModel
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function addUser()
    {
        $firstname = $_POST['firstname'] ?? null;
        $lastname = $_POST['lastname'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        $tel = $_POST['tel'] ?? null;
        $address = $_POST['address'] ?? null;
        $role = $_POST['role'] ?? null;

        if ($firstname && $lastname && $email && $password && $tel && $address && $role) {
            if ($this->userModel->emailExists($email)) {
                echo 'Erreur : L\'email est déjà utilisé.';
            } else {
                $this->userModel->addUser($firstname, $lastname, $email, $password, $tel, $address, $role);
                echo 'Utilisateur ajouté avec succès.';
            }
        } else {
            echo 'Tous les champs sont requis.';
        }
    }


    public function getAllUsers()
    {
        $users = $this->userModel->getAll();
        echo json_encode($users);
    }

    public function getUser($id)
    {
        $user = $this->userModel->getById($id);
        echo json_encode($user);
    }

    public function updateUser($id)
    {
        $firstname = $_POST['firstname'] ?? null;
        $lastname = $_POST['lastname'] ?? null;
        $email = $_POST['email'] ?? null;
        $tel = $_POST['tel'] ?? null;
        $address = $_POST['address'] ?? null;
        $role = $_POST['role'] ?? null;

        // Il n'est pas nécessaire de vérifier si tous les champs sont remplis
        // Le modèle se chargera d'utiliser les valeurs existantes si certaines sont manquantes
        $this->userModel->update($id, $firstname, $lastname, $email, $tel, $address, $role);
        echo 'Utilisateur mis à jour avec succès.';
    }


    public function deleteUser($id)
    {
        $this->userModel->delete($id);
        echo 'Utilisateur supprimé avec succès.';
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log(print_r($data, true)); // Affiche les données reçues dans les logs d'erreur

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if ($email && $password) {
            $user = $this->userModel->login($email, $password);
            if ($user) {
                // Générer un token (exemple simpliste, utilisez une bibliothèque sécurisée dans la production)
                $token = bin2hex(random_bytes(16));
                // TODO: Stockez ce token dans une table de session ou renvoyez-le pour être stocké dans le front-end

                // Renvoyer les informations de l'utilisateur (sans le mot de passe)
                unset($user['password']); // Ne jamais renvoyer le mot de passe, même hashé
                echo json_encode(['token' => $token, 'user' => $user]);
            } else {
                echo json_encode(['error' => 'Email ou mot de passe incorrect']);
            }
        } else {
            echo json_encode(['error' => 'Email et mot de passe sont requis']);
        }
    }


    private function getUserFromToken($token)
    {
        // Utilisez la méthode getUserFromSessionToken de userModel
        return $this->userModel->getUserFromSessionToken($token);
    }


    private function checkAdmin($token)
    {
        $user = $this->getUserFromToken($token);
        return $user && $user['role'] === 'Admin';
    }


    public function someAdminFunction()
    {
        $token = $_POST['token'] ?? null;
        if ($this->checkAdmin($token)) {
            // Le code ici est exécuté si l'utilisateur est un admin
        } else {
            echo json_encode(['error' => 'Accès refusé']);
            return;
        }
    }
}
