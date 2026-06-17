<?php
header("Access-Control-Allow-Origin: " . ($_ENV['ALLOWED_ORIGIN'] ?? '*'));
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controller\SignupController;
use App\Controller\LoginController;

// Initialize configurations if running locally
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($requestUri === '/login') {
    $controller = new LoginController();
    $controller->handleLogin();
} else {
    // Default to Signup Controller
    $controller = new SignupController();
    $controller->handleRequest();
}