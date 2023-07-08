<?php

class UserController
{
    private PDO $connection;

    public function __construct(Database $database)
    {
        $this->connection = $database->getConnection();
    }

    public function processRequest(string $method): void
    {
        switch ($method) {
            case 'GET':
                $this->getUsers();
                break;
            case 'POST':
                $this->addUser();
                break;
            default:
                http_response_code(405);
                header('Allow: GET, POST');
                echo json_encode(['error' => 'Method not allowed']);
                break;
        }
    }

    private function getUsers(): void
    {
        $sql = "SELECT id, first_name, last_name, email_address, phone_number FROM users";
        $statement = $this->connection->query($sql);
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($users);
    }

    private function addUser(): void
    {
        // Cast input to array from json to work with error handling
        $input = (array) json_decode(file_get_contents('php://input'), true);

        $errors = $this->getValidationErrors($input);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['error' => $errors]);
            exit;
        }

        // Trim and sanitize input
        $firstName = trim($input['first_name']);
        $lastName = trim($input['last_name']);
        $emailAddress = filter_var(trim($input['email_address']), FILTER_SANITIZE_EMAIL);
        $password = password_hash(trim($input['password']), PASSWORD_BCRYPT);
        $phoneNumber = empty(trim($input['phone_number'])) ? null : trim($input['phone_number']);

        $sql = "INSERT INTO users (first_name, last_name, email_address, password, phone_number) VALUES (:first_name, :last_name, :email_address, :password, :phone_number)";
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':first_name', $firstName);
        $statement->bindParam(':last_name', $lastName);
        $statement->bindParam(':email_address', $emailAddress);
        $statement->bindParam(':password', $password);
        $statement->bindParam(':phone_number', $phoneNumber);
        $statement->execute();

        $id = $this->connection->lastInsertId();

        $sql = "SELECT id, first_name, last_name, email_address, phone_number FROM users WHERE id = :id";
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':id', $id);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        http_response_code(201);
        echo json_encode($user);
    }

    private function getValidationErrors(array $input): array
    {
        $errors = [];

        if (!isset($input['first_name']) || empty(trim($input['first_name']))) {
            $errors[] = 'First name is required';
        }

        if (!isset($input['last_name']) || empty(trim($input['last_name']))) {
            $errors[] = 'Last name is required';
        }

        if (!isset($input['email_address']) || empty(trim($input['email_address']))) {
            $errors[] = 'Email address is required';
        } else if (!filter_var(trim($input['email_address']), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email address is invalid';
        }

        if (!isset($input['password']) || empty(trim($input['password']))) {
            $errors[] = 'Password is required';
        }

        return $errors;
    }
}
