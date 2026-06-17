<?php
namespace App\Controller;

use App\Config\Database;
use App\Validator\InputValidator;
use PDOException;

class LoginController {
    public function handleLogin() {
        // Cross-Origin Access Control for Cloudflare Pages frontend
        header("Access-Control-Allow-Origin: https://php-signup-app.jacobotana96.workers.dev");
        header("Access-Control-Allow-Credentials: true");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => 'Only POST requests are permitted.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Malformed JSON payload.']);
            return;
        }

        // Validate Input
        $errors = InputValidator::validateLogin($data);
        if (!empty($errors)) {
            header('HTTP/1.1 422 Unprocessable Entity');
            echo json_encode(['errors' => $errors]);
            return;
        }

        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $password = $data['password'];
        $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');

        try {
            $db = Database::getConnection();

            // 1. Rate Limiting Check: Block if > 5 failed attempts in last 15 mins
            $limitStmt = $db->prepare("
                SELECT COUNT(*) FROM public.login_attempts 
                WHERE email = :email AND is_successful = false 
                AND attempted_at > NOW() - INTERVAL '15 minutes'
            ");
            $limitStmt->execute([':email' => $email]);
            if ($limitStmt->fetchColumn() >= 5) {
                header('HTTP/1.1 429 Too Many Requests');
                echo json_encode(['error' => 'Account temporarily locked due to too many failed attempts. Try again in 15 minutes.']);
                return;
            }

            // 2. Fetch User Record
            $stmt = $db->prepare("SELECT id, full_name, password_hash FROM public.signups WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            // 3. Verify Password securely
            if ($user && password_verify($password, $user['password_hash'])) {
                // Log Success
                $this->logAttempt($db, $email, true, $ipHash);

                // Establish Secure Session Configuration
                if (session_status() === PHP_SESSION_NONE) {
                    // Set secure cookie attributes before starting session
                    session_ Ramsey_cookie_params([
                        'lifetime' => 86400, // 24 Hours
                        'path' => '/',
                        'domain' => parse_url($_ENV['ALLOWED_ORIGIN'], PHP_URL_HOST),
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'None' // Critical configuration for cross-origin setups
                    ]);
                    session_start();
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];

                header('HTTP/1.1 200 OK');
                echo json_encode([
                    'success' => 'Authentication successful.',
                    'user' => [
                        'name' => $user['full_name'],
                        'email' => $email
                    ]
                ]);
            } else {
                // Log Failure (Use a generic message to prevent user enumeration)
                $this->logAttempt($db, $email, false, $ipHash);
                header('HTTP/1.1 401 Unauthorized');
                echo json_encode(['error' => 'Invalid email address or password configuration.']);
            }

        } catch (PDOException $e) {
            error_log("Login System Database Fault: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'An authentication tracking error occurred.']);
        }
    }

    private function logAttempt($db, $email, $isSuccessful, $ipHash) {
        $logStmt = $db->prepare("
            INSERT INTO public.login_attempts (email, is_successful, ip_address_hashed) 
            VALUES (:email, :is_successful, :ip_hash)
        ");
        $logStmt->execute([
            ':email' => $email,
            ':is_successful' => $isSuccessful ? 'true' : 'false',
            ':ip_hash' => $ipHash
        ]);
    }
}