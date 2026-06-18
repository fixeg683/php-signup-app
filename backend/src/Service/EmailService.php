<?php
namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    public static function sendAdminNotification(string $name, string $email, ?string $phone): bool {
        $mail = new PHPMailer(true);
        try {
            // Server configurations using standard platform lookups
            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST') ?: $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER') ?: $_ENV['SMTP_USER'];
            $mail->Password   = getenv('SMTP_PASS') ?: $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)(getenv('SMTP_PORT') ?: $_ENV['SMTP_PORT']);

            // Recipients
            $mail->setFrom(getenv('SMTP_USER') ?: $_ENV['SMTP_USER'], 'System Signup Alert');
            $mail->addAddress(getenv('ADMIN_EMAIL') ?: $_ENV['ADMIN_EMAIL']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Alert: New User Registered';
            
            $phoneField = $phone ? $phone : 'Not Provided';
            $mail->Body    = "
                <h3>New Signup Received</h3>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Phone:</strong> {$phoneField}</p>
                <p><em>Timestamp: " . date('Y-m-d H:i:s UTC') . "</em></p>
            ";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Mail Delivery Failure: " . $mail->ErrorInfo);
            return false;
        }
    }
}