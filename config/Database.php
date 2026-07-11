<?php
/**
 * Database.php
 * Handles the PDO database connection using the Singleton design pattern
 * (Singleton = only ONE connection object is ever created and reused)
 */

class Database
{
    private static $instance = null;   // static property holds the single instance
    private $connection;

    private $host = "localhost";
    private $dbname = "death_registration";
    private $username = "root";
    private $password = "";            // XAMPP default: empty password

    // Constructor is PRIVATE - stops anyone from doing "new Database()" directly
    private function __construct()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password);

            // Make PDO throw exceptions on errors (easier to debug + catch)
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Always use real prepared statements (prevents SQL injection)
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            // Never show raw DB errors to users in production
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Public static method - the ONLY way to get access to the connection
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database(); // created only the first time
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    // Destructor - runs automatically when the object is destroyed
    public function __destruct()
    {
        $this->connection = null;
    }
}
