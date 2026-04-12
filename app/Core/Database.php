<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            // Se já definimos ROOT_PATH no index.php, usamos ele.
            // Senão, tentamos localizar aqui por perto.
            if (!defined('ROOT_PATH')) {
                if (file_exists(__DIR__ . '/../../config/config.php')) {
                    define('ROOT_PATH', __DIR__ . '/../../');
                } else {
                    define('ROOT_PATH', __DIR__ . '/');
                }
            }
            require_once ROOT_PATH . 'config/config.php';
            
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Erro na conexão com o banco de dados: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
