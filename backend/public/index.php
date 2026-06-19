<?php
// Use getenv() to support Docker/Render environment loading safely
$allowed_origin = getenv('ALLOWED_ORIGIN') ?: ($_ENV['ALLOWED_ORIGIN'] ?? '*');

header("Access-Control-Allow-Origin: " . $allowed_origin);
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
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

// Use query parameter routing for explicit control
$route = $_GET['route'] ?? '';

if ($route === 'login') {
    $controller = new LoginController();
    $controller->handleLogin();
} else {
    // Default to Signup Controller
    $controller = new SignupController();
    $controller->handleRequest();
}