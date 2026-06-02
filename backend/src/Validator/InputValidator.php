<?php
namespace App\Validator;

class InputValidator {
    public static function sanitizeString(string $data): string {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public static function validate(array $input): array {
        $errors = [];

        // Validate Full Name
        if (empty($input['full_name'])) {
            $errors['full_name'] = 'Full Name is required.';
        } elseif (strlen($input['full_name']) < 2 || strlen($input['full_name']) > 100) {
            $errors['full_name'] = 'Name must be between 2 and 100 characters.';
        }

        // Validate Email
        if (empty($input['email'])) {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address format.';
        }

        // Validate Phone (Optional)
        if (!empty($input['phone_number'])) {
            // Basic regex regex matching international formats
            if (!preg_match('/^\+?[0-9]{7,15}$/', $input['phone_number'])) {
                $errors['phone_number'] = 'Invalid phone number format.';
            }
        }

        return $errors;
    }
}