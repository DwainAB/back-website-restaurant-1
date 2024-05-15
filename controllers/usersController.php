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
        $response = array();

        // Vérification de la présence des données requises
        if (isset($_POST['ref_restaurant']) && isset($_POST['place_id']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['tel']) && isset($_POST['address'])) {
            $ref_restaurant = $_POST['ref_restaurant'];
            $place_id = $_POST['place_id']; // Ajout de la récupération du place_id
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $tel = $_POST['tel'] ?? null;
            $address = $_POST['address'] ?? null;
            $role = isset($_POST['role']) ? $_POST['role'] : 'user'; // Définition de la valeur par défaut pour role

            // Vérification si l'email est déjà utilisé
            if ($this->userModel->emailExists($email)) {
                $response['success'] = false;
                $response['message'] = 'Erreur : L\'email est déjà utilisé.';
            } else {
                // Ajout de l'utilisateur avec le place_id
                $this->userModel->addUser($ref_restaurant, $place_id, $firstname, $lastname, $email, $password, $tel, $address, $role);
                $response['success'] = true;
                $response['message'] = 'Utilisateur ajouté avec succès !!!';

                require_once './services/sendEmailAddUser.php';
                sendMailAddUser($email, $firstname, $lastname, $password, $ref_restaurant);
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Erreur : Données manquantes.';
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }



    public function getAllUsers($ref_restaurant)
    {
        $users = $this->userModel->getAll($ref_restaurant);
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
            $newPassword = $_POST['newPassword'] ?? null; // Nouveau mot de passe
            $oldPassword = $_POST['oldPassword'] ?? null; // Ancien mot de passe

            // Récupérer les données actuelles de l'utilisateur
            $currentUser = $this->userModel->getById($id);

            // Vérifier si l'ancien mot de passe correspond
            if (!password_verify($oldPassword, $currentUser['password'])) {
                http_response_code(400); // Bad Request
                echo json_encode([
                    'message' => 'L\'ancien mot de passe est incorrect.',
                    'Ancien mot de passe' => $oldPassword,
                    'Nouveau mot de passe' => $newPassword,
                    'mot de passe stocké dans la base de donnée' => $currentUser['password']
                ]);
                return;
            }

            // Mise à jour des autres informations de l'utilisateur
            $updateStatus = $this->userModel->update($id, $firstname, $lastname, $email, $tel, $address, $role, $newPassword);

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
        echo json_encode(['message' => 'Utilisateur supprimé avec succès.']);
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

    public function sendPassword()
    {
        error_log("sendPassword method called"); // Log pour vérifier que la méthode est appelée

        // Vérifier que l'email est présent dans la demande
        if (isset($_POST['email'])) {
            $email = $_POST['email'];
            error_log("Email received: $email"); // Log pour vérifier que l'email est reçu

            // Récupérer l'utilisateur par email
            $user = $this->userModel->getUserByEmail($email);
            error_log("User fetched: " . print_r($user, true)); // Log pour vérifier que l'utilisateur est récupéré

            if ($user) {
                // Générer un mot de passe aléatoire de 10 caractères
                $newPassword = $this->generateRandomPassword(10);
                error_log("Generated password: $newPassword"); // Log pour vérifier que le mot de passe est généré

                // Hacher le mot de passe
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                error_log("Hashed password: $hashedPassword"); // Log pour vérifier que le mot de passe est haché

                // Mettre à jour le mot de passe de l'utilisateur dans la base de données
                $updateSuccess = $this->userModel->updateUserPassword($email, $hashedPassword);
                error_log("Password update success: " . ($updateSuccess ? "true" : "false")); // Log pour vérifier que le mot de passe est mis à jour

                if ($updateSuccess) {

                    require_once './services/sendEmailResetPassword.php';
                    sendMailAddUser($email, $newPassword);

                    http_response_code(200);
                    echo json_encode(['message' => 'Mot de passe réinitialisé avec succès', 'password' => $newPassword]);
                } else {
                    http_response_code(500); // Erreur serveur
                    echo json_encode(['message' => 'Erreur lors de la mise à jour du mot de passe']);
                }
            } else {
                http_response_code(404); // Utilisateur non trouvé
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } else {
            http_response_code(400); // Mauvaise demande
            echo json_encode(['message' => 'Email manquant']);
        }
    }

    private function generateRandomPassword($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomPassword = '';
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomPassword;
    }
}
