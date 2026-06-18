<?php
namespace App\Controller;

use App\Config\Database;
use App\Validator\InputValidator;
use App\Service\EmailService;
use PDOException;

class SignupController {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => 'Only POST requests are permitted.']);
            return;
        }

        // Read and parse raw JSON input
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);

        if (!$data) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Malformed JSON input payload.']);
            return;
        }

        // CSRF verification check (Since frontend is decoupled, we depend on CORS and custom token verification headers)
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'FetchApp') {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Security token verification failed.']);
            return;
        }

        // Input Validation
        $errors = InputValidator::validate($data);
        if (!empty($errors)) {
            header('HTTP/1.1 422 Unprocessable Entity');
            echo json_encode(['errors' => $errors]);
            return;
        }

        // Sanitization
        $name  = InputValidator::sanitizeString($data['full_name']);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $phone = !empty($data['phone_number']) ? InputValidator::sanitizeString($data['phone_number']) : null;

        try {
            $db = Database::getConnection();
            
            // Check for duplicate record
            $stmt = $db->prepare("SELECT id FROM public.signups WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                header('HTTP/1.1 409 Conflict');
                echo json_encode(['error' => 'This email address is already registered.']);
                return;
            }

            // Persistence
            $insertStmt = $db->prepare("
                INSERT INTO public.signups (full_name, email, phone_number) 
                VALUES (:full_name, :email, :phone_number)
            ");
            $insertStmt->execute([
                ':full_name'    => $name,
                ':email'        => $email,
                ':phone_number' => $phone
            ]);

            // Outbound Notification Service
            EmailService::sendAdminNotification($name, $email, $phone);

            // FIX: Set explicit status code header and output structured JSON data before exiting
            http_response_code(201);
            echo json_encode([
                'success' => 'Signup finalized successfully.'
            ]);
            exit; // Terminate execution to prevent accidental extra whitespace or warnings

        } catch (PDOException $e) {
            error_log("Database Storage Error: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'An internal database serialization fault occurred.']);
        }
    }
}