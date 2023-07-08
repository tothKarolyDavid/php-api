<?php

declare(strict_types=1);

// Autoload classes
spl_autoload_register(function (string $className) {
    require __DIR__ . '/src/' . $className . '.php';
});

// Set error and exception handlers
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

// Create database and tables with the data from config.php
$config = include('config.php');

$database = new Database(
    $config['host'],
    $config['database_name'],
    $config['user'],
    $config['password']
);

header('Content-Type: application/json; charset=UTF-8');

$parts = explode("/", $_SERVER['REQUEST_URI']);

if ($parts[1] != "users" && $parts[1] != "parcels") {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid endpoint']);
    exit;
}

$id = $parts[2] ?? null;

$userController = new UserController($database);
$parcelController = new ParcelController($database);

switch ($parts[1]) {
    case 'users':
        $userController->processRequest($_SERVER['REQUEST_METHOD']);
        break;
    case 'parcels':
        $parcelController->processRequest($_SERVER['REQUEST_METHOD'], $id);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid endpoint']);
        exit;
}
