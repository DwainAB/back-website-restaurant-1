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

    if (isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['tel']) && isset($_POST['address']) && isset($_POST['role']) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $tel = $_POST['tel'];
    $address = $_POST['address'];
    $role = $_POST['role'];
}

    
    $response = array();

    /*$firstname = $_POST['firstname'] ?? null;
    $lastname = $_POST['lastname'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $tel = $_POST['tel'] ?? null;
    $address = $_POST['address'] ?? null;
    $role = $_POST['role'] ?? null;*/

    // Initialisation du tableau pour stocker les champs manquants
    $missingFields = array();

    // Vérification de la présence de tous les champs requis
    if (!$firstname) {
        $missingFields[] = 'firstname';
    }
    if (!$lastname) {
        $missingFields[] = 'lastname';
    }
    if (!$email) {
        $missingFields[] = 'email';
    }
    if (!$password) {
        $missingFields[] = 'password';
    }
    if (!$tel) {
        $missingFields[] = 'tel';
    }
    if (!$address) {
        $missingFields[] = 'address';
    }
    if (!$role) {
        $missingFields[] = 'role';
    }

    if (!empty($missingFields)) {
        $response['success'] = false;
        $response['message'] = 'il Manque : ' . implode(', ', $missingFields) . '.';
        echo json_encode($response);
        return;
    }

    // Vérification si l'email est déjà utilisé
    if ($this->userModel->emailExists($email)) {
        $response['success'] = false;
        $response['message'] = 'Erreur : L\'email est déjà utilisé.';
    } else {
        // Ajout de l'utilisateur
        $this->userModel->addUser($firstname, $lastname, $email, $password, $tel, $address, $role);
        $response['success'] = true;
        $response['message'] = 'Utilisateur ajouté avec succès !!!!!!.';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
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
        try {
            $firstname = $_POST['firstname'] ?? null;
            $lastname = $_POST['lastname'] ?? null;
            $email = $_POST['email'] ?? null;
            $tel = $_POST['tel'] ?? null;
            $address = $_POST['address'] ?? null;
            $role = $_POST['role'] ?? null;

            // Ici, le modèle `update` doit renvoyer `true` si la mise à jour est réussie, `false` sinon
            $updateStatus = $this->userModel->update($id, $firstname, $lastname, $email, $tel, $address, $role);

            if ($updateStatus) {
                http_response_code(200); // OK
                echo json_encode(['message' => 'Utilisateur mis à jour avec succès.']);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(['message' => 'Erreur lors de la mise à jour de l\'utilisateur.']);
            }
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode(['message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
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
