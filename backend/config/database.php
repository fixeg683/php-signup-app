<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = getenv('SUPABASE_DB_HOST');
            $db   = getenv('SUPABASE_DB_NAME');
            $user = getenv('SUPABASE_DB_USER');
            $pass = getenv('SUPABASE_DB_PASSWORD');
            $port = getenv('SUPABASE_DB_PORT');
            
            $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require;";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Log error silently in production, avoid leaking details
                error_log("Database Connection Error: " . $e->getMessage());
                header('HTTP/1.1 500 Internal Server Error');
                echo json_encode(['error' => 'Database connection failure.']);
                exit;
            }
        }
        return self::$instance;
    }
}