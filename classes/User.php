<?php
/**
 * User.php
 * Handles everything related to Users: register, login, fetch, update.
 * Demonstrates: Encapsulation, Constructor, PDO prepared statements, password hashing.
 */

require_once __DIR__ . '/../config/Database.php';

class User
{
    // Encapsulation: properties are private, only accessible through methods
    private $db;

    private $userId;
    private $fullName;
    private $username;
    private $email;
    private $role;

    // Constructor - runs every time "new User()" is called
    public function __construct()
    {
        // Get the ONE shared PDO connection from our Singleton Database class
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Register a new user.
     * Returns true on success, or a string error message on failure.
     */
    public function register($fullName, $username, $email, $password, $role)
    {
        // 1. Check if username or email already exists (prevents duplicates)
        $checkSql = "SELECT user_id FROM users WHERE username = :username OR email = :email";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        if ($checkStmt->rowCount() > 0) {
            return "Username or email already exists.";
        }

        // 2. Hash the password - NEVER store plain text passwords
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert using a prepared statement (prevents SQL Injection)
        $sql = "INSERT INTO users (full_name, username, email, password_hash, role)
                VALUES (:full_name, :username, :email, :password_hash, :role)";
        $stmt = $this->db->prepare($sql);

        $success = $stmt->execute([
            ':full_name' => htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'), // XSS protection
            ':username'  => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            ':email'     => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            ':password_hash' => $hashedPassword,
            ':role'      => $role
        ]);

        return $success ? true : "Registration failed. Please try again.";
    }

    /**
     * Attempt to log a user in.
     * Returns the user's data (array) on success, or false on failure.
     */
    public function login($username, $password)
    {
        $sql = "SELECT * FROM users WHERE username = :username AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // password_verify() checks the plain password against the stored hash
        if ($user && password_verify($password, $user['password_hash'])) {
            // Store details in this object (encapsulated state)
            $this->userId   = $user['user_id'];
            $this->fullName = $user['full_name'];
            $this->username = $user['username'];
            $this->email    = $user['email'];
            $this->role     = $user['role'];

            return $user; // caller (e.g. login.php) will start the session
        }

        return false; // wrong username or password
    }

    /**
     * Fetch a single user by ID.
     */
    public function findById($userId)
    {
        $sql = "SELECT user_id, full_name, username, email, role, is_active, created_at
                FROM users WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all users (for Admin's user management page).
     */
    public function getAll()
    {
        $sql = "SELECT user_id, full_name, username, email, role, is_active, created_at
                FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Deactivate a user instead of deleting (keeps audit trail intact).
     */
    public function deactivate($userId)
    {
        $sql = "UPDATE users SET is_active = 0 WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    // Simple getters (part of encapsulation - controlled read access)
    public function getUserId()   { return $this->userId; }
    public function getFullName() { return $this->fullName; }
    public function getRole()     { return $this->role; }
}
