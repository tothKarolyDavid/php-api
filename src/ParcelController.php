<?php

class ParcelController
{
    private PDO $connection;

    public function __construct(Database $database)
    {
        $this->connection = $database->getConnection();
    }

    public function processRequest(string $method, ?string $number): void
    {
        switch ($method) {
            case 'GET':
                $this->getParcel($number);
                break;
            case 'POST':
                $this->addParcel();
                break;
            default:
                http_response_code(405);
                header('Allow: GET, POST');
                echo json_encode(['error' => 'Method not allowed']);
                exit;
        }
    }

    private function getParcel(?string $number): void
    {
        if ($number === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parcel number']);
            exit;
        }

        $query = $this->connection->prepare('SELECT * FROM parcels WHERE parcel_number = :parcel_number');
        $query->execute(['parcel_number' => $number]);
        $parcel = $query->fetch(PDO::FETCH_ASSOC);

        if (!$parcel) {
            http_response_code(404);
            echo json_encode(['error' => 'Parcel not found']);
            exit;
        }

        $query = $this->connection->prepare('SELECT * FROM users WHERE id = :id');
        $query->execute(['id' => $parcel['user_id']]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        unset($user['password']);
        unset($parcel['user_id']);

        $parcel['user'] = $user;

        echo json_encode($parcel);
    }

    private function addParcel(): void
    {
        // Cast input to array from json to work with error handling
        $input = (array) json_decode(file_get_contents('php://input'), true);

        $errors = $this->getValidationErrors($input);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['error' => $errors]);
            exit;
        }

        $size = trim($input['size']);
        $user_id = trim($input['user_id']);

        // Generating parcel number until it is unique in the database
        do {
            $parcel_number = bin2hex(random_bytes(5));
            $query = $this->connection->prepare('SELECT parcel_number FROM parcels WHERE parcel_number = :parcel_number');
            $query->execute(['parcel_number' => $parcel_number]);
            $parcel = $query->fetch(PDO::FETCH_ASSOC);
        } while ($parcel);


        $sql = "INSERT INTO parcels (parcel_number, size, user_id) VALUES (:parcel_number, :size, :user_id)";
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':parcel_number', $parcel_number);
        $statement->bindParam(':size', $size);
        $statement->bindParam(':user_id', $user_id);
        $statement->execute();

        $query = $this->connection->prepare('SELECT parcel_number, size, user_id FROM parcels WHERE parcel_number = :parcel_number');
        $query->execute(['parcel_number' => $parcel_number]);
        $parcel = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->connection->prepare('SELECT id, first_name, last_name, email_address, phone_number FROM users WHERE id = :id');
        $query->execute(['id' => $parcel['user_id']]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        unset($user['password']);
        unset($parcel['user_id']);

        $parcel['user'] = $user;

        http_response_code(201);
        echo json_encode($parcel);
    }

    private function getValidationErrors(array $input): array
    {
        $errors = [];

        if (!isset($input['size']) || empty(trim($input['size']))) {
            $errors[] = 'Size is required';
        } elseif (!in_array(trim($input['size']), ['S', 'M', 'L', 'XL'])) {
            $errors[] = 'Invalid size';
        }

        if (!isset($input['user_id']) || empty(trim($input['user_id']))) {
            $errors[] = 'User ID is required';
        } elseif (!is_numeric($input['user_id'])) {
            $errors[] = 'Invalid user ID';
        } else {
            // Checking if user exists with this ID
            $query = $this->connection->prepare('SELECT id FROM users WHERE id = :id');
            $query->execute(['id' => trim($input['user_id'])]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $errors[] = 'User not found with this ID';
            }
        }

        return $errors;
    }
}
